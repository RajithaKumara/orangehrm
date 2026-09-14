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

namespace OrangeHRM\Mcp\Method;

use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Mcp\Audit\AuditWriter;
use OrangeHRM\Mcp\JsonRpc\Envelope;
use OrangeHRM\Mcp\JsonRpc\EnvelopeException;
use OrangeHRM\Mcp\JsonRpc\ErrorCode;
use OrangeHRM\Mcp\Service\McpAccessPolicy;
use OrangeHRM\Mcp\Session\SessionContext;
use OrangeHRM\Mcp\Tool\RestDispatcher;
use OrangeHRM\Mcp\Tool\ToolRegistry;
use Throwable;

/**
 * MCP `tools/call`.
 *
 * Every invocation writes an audit row before returning, including failures.
 * A tool-level failure (the endpoint said no) comes back as a normal result
 * with isError set, per the MCP spec; only protocol-level problems become
 * JSON-RPC errors.
 */
class ToolsCallHandler implements MethodHandlerInterface
{
    use AuthUserTrait;

    private ToolRegistry $registry;

    private RestDispatcher $dispatcher;

    private AuditWriter $auditWriter;

    private SessionContext $sessionContext;

    private McpAccessPolicy $policy;

    public function __construct(
        ToolRegistry $registry,
        RestDispatcher $dispatcher,
        AuditWriter $auditWriter,
        SessionContext $sessionContext,
        McpAccessPolicy $policy
    ) {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
        $this->auditWriter = $auditWriter;
        $this->sessionContext = $sessionContext;
        $this->policy = $policy;
    }

    public function getMethodName(): string
    {
        return 'tools/call';
    }

    public function handle(Envelope $envelope)
    {
        $params = $envelope->getParams();
        $name = $params['name'] ?? null;
        $arguments = $params['arguments'] ?? [];

        if (!is_string($name) || $name === '') {
            throw new EnvelopeException(
                ErrorCode::INVALID_PARAMS,
                'Invalid params: `name` is required',
                ['category' => ErrorCode::CATEGORY_VALIDATION]
            );
        }
        if (!is_array($arguments)) {
            throw new EnvelopeException(
                ErrorCode::INVALID_PARAMS,
                'Invalid params: `arguments` must be an object',
                ['category' => ErrorCode::CATEGORY_VALIDATION]
            );
        }

        $tool = $this->registry->get($name);
        if ($tool === null) {
            throw new EnvelopeException(
                ErrorCode::METHOD_NOT_FOUND,
                sprintf('Unknown tool `%s`', $name),
                ['category' => ErrorCode::CATEGORY_NOT_FOUND, 'tool' => $name]
            );
        }

        // Re-check the administrator's switch at dispatch. Filtering tools/list
        // is a convenience; this is the control.
        if (!$this->policy->isToolEnabled($tool->getName())) {
            throw new EnvelopeException(
                ErrorCode::UNAUTHENTICATED,
                sprintf('Tool `%s` is not enabled on this instance', $tool->getName()),
                ['category' => ErrorCode::CATEGORY_AUTHORIZATION, 'tool' => $tool->getName()]
            );
        }

        // Read-only mode, enforced at dispatch as well as in the catalog.
        // Filtering tools/list is a convenience; this is the control.
        if ($tool->isWriteOperation() && $this->policy->isReadOnly()) {
            throw new EnvelopeException(
                ErrorCode::READ_ONLY_MODE,
                'This instance is in read-only mode; write tools are disabled',
                ['category' => ErrorCode::CATEGORY_READ_ONLY, 'tool' => $tool->getName()]
            );
        }

        $startedAt = microtime(true);
        $status = 'error';
        $result = null;
        $thrown = null;

        try {
            $queryParams = $tool->buildQueryParams($arguments);
            $dispatched = $this->dispatcher->dispatchGet($tool, $queryParams);
            $status = (string) $dispatched['status'];
            $result = $this->toToolResult($dispatched);
        } catch (EnvelopeException $e) {
            $status = 'denied';
            $thrown = $e;
        } catch (Throwable $e) {
            $status = 'exception';
            $thrown = $e;
        }

        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

        // Audit first, then decide what to return. A failure to audit overrides
        // whatever the call produced (NFR-R3).
        $this->auditWriter->write(
            $this->resolveUserId(),
            $this->sessionContext->getSessionId(),
            $tool->getName(),
            $tool->getPath(),
            'GET',
            $arguments,
            $status,
            $latencyMs,
            $this->sessionContext->getClientId()
        );

        if ($thrown instanceof EnvelopeException) {
            throw $thrown;
        }
        if ($thrown !== null) {
            throw new EnvelopeException(
                ErrorCode::INTERNAL_ERROR,
                'Tool execution failed',
                ['category' => ErrorCode::CATEGORY_INTERNAL, 'tool' => $tool->getName()],
                $thrown
            );
        }

        return $result;
    }

    /**
     * Map a REST response onto an MCP tool result. Non-2xx becomes a tool-level
     * error (isError), which the model can read and recover from, rather than a
     * JSON-RPC error that reads as a protocol fault.
     */
    private function toToolResult(array $dispatched): array
    {
        $status = (int) $dispatched['status'];
        $payload = $dispatched['payload'];
        $isError = $status < 200 || $status >= 300;

        $body = $isError
            ? ($payload['error'] ?? ['status' => $status])
            : ($payload['data'] ?? $payload);

        $text = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $result = [
            'content' => [
                ['type' => 'text', 'text' => $text === false ? '{}' : $text],
            ],
            'isError' => $isError,
        ];

        if (!$isError && isset($payload['meta']) && $payload['meta'] !== []) {
            $result['_meta'] = ['orangehrm' => $payload['meta']];
        }

        return $result;
    }

    private function resolveUserId(): ?int
    {
        try {
            $userId = $this->getAuthUser()->getUserId();
            return $userId === null ? null : (int) $userId;
        } catch (Throwable $e) {
            return null;
        }
    }
}
