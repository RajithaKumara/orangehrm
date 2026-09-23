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

namespace OrangeHRM\Tests\Mcp\Service;

use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Entity\OAuthClient;
use OrangeHRM\Framework\Services;
use OrangeHRM\Mcp\Service\TokenClientResolver;
use OrangeHRM\OAuth\Dao\OAuthClientDao;
use OrangeHRM\OAuth\Service\OAuthService;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Service
 */
class TokenClientResolverTest extends KernelTestCase
{
    /**
     * @param mixed $storedRowId what OAuthSubscriber left on AuthUser, if anything
     * @param array<int,string> $clients row id => client_id
     */
    private function resolverWith($storedRowId, array $clients = []): TokenClientResolver
    {
        $authUser = $this->getMockBuilder(AuthUser::class)->disableOriginalConstructor()->getMock();
        $authUser->method('getAttribute')->willReturnCallback(
            static function (string $name) use ($storedRowId) {
                return $name === AuthUser::OAUTH_CLIENT_ID ? $storedRowId : null;
            }
        );

        $dao = $this->getMockBuilder(OAuthClientDao::class)->disableOriginalConstructor()->getMock();
        $dao->method('getOAuthClientById')->willReturnCallback(
            static function (int $id) use ($clients): ?OAuthClient {
                if (!isset($clients[$id])) {
                    return null;
                }
                $client = new OAuthClient();
                $client->setClientId($clients[$id]);
                return $client;
            }
        );

        $oauthService = $this->getMockBuilder(OAuthService::class)->getMock();
        $oauthService->method('getOAuthClientDao')->willReturn($dao);

        $this->createKernelWithMockServices([
            Services::AUTH_USER => $authUser,
            Services::OAUTH_SERVICE => $oauthService,
        ]);

        return new TokenClientResolver();
    }

    public function testResolvesTheClientThatPresentedTheToken(): void
    {
        $resolver = $this->resolverWith(7, [7 => 'claude_desktop', 9 => 'orangehrm_mobile_app']);

        $this->assertSame('claude_desktop', $resolver->resolveClientId());
    }

    /**
     * A session login never goes through OAuthSubscriber, so nothing is stored.
     * The caller must read that as "unknown", and McpConfigPolicy refuses an
     * unknown client whenever approval is enforced.
     */
    public function testSessionLoginResolvesToNull(): void
    {
        $this->assertNull($this->resolverWith(null)->resolveClientId());
    }

    /**
     * The token outlived its client row. Nothing to vouch for, so nothing is
     * claimed -- in particular the numeric id is not passed through as if it
     * were a client_id.
     */
    public function testDeletedClientResolvesToNull(): void
    {
        $this->assertNull($this->resolverWith(7, [9 => 'orangehrm_mobile_app'])->resolveClientId());
    }

    /**
     * AuthUser is session backed, so the stored value survives a round trip
     * through serialization and can come back as a string.
     */
    public function testRowIdStoredAsAStringStillResolves(): void
    {
        $this->assertSame('claude_desktop', $this->resolverWith('7', [7 => 'claude_desktop'])->resolveClientId());
    }

    /**
     * Whatever else an empty value might mean, it is not a client. Guarded
     * because casting '' to int yields 0, which would query for row zero.
     */
    public function testEmptyStoredValueResolvesToNull(): void
    {
        $this->assertNull($this->resolverWith('')->resolveClientId());
    }
}
