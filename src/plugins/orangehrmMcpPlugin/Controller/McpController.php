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

namespace OrangeHRM\Mcp\Controller;

use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use OrangeHRM\Mcp\JsonRpc\Envelope;
use OrangeHRM\Mcp\JsonRpc\EnvelopeException;
use OrangeHRM\Mcp\JsonRpc\ErrorCode;
use OrangeHRM\Mcp\Session\SessionContext;
use OrangeHRM\Mcp\Traits\McpEnabledTrait;
use OrangeHRM\Mcp\Traits\Service\McpAccessPolicyTrait;
use OrangeHRM\Mcp\Traits\Service\McpDispatcherTrait;
use OrangeHRM\Mcp\Traits\Service\McpOperatorLoggerTrait;
use OrangeHRM\Mcp\Traits\Service\McpSessionContextTrait;
use OrangeHRM\Mcp\Traits\Service\McpTokenClientResolverTrait;
use Throwable;

/**
 * MCP HTTP entry point (Streamable HTTP: one HTTP request per JSON-RPC message).
 *
 * Responsibilities, in order: module gate, auth gate, TLS gate, envelope parse,
 * dispatch, encode. Method semantics live in the Method handlers.
 *
 * Implements PublicControllerInterface deliberately -- otherwise
 * AuthenticationSubscriber redirects unauthenticated requests to the login page
 * and the 401 branch below, which is what drives OAuth discovery, is
 * unreachable.
 */
class McpController extends AbstractController implements PublicControllerInterface
{
    use McpEnabledTrait;
    use AuthUserTrait;
    use McpDispatcherTrait;
    use McpSessionContextTrait;
    use McpOperatorLoggerTrait;
    use McpAccessPolicyTrait;
    use McpTokenClientResolverTrait;

    public function handle(Request $request): Response
    {
        $correlationId = bin2hex(random_bytes(8));

        if (!$this->isMcpModuleEnabled()) {
            return $this->error(
                null,
                ErrorCode::MODULE_DISABLED,
                'MCP module is disabled',
                ['reason' => 'module_disabled', 'correlation_id' => $correlationId]
            );
        }

        if (!$this->getAuthUser()->isAuthenticated()) {
            return $this->unauthenticated($request, $correlationId);
        }

        // Organisation policy gate. Distinct from the per-call RBAC that
        // RestDispatcher enforces: this answers whether this person should be
        // reaching OrangeHRM through an AI assistant at all.
        if (!$this->getMcpAccessPolicy()->isEnabled()) {
            return $this->error(null, ErrorCode::MODULE_DISABLED, 'MCP access is disabled', [
                'category' => ErrorCode::CATEGORY_AUTHORIZATION,
                'correlation_id' => $correlationId,
                'hint' => 'An administrator must enable MCP under Admin > Configuration > MCP Configuration.',
            ]);
        }

        if (!$this->getMcpAccessPolicy()->mayConnect(
            $this->getAuthUser()->getUserId(),
            $this->getAuthUser()->getUserRoleId()
        )) {
            return $this->error(null, ErrorCode::UNAUTHENTICATED, 'Your account is not permitted to use MCP', [
                'category' => ErrorCode::CATEGORY_AUTHORIZATION,
                'correlation_id' => $correlationId,
                'hint' => 'Ask an administrator to allow your role, or you specifically, under MCP Configuration.',
            ]);
        }

        // Token audience (README 4.13): a token minted for another OrangeHRM
        // application must not be replayable against the AI surface.
        if ($this->getMcpAccessPolicy()->isClientApprovalEnforced()) {
            $clientId = $this->getMcpTokenClientResolver()->resolveClientId($request);
            if (!$this->getMcpAccessPolicy()->isClientApproved($clientId)) {
                return $this->error(null, ErrorCode::UNAUTHENTICATED, 'This application is not approved for MCP', [
                    'category' => ErrorCode::CATEGORY_AUTHORIZATION,
                    'correlation_id' => $correlationId,
                    'hint' => 'An administrator must approve this OAuth client under MCP Configuration.',
                ]);
            }
            $this->getMcpSessionContext()->setClientId($clientId);
        }

        if (!$this->isTransportSecure($request)) {
            return $this->error(
                null,
                ErrorCode::INSECURE_TRANSPORT,
                'MCP requires TLS on non-localhost deployments',
                [
                    'category' => ErrorCode::CATEGORY_AUTHORIZATION,
                    'correlation_id' => $correlationId,
                    'hint' => 'Terminate TLS at your reverse proxy and forward X-Forwarded-Proto.',
                ]
            );
        }

        $this->getMcpSessionContext()->setSessionId($request->headers->get(SessionContext::HEADER));

        $payload = json_decode((string) $request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(null, ErrorCode::PARSE_ERROR, 'Parse error', [
                'correlation_id' => $correlationId,
            ]);
        }

        try {
            $envelope = Envelope::fromPayload($payload);
        } catch (EnvelopeException $e) {
            return $this->error($this->safeId($payload), $e->getCode(), $e->getMessage(), array_merge(
                $e->getErrorData(),
                ['correlation_id' => $correlationId]
            ));
        }

        if (isset($payload['params']['clientInfo']['name'])) {
            $this->getMcpSessionContext()->setClientId((string) $payload['params']['clientInfo']['name']);
        }

        // Notifications get no body. Streamable HTTP wants 202 Accepted here.
        if ($envelope->isNotification()) {
            $this->dispatchNotification($envelope, $correlationId);
            $response = $this->getResponse();
            $response->setStatusCode(Response::HTTP_ACCEPTED);
            $response->setContent('');
            return $response;
        }

        $startedAt = microtime(true);
        try {
            $result = $this->getMcpDispatcher()->dispatch($envelope);
        } catch (EnvelopeException $e) {
            $this->logCall($envelope, $correlationId, $e->getCode(), $startedAt);
            return $this->error($envelope->getId(), $e->getCode(), $e->getMessage(), array_merge(
                $e->getErrorData(),
                ['correlation_id' => $correlationId, 'session_id' => $this->getMcpSessionContext()->getSessionId()]
            ));
        } catch (Throwable $e) {
            $this->logCall($envelope, $correlationId, ErrorCode::INTERNAL_ERROR, $startedAt);
            return $this->error($envelope->getId(), ErrorCode::INTERNAL_ERROR, 'Internal error', [
                'category' => ErrorCode::CATEGORY_INTERNAL,
                'correlation_id' => $correlationId,
                'session_id' => $this->getMcpSessionContext()->getSessionId(),
            ]);
        }

        $this->logCall($envelope, $correlationId, 200, $startedAt);

        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set(SessionContext::HEADER, $this->getMcpSessionContext()->getSessionId());
        $response->setContent(Envelope::encodeResult($envelope->getId(), $result));
        return $response;
    }

