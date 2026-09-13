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
