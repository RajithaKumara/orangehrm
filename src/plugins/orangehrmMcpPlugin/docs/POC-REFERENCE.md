# POC reference code (from the `ohrm-57` checkout, branch `mcp`)

Verbatim copy of the proof-of-concept MCP plugin that was built in the sibling
checkout `~/Documents/OHRM/ohrm-57` (four commits, ~600 lines, no tests). It is
kept here as **reference material for Epic 2**, not as code to drop in. Each file
is preceded by a note on what is right about it and what must change when it is
ported into this plugin's structure (`Mcp/Method/`, `Mcp/Tool/`, `Controller/`).

The POC was exercised against Claude with a pre-issued bearer token; the
discovery flow was never verified headlessly (see defect 1 below).

Known defects in this code, all confirmed by reading the framework:

1. Neither controller implements `PublicControllerInterface`. On an
   unauthenticated request `AuthenticationSubscriber::onControllerEvent` falls
   through to `throw new SessionExpiredException()`, which becomes a 302 to the
   login page. The 401 + `WWW-Authenticate` branch is unreachable, and the
   `.well-known` documents need a session cookie.
2. `PimMyselfTool::execute()` instantiates the API class directly. That skips
   `ApiAuthorizationSubscriber` (data-group RBAC, runs on the kernel CONTROLLER
   event for `AbstractRestController` only) and skips request validation
   (runs inside `AbstractRestController::handle()`). Harmless for `MyInfoAPI`,
   a privilege escalation as a general pattern. See the dispatch recipe in
   `../README.md`.
3. `PROTOCOL_VERSION` is `2024-11-05` while the discovery metadata is the
   2025-06-18 shape. Pin `2025-06-18`.
4. Notifications return HTTP 204. Streamable HTTP requires 202 Accepted.
5. No GPL license header on any file. Every PHP file in this repo starts with
   the 17-line header (copy from any existing plugin file).
6. `ToolRegistry` hardwires tools in its constructor. Here, tools come from the
   OpenAPI router plus DI-registered orchestration tools (architecture.md,
   "Tool Surface Architecture").

What the POC got right and this plugin must keep:

- Host-origin issuer versus app-origin endpoints in the OAuth metadata, and
  honoring `X-Forwarded-Proto` / `X-Forwarded-Host`. This is what makes
  `https://example.com/orangehrm/` installs behind nginx work with Claude.
- `WWW-Authenticate: Bearer realm=..., resource_metadata=...` on 401.
- JSON-RPC error codes and the `isError: true` tool-result shape for tool-level
  failures (not a JSON-RPC error).
- Rejecting batch requests explicitly.
- Returning `$result->normalize()`-style data as `text` content.

---

## `config/routes.yaml`

```yaml
mcp_endpoint:
  path: /api/mcp
  controller: OrangeHRM\Mcp\Controller\McpController::handle
  methods: [ POST ]

mcp_protected_resource_metadata:
  path: /.well-known/oauth-protected-resource
  controller: OrangeHRM\Mcp\Controller\MetadataController::protectedResource
  methods: [ GET ]

mcp_authorization_server_metadata:
  path: /.well-known/oauth-authorization-server
  controller: OrangeHRM\Mcp\Controller\MetadataController::authorizationServer
  methods: [ GET ]
```

Port note: also register the RFC 9728 path-suffixed variant
`/.well-known/oauth-protected-resource/api/mcp` pointing at the same action, and
add `GET` and `DELETE` to the MCP route (GET returns 405 because there is no
server-push stream; DELETE ends the session once Story 2.3 exists).

---

## `Controller/McpController.php`

Port note: this becomes `Mcp/JsonRpc/Dispatcher` plus one handler class per
method under `Mcp/Method/` (Story 2.1). The controller keeps only: module gate,
auth gate (401 branch), envelope parse, dispatch, encode. Add
`implements PublicControllerInterface`. Replace the `switch` with a handler map.
Change 204 to 202 for notifications. Keep the error-code constants.

