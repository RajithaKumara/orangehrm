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
