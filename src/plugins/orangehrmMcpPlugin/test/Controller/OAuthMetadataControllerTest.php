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

namespace OrangeHRM\Tests\Mcp\Controller;

use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Mcp\Controller\OAuthMetadataController;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * The two RFC documents an MCP client reads before it can authenticate at all.
 *
 * @group Mcp
 * @group Controller
 */
class OAuthMetadataControllerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $this->createKernelWithMockServices([]);
    }

    private function request(array $server = []): Request
    {
        return Request::create('/.well-known/oauth-authorization-server', 'GET', [], [], [], $server);
    }

    /**
     * These URLs are built from the caller's own forwarded headers, so the
     * response varies by requester. A shared cache stores it under a key that
     * ignores those headers, which is how one crafted request ends up telling
     * every later client that the token endpoint is on a host the attacker
     * chose. `private` keeps the client-side caching everyone actually wants and
     * keeps it out of caches that would hand it to someone else.
     */
    public function testDiscoveryIsCacheableByTheClientButNotByAShieldCache(): void
    {
        $header = (new OAuthMetadataController())
            ->authorizationServer($this->request())
            ->headers->get('Cache-Control');

        $this->assertStringContainsString('private', $header);
        $this->assertStringNotContainsString('public', $header);
        $this->assertStringContainsString('max-age=3600', $header, 'clients should still cache discovery');
    }

    public function testProtectedResourceDocumentIsCachedTheSameWay(): void
    {
        $header = (new OAuthMetadataController())
            ->protectedResource($this->request())
            ->headers->get('Cache-Control');

        $this->assertStringContainsString('private', $header);
        $this->assertStringNotContainsString('public', $header);
    }

    /**
     * The behaviour that makes the header matter: the document is shaped by
     * headers the caller sets. Pinned so that if the origin ever becomes
     * request-independent, whoever makes that change sees this test and can
     * revisit the caching decision along with it.
     */
    public function testForwardedHeadersShapeTheAdvertisedEndpoints(): void
    {
        $response = (new OAuthMetadataController())->authorizationServer($this->request([
            'HTTP_X_FORWARDED_HOST' => 'elsewhere.example',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]));

        $document = json_decode($response->getContent(), true);
        $this->assertSame('https://elsewhere.example', $document['issuer']);
        $this->assertStringStartsWith('https://elsewhere.example', $document['token_endpoint']);
    }
}
