<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace OrangeHRM\Mcp\Tool;

use OrangeHRM\Core\Controller\Rest\V2\GenericRestController;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Framework\Http\Request as HttpRequest;
use OrangeHRM\Mcp\JsonRpc\EnvelopeException;
use OrangeHRM\Mcp\JsonRpc\ErrorCode;
use OrangeHRM\Mcp\Traits\Service\ModuleServiceTrait;
use Throwable;

/**
 * The only sanctioned way an MCP tool reaches data.
 *
 * Two protections that guard the REST surface live in kernel event subscribers,
 * so an in-process call skips them and must replicate them here:
 *
 *  - ApiAuthorizationSubscriber (CONTROLLER event) -- the ohrm_api_permission
 *    data-group RBAC check, by HTTP verb.
 *  - ModuleNotAvailableSubscriber (REQUEST event) -- refuses /api/v2/<module>
 *    when that module is disabled.
 *
 * Request validation is *not* replicated, because it runs inside
 * AbstractRestController::handle(); dispatching through GenericRestController
 * keeps it. Instantiating an API class directly would lose both the RBAC check
 * and validation, which is why that is forbidden (see the plugin README).
 */
class RestDispatcher
{
    use UserRoleManagerTrait;
    use ModuleServiceTrait;
    use ServiceContainerTrait;

    /**
     * Resolved read grants, keyed by API class, for the life of the request.
     *
     * tools/list asks about every tool in the catalog, and the generic router
     * contributes hundreds. Many share an API class -- every route under one
     * endpoint family does -- so without this the same grant is resolved dozens
     * of times and tools/list spends most of its time in the permission layer.
     *
     * Request-scoped on purpose. The session-scoped PermissionProfile of Story
     * 2.7 is the real answer; this is the cheap correct version until then.
     *
     * @var array<string,bool>
     */
    private array $readGrantCache = [];

    /**
     * @var string[]|null enabled module names, resolved once per request
     */
    private ?array $enabledModules = null;

    /**
     * Dispatch a read-only tool call into the REST pipeline as the authenticated user.
     *
     * Takes the raw tool arguments rather than pre-split parameters, so that
     * splitting them into path placeholders and query string happens in exactly
     * one place for both curated and generic tools.
     *
     * @return array{status:int,payload:array} decoded REST response
     * @throws EnvelopeException on RBAC denial, a disabled module, or a missing
     *                           path parameter
     */
    public function dispatchGet(ToolInterface $tool, array $arguments): array
    {
        $this->assertModuleEnabled($tool);
        $this->assertReadPermitted($tool);

        $path = $this->expandPath($tool, $arguments);
        $queryParams = $tool->buildQueryParams($arguments);

        $httpRequest = HttpRequest::create($path, HttpRequest::METHOD_GET);
        $httpRequest->headers->set('Content-Type', 'application/json');
        $httpRequest->query->add($queryParams);
        $httpRequest->attributes->set('_api', $tool->getApiClass());
        // Replicate the route's `defaults` block; see ToolInterface::getRouteAttributes().
        foreach ($tool->getRouteAttributes() as $name => $value) {
            $httpRequest->attributes->set($name, $value);
        }
        // Path placeholders also travel as request attributes, the way the
        // router would have supplied them.
        foreach ($this->extractPathParams($tool, $arguments) as $name => $value) {
            $httpRequest->attributes->set($name, $value);
        }

        try {
            $httpResponse = (new GenericRestController())->handle($httpRequest);
        } catch (Throwable $e) {
            throw new EnvelopeException(
                ErrorCode::INTERNAL_ERROR,
                'Tool dispatch failed',
                ['category' => ErrorCode::CATEGORY_INTERNAL, 'tool' => $tool->getName()],
                $e
            );
        }

        $decoded = json_decode((string) $httpResponse->getContent(), true);

        return [
            'status' => $httpResponse->getStatusCode(),
            'payload' => is_array($decoded) ? $decoded : [],
        ];
    }