```php
<?php

namespace OrangeHRM\Mcp\Controller;

use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\LoggerTrait;
use OrangeHRM\Framework\Http\Request as HttpRequest;
use OrangeHRM\Framework\Http\Response as HttpResponse;
use OrangeHRM\Mcp\Server\ToolInterface;
use OrangeHRM\Mcp\Server\ToolRegistry;
use Throwable;

use function sprintf;

class McpController extends AbstractController
{
    use AuthUserTrait;
    use LoggerTrait;

    public const PROTOCOL_VERSION = '2024-11-05';
    public const SERVER_NAME = 'orangehrm-mcp';
    public const SERVER_VERSION = '0.1.0';

    private const ERR_PARSE = -32700;
    private const ERR_INVALID_REQUEST = -32600;
    private const ERR_METHOD_NOT_FOUND = -32601;
    private const ERR_INVALID_PARAMS = -32602;
    private const ERR_INTERNAL = -32603;

    private ToolRegistry $registry;

    public function __construct()
    {
        $this->registry = new ToolRegistry();
    }

    public function handle(HttpRequest $httpRequest): HttpResponse
    {
        $response = new HttpResponse();
        $response->headers->set('Content-Type', 'application/json');

        if (!$this->getAuthUser()->isAuthenticated()) {
            $metadataUrl = MetadataController::getOrigin($httpRequest)
                . MetadataController::PROTECTED_RESOURCE_PATH;
            $response->setStatusCode(401);
            $response->headers->set(
                'WWW-Authenticate',
                sprintf('Bearer realm="OrangeHRM MCP", resource_metadata="%s"', $metadataUrl)
            );
            $response->setContent(json_encode([
                'jsonrpc' => '2.0',
                'id' => null,
                'error' => [
                    'code' => self::ERR_INVALID_REQUEST,
                    'message' => 'Unauthorized: missing or invalid bearer token',
                ],
            ]));
            return $response;
        }

        $raw = $httpRequest->getContent();
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $response->setContent($this->encodeError(null, self::ERR_PARSE, 'Parse error'));
            return $response;
        }

        // Batch requests are valid in JSON-RPC; out of scope for this POC.
        if (isset($payload[0])) {
            $response->setContent($this->encodeError(null, self::ERR_INVALID_REQUEST, 'Batch requests not supported'));
            return $response;
        }

        $id = $payload['id'] ?? null;
        $method = $payload['method'] ?? null;
        $params = $payload['params'] ?? [];

        if (!is_string($method)) {
            $response->setContent($this->encodeError($id, self::ERR_INVALID_REQUEST, 'Invalid Request'));
            return $response;
        }

        try {
            switch ($method) {
                case 'initialize':
                    $result = $this->handleInitialize($params);
                    break;
                case 'notifications/initialized':
                case 'notifications/cancelled':
                    // Notifications: no response body.
                    $response->setStatusCode(204);
                    $response->setContent('');
                    return $response;
                case 'ping':
                    $result = (object)[];
                    break;
                case 'tools/list':
                    $result = $this->handleToolsList();
                    break;
                case 'tools/call':
                    $result = $this->handleToolsCall($params);
                    break;
                default:
                    $response->setContent(
                        $this->encodeError($id, self::ERR_METHOD_NOT_FOUND, 'Method not found: ' . $method)
                    );
                    return $response;
            }
        } catch (Throwable $e) {
            $this->getLogger()->error('[MCP] ' . $e->getMessage());
            $this->getLogger()->error($e->getTraceAsString());
            $response->setContent(
                $this->encodeError($id, self::ERR_INTERNAL, 'Internal error: ' . $e->getMessage())
            );
            return $response;
        }

        $response->setContent(json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ], JSON_UNESCAPED_SLASHES));
        return $response;
    }

    private function handleInitialize(array $params): array
    {
        return [
            'protocolVersion' => self::PROTOCOL_VERSION,
            'capabilities' => [
                'tools' => (object)[],
            ],
            'serverInfo' => [
                'name' => self::SERVER_NAME,
                'version' => self::SERVER_VERSION,
            ],
        ];
    }

    private function handleToolsList(): array
    {
        $tools = [];
        foreach ($this->registry->all() as $tool) {
            $tools[] = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'inputSchema' => $tool->getInputSchema(),
            ];
        }
        return ['tools' => $tools];
    }

    private function handleToolsCall(array $params): array
    {
        $name = $params['name'] ?? null;
        $arguments = $params['arguments'] ?? [];

        if (!is_string($name)) {
            return $this->toolError('Missing tool name');
        }
        if (!is_array($arguments)) {
            return $this->toolError('Arguments must be an object');
        }

        $tool = $this->registry->get($name);
        if (!$tool instanceof ToolInterface) {
            return $this->toolError('Unknown tool: ' . $name);
        }

        try {
            $data = $tool->execute($arguments);
        } catch (Throwable $e) {
            $this->getLogger()->error('[MCP] tool ' . $name . ' failed: ' . $e->getMessage());
            return $this->toolError($e->getMessage());
        }

        return [
            'content' => [
                ['type' => 'text', 'text' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)],
            ],
            'isError' => false,
        ];
    }

    private function toolError(string $message): array
    {
        return [
            'content' => [['type' => 'text', 'text' => $message]],
            'isError' => true,
        ];
    }

    private function encodeError($id, int $code, string $message): string
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => ['code' => $code, 'message' => $message],
        ]);
    }
}
```