    /**
     * Notifications are fire-and-forget; an unknown one must not be an error.
     */
    private function dispatchNotification(Envelope $envelope, string $correlationId): void
    {
        if (!$this->getMcpDispatcher()->has($envelope->getMethod())) {
            return;
        }
        try {
            $this->getMcpDispatcher()->dispatch($envelope);
        } catch (Throwable $e) {
            $this->getMcpOperatorLogger()->logCall(
                $this->getMcpSessionContext()->getSessionId(),
                $envelope->getMethod(),
                $correlationId,
                'notification_failed',
                0,
                ErrorCode::CATEGORY_INTERNAL
            );
        }
    }

    /**
     * 401 carrying the RFC 9728 pointer. This is what bootstraps the whole OAuth
     * flow: the client reads resource_metadata, fetches the two well-known
     * documents, and starts the authorization code exchange.
     */
    private function unauthenticated(Request $request, string $correlationId): Response
    {
        $metadataUrl = OAuthMetadataController::getAppOrigin($request)
            . OAuthMetadataController::PROTECTED_RESOURCE_PATH;

        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set(
            'WWW-Authenticate',
            sprintf('Bearer realm="OrangeHRM MCP", resource_metadata="%s"', $metadataUrl)
        );
        $response->setContent(Envelope::encodeError(null, ErrorCode::UNAUTHENTICATED, 'Unauthenticated', [
            'category' => ErrorCode::CATEGORY_AUTHORIZATION,
            'correlation_id' => $correlationId,
            'resource_metadata' => $metadataUrl,
        ]));
        return $response;
    }

    /**
     * TLS is required off-localhost. Honours X-Forwarded-Proto so a reverse
     * proxy terminating TLS is accepted.
     */
    private function isTransportSecure(Request $request): bool
    {
        if ($request->isSecure()) {
            return true;
        }
        $forwardedProto = $request->headers->get('X-Forwarded-Proto');
        if (is_string($forwardedProto) && strtolower(trim(explode(',', $forwardedProto)[0])) === 'https') {
            return true;
        }

        $host = strtolower((string) parse_url('http://' . $request->getHttpHost(), PHP_URL_HOST));
        return in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true);
    }

    /**
     * @param mixed $payload
     * @return string|int|null
     */
    private function safeId($payload)
    {
        if (!is_array($payload)) {
            return null;
        }
        $id = $payload['id'] ?? null;
        return is_string($id) || is_int($id) ? $id : null;
    }

    private function logCall(Envelope $envelope, string $correlationId, int $status, float $startedAt): void
    {
        $this->getMcpOperatorLogger()->logCall(
            $this->getMcpSessionContext()->getSessionId(),
            $envelope->getMethod(),
            $correlationId,
            (string) $status,
            (int) round((microtime(true) - $startedAt) * 1000),
            $status === 200 ? null : ErrorCode::CATEGORY_INTERNAL
        );
    }

    /**
     * @param string|int|null $id
     */
    private function error($id, int $code, string $message, array $data = []): Response
    {
        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(Envelope::encodeError($id, $code, $message, $data));
        return $response;
    }
}
