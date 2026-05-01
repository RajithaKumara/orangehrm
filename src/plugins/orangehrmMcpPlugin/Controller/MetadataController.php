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
        $origin = $this->getOrigin($request);

        return $this->json([
            'resource' => $origin . self::MCP_RESOURCE_PATH,
            'authorization_servers' => [$origin],
            'bearer_methods_supported' => ['header'],
            'resource_name' => 'OrangeHRM MCP',
        ]);
    }

    public function authorizationServer(HttpRequest $request): HttpResponse
    {
        $origin = $this->getOrigin($request);

        return $this->json([
            'issuer' => $origin,
            'authorization_endpoint' => $origin . '/oauth2/authorize',
            'token_endpoint' => $origin . '/oauth2/token',
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
        return rtrim($request->getSchemeAndHttpHost() . $request->getBaseUrl(), '/');
    }

    private function json(array $data): HttpResponse
    {
        $response = new HttpResponse(json_encode($data, JSON_UNESCAPED_SLASHES));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        return $response;
    }
}