---

## `Controller/MetadataController.php`

Port note: keep almost as-is. Add `implements PublicControllerInterface`, the
GPL header, and read `MCP_RESOURCE_PATH` from one shared constant with the
route. Consider `registration_endpoint` once dynamic client registration exists.
The `Cache-Control: public` header is correct; these documents carry no secrets.

```php
<?php

namespace OrangeHRM\Mcp\Controller;

use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Framework\Http\Request as HttpRequest;
use OrangeHRM\Framework\Http\Response as HttpResponse;

class MetadataController extends AbstractController
{
    public const MCP_RESOURCE_PATH = '/api/mcp';
    public const PROTECTED_RESOURCE_PATH = '/.well-known/oauth-protected-resource';
    public const AUTHORIZATION_SERVER_PATH = '/.well-known/oauth-authorization-server';

    public function protectedResource(HttpRequest $request): HttpResponse
    {
        $hostOrigin = $this->getHostOrigin($request);
        $appOrigin = $this->getOrigin($request);

        return $this->json([
            'resource' => $appOrigin . self::MCP_RESOURCE_PATH,
            // Use host-root as the issuer — Claude (and several other MCP
            // clients) strip the path when constructing the RFC 8414
            // well-known URL, so the issuer must live at the host root.
            'authorization_servers' => [$hostOrigin],
            'bearer_methods_supported' => ['header'],
            'resource_name' => 'OrangeHRM MCP',
        ]);
    }

    public function authorizationServer(HttpRequest $request): HttpResponse
    {
        $hostOrigin = $this->getHostOrigin($request);
        $appOrigin = $this->getOrigin($request);

        return $this->json([
            // Issuer must match the value advertised in the protected-resource
            // doc and equal the well-known URL Claude looked up (host root).
            'issuer' => $hostOrigin,
            // OAuth endpoints can live anywhere; advertise their real subpath.
            'authorization_endpoint' => $appOrigin . '/oauth2/authorize',
            'token_endpoint' => $appOrigin . '/oauth2/token',
            'response_types_supported' => ['code'],
            'response_modes_supported' => ['query'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post'],
            'code_challenge_methods_supported' => ['S256'],
            'scopes_supported' => [],
        ]);
    }

    public static function getOrigin(HttpRequest $request): string
    {
        return rtrim(self::getHostOrigin($request) . $request->getBaseUrl(), '/');
    }

    public static function getHostOrigin(HttpRequest $request): string
    {
        $forwardedProto = $request->headers->get('X-Forwarded-Proto');
        $scheme = $forwardedProto !== null
            ? trim(explode(',', $forwardedProto)[0])
            : $request->getScheme();

        $forwardedHost = $request->headers->get('X-Forwarded-Host');
        $host = $forwardedHost !== null
            ? trim(explode(',', $forwardedHost)[0])
            : $request->getHttpHost();

        return $scheme . '://' . $host;
    }

    private function json(array $data): HttpResponse
    {
        $response = new HttpResponse(json_encode($data, JSON_UNESCAPED_SLASHES));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        return $response;
    }
}
```

