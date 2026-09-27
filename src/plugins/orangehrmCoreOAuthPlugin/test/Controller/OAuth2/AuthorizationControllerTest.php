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

namespace OrangeHRM\Tests\OAuth\Controller\OAuth2;

use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Services;
use OrangeHRM\OAuth\Controller\OAuth2\AuthorizationController;
use OrangeHRM\OAuth\Service\PsrHttpFactoryHelper;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * Consent is the act that creates tokens, so it has to be reached by a person
 * signed in to OrangeHRM -- never by presenting a token.
 *
 * Without that, OAuthSubscriber (which authenticates a bearer on every route)
 * would let an access token authorise the minting of another one, for any
 * registered client, with the code readable straight off the 302 and no consent
 * screen ever drawn. That defeats the audience check entirely.
 *
 * @group OAuth
 * @group Controller
 */
class AuthorizationControllerTest extends KernelTestCase
{
    /**
     * @param int|null $oauthClientRowId set by OAuthSubscriber on a bearer
     *                                   request, absent on a session login
     */
    private function controllerFor(?int $oauthClientRowId): AuthorizationController
    {
        $authUser = $this->getMockBuilder(AuthUser::class)->disableOriginalConstructor()->getMock();
        $authUser->method('isAuthenticated')->willReturn(true);
        $authUser->method('getAttribute')->willReturnCallback(
            static function (string $name) use ($oauthClientRowId) {
                return $name === AuthUser::OAUTH_CLIENT_ID ? $oauthClientRowId : null;
            }
        );

        $this->createKernelWithMockServices([
            Services::AUTH_USER => $authUser,
            // Both paths build their response through this, including the
            // refusal, so it has to be present for either assertion to mean
            // anything.
            Services::PSR_HTTP_FACTORY_HELPER => new PsrHttpFactoryHelper(),
        ]);

        return new AuthorizationController();
    }

    private function errorTypeOf(AuthorizationController $controller): string
    {
        $response = $controller->handle(Request::create('/oauth2/authorize/consent', 'GET'));

        return (string) json_decode($response->getContent(), true)['error'];
    }

    public function testConsentIsRefusedWhenTheRequestCarriesAnAccessToken(): void
    {
        $this->assertSame('access_denied', $this->errorTypeOf($this->controllerFor(7)));
    }

    /**
     * The counterpart, and what makes the test above mean something: a session
     * request reaches the OAuth server and fails there instead, on the request
     * parameters this bare request does not carry. Any error but access_denied
     * proves it got past the guard.
     */
    public function testASessionRequestReachesTheOAuthServer(): void
    {
        $this->assertNotSame('access_denied', $this->errorTypeOf($this->controllerFor(null)));
    }
}