    /**
     * Substitutes `{placeholder}` segments in the tool's path from the supplied
     * arguments.
     *
     * A missing placeholder is a client error rather than something to paper
     * over: dispatching `/api/v2/pim/employees/{empNumber}` with the braces
     * intact would 404 confusingly.
     *
     * @throws EnvelopeException when a placeholder has no argument
     */
    private function expandPath(ToolInterface $tool, array $arguments): string
    {
        $path = $tool->getPath();
        if (strpos($path, '{') === false) {
            return $path;
        }

        $matches = [];
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', $path, $matches);
        foreach ($matches[1] as $name) {
            if (!array_key_exists($name, $arguments) || $arguments[$name] === null) {
                throw new EnvelopeException(
                    ErrorCode::INVALID_PARAMS,
                    sprintf('Missing required argument `%s`', $name),
                    [
                        'category' => ErrorCode::CATEGORY_VALIDATION,
                        'tool' => $tool->getName(),
                        'parameter' => $name,
                    ]
                );
            }
            $path = str_replace('{' . $name . '}', rawurlencode((string) $arguments[$name]), $path);
        }

        return $path;
    }

    /**
     * @return array<string,mixed> placeholder name to supplied value
     */
    private function extractPathParams(ToolInterface $tool, array $arguments): array
    {
        $matches = [];
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', $tool->getPath(), $matches);

        $params = [];
        foreach ($matches[1] as $name) {
            if (array_key_exists($name, $arguments)) {
                $params[$name] = $arguments[$name];
            }
        }
        return $params;
    }

    /**
     * Replicates ModuleNotAvailableSubscriber for in-process calls.
     *
     * @throws EnvelopeException
     */
    private function assertModuleEnabled(ToolInterface $tool): void
    {
        if (!in_array($tool->getModule(), $this->enabledModules(), true)) {
            throw new EnvelopeException(
                ErrorCode::INVALID_REQUEST,
                sprintf('The `%s` module is disabled on this instance', $tool->getModule()),
                [
                    'category' => ErrorCode::CATEGORY_BUSINESS_RULE,
                    'module' => $tool->getModule(),
                    'tool' => $tool->getName(),
                ]
            );
        }
    }

    /**
     * Replicates ApiAuthorizationSubscriber for in-process calls. Every tool in
     * this prototype is a GET, so the relevant grant is always canRead().
     *
     * @throws EnvelopeException
     */
    private function assertReadPermitted(ToolInterface $tool): void
    {
        if (!$this->canRead($tool->getApiClass())) {
            throw new EnvelopeException(
                ErrorCode::UNAUTHENTICATED,
                'You do not have permission to use this tool',
                [
                    'category' => ErrorCode::CATEGORY_AUTHORIZATION,
                    'tool' => $tool->getName(),
                ]
            );
        }
    }

    /**
     * Visibility probe for tools/list. Same grant check as dispatch, without
     * throwing -- so the catalog can be filtered to what the user may invoke.
     * Dispatch re-checks regardless (defense in depth).
     */
    public function isVisibleTo(ToolInterface $tool): bool
    {
        try {
            if (!in_array($tool->getModule(), $this->enabledModules(), true)) {
                return false;
            }
            return $this->canRead($tool->getApiClass());
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @return string[]
     */
    private function enabledModules(): array
    {
        if ($this->enabledModules === null) {
            $this->enabledModules = $this->getModuleService()->getModuleDao()->getEnabledModuleNameList();
        }
        return $this->enabledModules;
    }

    private function canRead(string $apiClass): bool
    {
        if (!array_key_exists($apiClass, $this->readGrantCache)) {
            $this->readGrantCache[$apiClass] =
                $this->getUserRoleManager()->getApiPermissions($apiClass)->canRead();
        }
        return $this->readGrantCache[$apiClass];
    }
}