---

## `Server/ToolInterface.php`

Port note: the architecture's `OrchestrationToolInterface` (architecture.md,
"Tool Surface Architecture") supersedes this. It adds `getApiClass()`,
`getEntityScopedParams()`, `getExamples()`, `requiresConfirmation()`,
`additionalVisibilityCheck()`, and `dispatch(McpInvocationContext)`.

```php
<?php

namespace OrangeHRM\Mcp\Server;

interface ToolInterface
{
    public function getName(): string;

    public function getDescription(): string;

    /**
     * JSON Schema describing arguments accepted by the tool.
     */
    public function getInputSchema(): array;

    /**
     * Execute the tool with the given arguments.
     * Return value is encoded as JSON text content in the MCP response.
     */
    public function execute(array $arguments): array;
}
```

---

## `Server/ToolRegistry.php`

Port note: replace with the three-source registry (OpenAPI-derived tools,
overlay, DI-registered orchestration tools). Only the `get()` / `all()` shape
survives.

```php
<?php

namespace OrangeHRM\Mcp\Server;

use OrangeHRM\Mcp\Tool\PimMyselfTool;

class ToolRegistry
{
    /** @var ToolInterface[] */
    private array $tools = [];

    public function __construct()
    {
        $this->register(new PimMyselfTool());
    }

    public function register(ToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /** @return ToolInterface[] */
    public function all(): array
    {
        return array_values($this->tools);
    }

    public function get(string $name): ?ToolInterface
    {
        return $this->tools[$name] ?? null;
    }
}
```

---

## `Tool/PimMyselfTool.php`

Port note: the schema and description are fine. `execute()` is the defect
described at the top: it must go through the permission check plus
`GenericRestController::handle()` (recipe in `../README.md`), never
`new MyInfoAPI(...)->getOne()`. This becomes `mcp_get_current_employee`
(Story 4.5) and is the first tool to bring up in Phase A.

```php
<?php

namespace OrangeHRM\Mcp\Tool;

use OrangeHRM\Core\Api\V2\Request as ApiRequest;
use OrangeHRM\Framework\Http\Request as HttpRequest;
use OrangeHRM\Mcp\Server\ToolInterface;
use OrangeHRM\Pim\Api\MyInfoAPI;

class PimMyselfTool implements ToolInterface
{
    public function getName(): string
    {
        return 'pim_myself';
    }

    public function getDescription(): string
    {
        return 'Get personal information for the currently authenticated employee. '
            . 'Wraps GET /api/v2/pim/myself.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'model' => [
                    'type' => 'string',
                    'enum' => [
                        MyInfoAPI::MODEL_DEFAULT,
                        MyInfoAPI::MODEL_SUMMARY,
                        MyInfoAPI::MODEL_DETAILED,
                    ],
                    'description' => 'Response model variant.',
                    'default' => MyInfoAPI::MODEL_DEFAULT,
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(array $arguments): array
    {
        $query = [];
        if (isset($arguments['model'])) {
            $query[MyInfoAPI::FILTER_MODEL] = $arguments['model'];
        }

        $httpRequest = new HttpRequest($query);
        $apiRequest = new ApiRequest($httpRequest);

        $api = new MyInfoAPI($apiRequest);   // DEFECT: bypasses RBAC and validation
        $result = $api->getOne();

        return $result->normalize();
    }
}
```
