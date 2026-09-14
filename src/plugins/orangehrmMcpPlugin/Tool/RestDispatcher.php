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
     * Dispatch a read-only tool call into the REST pipeline as the authenticated user.
     *
     * @return array{status:int,payload:array} decoded REST response
     * @throws EnvelopeException on RBAC denial or a disabled module
     */
    public function dispatchGet(ToolInterface $tool, array $queryParams): array
    {
        $this->assertModuleEnabled($tool);
        $this->assertReadPermitted($tool);

        $httpRequest = HttpRequest::create($tool->getPath(), HttpRequest::METHOD_GET);
        $httpRequest->headers->set('Content-Type', 'application/json');
        $httpRequest->query->add($queryParams);
        $httpRequest->attributes->set('_api', $tool->getApiClass());
        // Replicate the route's `defaults` block; see ToolInterface::getRouteAttributes().
        foreach ($tool->getRouteAttributes() as $name => $value) {
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
     * Replicates ModuleNotAvailableSubscriber for in-process calls.
     *
     * @throws EnvelopeException
     */
    private function assertModuleEnabled(ToolInterface $tool): void
    {
        $enabled = $this->getModuleService()->getModuleDao()->getEnabledModuleNameList();
        if (!in_array($tool->getModule(), $enabled, true)) {
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
        $permissions = $this->getUserRoleManager()->getApiPermissions($tool->getApiClass());
        if (!$permissions->canRead()) {
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
            $enabled = $this->getModuleService()->getModuleDao()->getEnabledModuleNameList();
            if (!in_array($tool->getModule(), $enabled, true)) {
                return false;
            }
            return $this->getUserRoleManager()->getApiPermissions($tool->getApiClass())->canRead();
        } catch (Throwable $e) {
            return false;
        }
    }
}
