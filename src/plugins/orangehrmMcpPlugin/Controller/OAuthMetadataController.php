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
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;

/**
 * RFC 9728 protected-resource metadata and RFC 8414 authorization-server
 * metadata. Without these an MCP client cannot discover how to authenticate and
 * the connection fails before any JSON-RPC is exchanged.
 *
 * Host origin versus app origin matters: MCP clients strip the path when
 * building the well-known URL, so the issuer must sit at the host root, while
 * the OAuth endpoints themselves live under whatever subpath OrangeHRM is
 * mounted at. X-Forwarded-* is honoured so installs behind a reverse proxy
 * advertise the public URL rather than the internal one.
 *
 * Cacheable, but `private` rather than `public`. Every OAuth provider caches
 * discovery -- clients fetch it on each connection and it changes rarely -- and
 * the ones that mark it `public` can, because their metadata is identical for
 * every requester. Ours is not: the URLs are built from the caller's own
 * forwarded headers, so the response genuinely varies by requester and `public`
 * would be a false claim about it.
 *
 * It is also the difference between a theoretical problem and a real one. In a
 * shared cache these documents are stored under a key that ignores the header
 * that shaped them, so one crafted request can leave every later client being
 * told the token endpoint lives on a host the attacker chose. `private` keeps
 * client-side caching, which is all anyone wanted, and keeps the document out of
 * caches that would serve it to someone else.
 */
class OAuthMetadataController extends AbstractController implements PublicControllerInterface
{
    public const MCP_RESOURCE_PATH = '/api/mcp';
    public const PROTECTED_RESOURCE_PATH = '/.well-known/oauth-protected-resource';
    public const AUTHORIZATION_SERVER_PATH = '/.well-known/oauth-authorization-server';

    public function protectedResource(Request $request): Response
    {
        return $this->json([
            'resource' => self::getAppOrigin($request) . self::MCP_RESOURCE_PATH,
            'authorization_servers' => [self::getHostOrigin($request)],
            'bearer_methods_supported' => ['header'],
            'resource_name' => 'OrangeHRM MCP',
        ]);
    }

    public function authorizationServer(Request $request): Response
    {
        $appOrigin = self::getAppOrigin($request);

        return $this->json([
            // Must equal the value advertised in the protected-resource document
            // and the well-known URL the client actually looked up.
            'issuer' => self::getHostOrigin($request),
            'authorization_endpoint' => $appOrigin . '/oauth2/authorize',
            'token_endpoint' => $appOrigin . '/oauth2/token',
            'response_types_supported' => ['code'],
            'response_modes_supported' => ['query'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post', 'none'],
            'code_challenge_methods_supported' => ['S256'],
            'scopes_supported' => [],
        ]);
    }

    /**
     * Origin plus the subpath OrangeHRM is mounted at.
     */
    public static function getAppOrigin(Request $request): string
    {
        return rtrim(self::getHostOrigin($request) . $request->getBaseUrl(), '/');
    }

    /**
     * Scheme and host only, proxy headers honoured.
     */
    public static function getHostOrigin(Request $request): string
    {
        $forwardedProto = $request->headers->get('X-Forwarded-Proto');
        $scheme = $forwardedProto !== null && $forwardedProto !== ''
            ? trim(explode(',', $forwardedProto)[0])
            : $request->getScheme();

        $forwardedHost = $request->headers->get('X-Forwarded-Host');
        $host = $forwardedHost !== null && $forwardedHost !== ''
            ? trim(explode(',', $forwardedHost)[0])
            : $request->getHttpHost();

        return $scheme . '://' . $host;
    }

    private function json(array $data): Response
    {
        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Cache-Control', 'private, max-age=3600');
        $response->setContent(json_encode($data, JSON_UNESCAPED_SLASHES));
        return $response;
    }
}
