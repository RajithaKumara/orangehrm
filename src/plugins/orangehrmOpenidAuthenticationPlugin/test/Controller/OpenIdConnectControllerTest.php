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

namespace OrangeHRM\Tests\OpenidAuthentication\Controller;

use DateTime;
use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Authentication\Service\AuthenticationService;
use OrangeHRM\Authentication\Service\LoginService;
use RuntimeException;
use OrangeHRM\Config\Config;
use OrangeHRM\Core\Api\V2\Exception\BadRequestException;
use OrangeHRM\Core\Authorization\Service\HomePageService;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Core\Service\DateTimeHelperService;
use OrangeHRM\Entity\LoginLog;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Session\MemorySessionStorage;
use OrangeHRM\Framework\Http\Session\Session;
use OrangeHRM\Framework\Logger\Logger;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use OrangeHRM\OpenidAuthentication\Controller\OpenIdConnectLoginController;
use OrangeHRM\OpenidAuthentication\Controller\OpenIdConnectRedirectController;
use OrangeHRM\OpenidAuthentication\OpenID\OpenIDConnectClient;
use OrangeHRM\OpenidAuthentication\Service\SocialMediaAuthenticationService;
use OrangeHRM\Tests\Util\KernelTestCase;
use OrangeHRM\Tests\Util\TestDataService;

/**
 * OIDC start (/openidauth/openIdCredentials/{providerId}) and callback (/openidauth/openIdCredentials).
 * The identity provider is replaced by a mocked OIDC client that returns the given claims.
 *
 * @group OpenId
 * @group Controller
 */
class OpenIdConnectControllerTest extends KernelTestCase
{
    private const SUB = 'b18df208-5cf2-4a8c-877e-2a9c77267cb2';
    private const ADMIN = 'vera.admin@orangehrm.us.com';
    private const ESS = 'manul@orangehrm.us.com';
    private const LOGIN_URL = 'http://orangehrm.test/web/index.php/auth/login';

    private Session $session;

    protected function setUp(): void
    {
        TestDataService::populate(
            Config::get(Config::PLUGINS_DIR) . '/orangehrmOpenidAuthenticationPlugin/test/fixtures/OpenIdConnectController.yml'
        );
        $this->getEntityManager()->createQueryBuilder()->delete(LoginLog::class, 'l')->getQuery()->execute();
    }

    protected function tearDown(): void
    {
        $connection = $this->getEntityManager()->getConnection();
        while ($connection->isTransactionActive()) {
            $connection->rollBack();
        }
        $this->getEntityManager()->createQueryBuilder()->delete(LoginLog::class, 'l')->getQuery()->execute();
        parent::tearDown();
    }

    /**
     * @param OpenIDConnectClient|null $client
     * @param string|null $trustedProviderIds opt-out config row value, null = no row
     */
    private function mockServices(?OpenIDConnectClient $client, ?string $trustedProviderIds = null): void
    {
        $this->session = new Session(new MemorySessionStorage());

        $service = $this->getMockBuilder(SocialMediaAuthenticationService::class)
            ->onlyMethods(['initiateAuthentication', 'getRedirectURL'])
            ->getMock();
        $service->method('getRedirectURL')->willReturn('http://orangehrm.test/web/index.php/openidauth/openIdCredentials');
        if ($client !== null) {
            $service->method('initiateAuthentication')->willReturn($client);
        } else {
            $service->expects($this->never())->method('initiateAuthentication');
        }

        $urlGenerator = $this->getMockBuilder(UrlGenerator::class)
            ->onlyMethods(['generate'])
            ->disableOriginalConstructor()
            ->getMock();
        $urlGenerator->method('generate')->willReturn(self::LOGIN_URL);

        $configService = $this->getMockBuilder(ConfigService::class)
            ->onlyMethods(['_getConfigValue'])
            ->getMock();
        $configService->method('_getConfigValue')->willReturnCallback(
            fn (string $key) => $key === ConfigService::KEY_OPENID_TRUST_EMAIL_WITHOUT_VERIFIED_CLAIM
                ? $trustedProviderIds
                : null
        );

        $dateTimeHelper = $this->getMockBuilder(DateTimeHelperService::class)
            ->onlyMethods(['getNow'])
            ->getMock();
        $dateTimeHelper->method('getNow')->willReturn(new DateTime('2026-11-16 09:00:00'));

        $this->createKernelWithMockServices([
            Services::SESSION => $this->session,
            Services::AUTH_USER => AuthUser::getInstance(),
            Services::SOCIAL_MEDIA_AUTH_SERVICE => $service,
            Services::AUTHENTICATION_SERVICE => new AuthenticationService(),
            Services::URL_GENERATOR => $urlGenerator,
            Services::CONFIG_SERVICE => $configService,
            Services::DATETIME_HELPER_SERVICE => $dateTimeHelper,
            Services::LOGGER => $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock(),
        ]);
    }

    private function client(array $idTokenClaims, array $userInfo): OpenIDConnectClient
    {
        $client = $this->getMockBuilder(OpenIDConnectClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate', 'getVerifiedClaims', 'requestUserInfo'])
            ->getMock();
        $client->method('authenticate')->willReturn(true);
        $client->method('getVerifiedClaims')->willReturn((object)$idTokenClaims);
        $client->method('requestUserInfo')->willReturn((object)$userInfo);
        return $client;
    }

    private function callbackController(?LoginService $loginService = null): OpenIdConnectRedirectController
    {
        return new class ($loginService) extends OpenIdConnectRedirectController {
            public function __construct(?LoginService $loginService)
            {
                parent::__construct();
                $this->loginService = $loginService;
            }

            public function getHomePageService(): HomePageService
            {
                return new class () extends HomePageService {
                    public function getHomePagePath(): ?string
                    {
                        return 'dashboard/index';
                    }
                };
            }

            public function handleSessionTimeoutRedirect(): ?string
            {
                return null;
            }

            protected function redirect(string $path): RedirectResponse
            {
                return new RedirectResponse('http://orangehrm.test/web/index.php/' . $path);
            }
        };
    }

    /**
     * @return array{0: RedirectResponse, 1: bool, 2: string|null}
     *         [response, authenticated, flashed login error]
     */
    private function runCallback(int $sessionProviderId, array $idTokenClaims, array $userInfo, ?string $trusted = null): array
    {
        $this->mockServices($this->client($idTokenClaims, $userInfo), $trusted);
        $this->session->set(AuthUser::OPENID_PROVIDER_ID, $sessionProviderId);
        $response = $this->callbackController()->handle(new Request());
        $flash = $this->session->getFlashBag()->get(AuthUser::FLASH_LOGIN_ERROR);
        return [
            $response,
            AuthUser::getInstance()->isAuthenticated(),
            $flash[0]['error'] ?? null,
        ];
    }

    private function loginLogUsers(): array
    {
        return array_map(
            fn (LoginLog $log) => $log->getUserName(),
            $this->getEntityManager()->getRepository(LoginLog::class)->findAll()
        );
    }

    private function assertRefused(array $result): void
    {
        [$response, $authenticated, $error] = $result;
        $this->assertFalse($authenticated, 'session must not be authenticated');
        $this->assertSame(self::LOGIN_URL, $response->getTargetUrl());
        $this->assertNotNull($error);
        $this->assertSame([], $this->loginLogUsers());
        $this->assertFalse($this->getEntityManager()->getConnection()->isTransactionActive());
    }

    public function testVerifiedEmailSignsInAndIsLogged(): void
    {
        [$response, $authenticated, $error] = $this->runCallback(
            1,
            ['sub' => self::SUB, 'email' => self::ESS, 'email_verified' => true],
            ['sub' => self::SUB, 'email' => self::ESS, 'email_verified' => true]
        );
        $this->assertTrue($authenticated);
        $this->assertNull($error);
        $this->assertSame('http://orangehrm.test/web/index.php/dashboard/index', $response->getTargetUrl());
        $this->assertSame(2, AuthUser::getInstance()->getUserId());
        // committed, not left open
        $this->assertFalse($this->getEntityManager()->getConnection()->isTransactionActive());
        $this->getEntityManager()->clear();
        $this->assertSame([self::ESS], $this->loginLogUsers());
    }

    public function testFailedSignInLogLeavesNoUserInSession(): void
    {
        $this->mockServices($this->client(
            ['sub' => self::SUB, 'email' => self::ESS, 'email_verified' => true],
            ['sub' => self::SUB, 'email' => self::ESS, 'email_verified' => true]
        ));
        $this->session->set(AuthUser::OPENID_PROVIDER_ID, 1);
        $loginService = $this->getMockBuilder(LoginService::class)
            ->onlyMethods(['addOIDCLogin'])
            ->getMock();
        $loginService->method('addOIDCLogin')->willThrowException(new RuntimeException('database gone'));

        $response = $this->callbackController($loginService)->handle(new Request());
        $this->assertSame(self::LOGIN_URL, $response->getTargetUrl());
        $this->assertFalse(AuthUser::getInstance()->isAuthenticated());
        $this->assertNull(AuthUser::getInstance()->getUserId());
        $this->assertFalse($this->getEntityManager()->getConnection()->isTransactionActive());
    }

    public function testProviderIdIsRemovedFromSessionOnCallback(): void
    {
        $this->runCallback(
            1,
            ['sub' => self::SUB, 'email' => self::ESS, 'email_verified' => true],
            ['sub' => self::SUB, 'email' => self::ESS, 'email_verified' => true]
        );
        $this->assertFalse($this->session->has(AuthUser::OPENID_PROVIDER_ID));
    }

    public function testUnverifiedEmailOfAdminIsRefused(): void
    {
        $result = $this->runCallback(
            1,
            ['sub' => self::SUB, 'email' => self::ADMIN, 'email_verified' => false],
            ['sub' => self::SUB, 'email' => self::ADMIN, 'email_verified' => false]
        );
        $this->assertRefused($result);
        $this->assertSame('invalid_credentials', $result[2]);
        $this->assertNull(AuthUser::getInstance()->getUserId());
    }

    public function testEmailWithoutVerifiedClaimIsRefused(): void
    {
        $result = $this->runCallback(
            3,
            ['sub' => self::SUB, 'email' => self::ADMIN],
            ['sub' => self::SUB, 'email' => self::ADMIN]
        );
        $this->assertRefused($result);
    }

    public function testEmailWithoutVerifiedClaimAcceptedForListedProvider(): void
    {
        [, $authenticated] = $this->runCallback(
            3,
            ['sub' => self::SUB, 'email' => self::ESS],
            ['sub' => self::SUB, 'email' => self::ESS],
            '3'
        );
        $this->assertTrue($authenticated);
        $this->assertSame(2, AuthUser::getInstance()->getUserId());
    }

    public function testListingAnotherProviderDoesNotHelp(): void
    {
        $result = $this->runCallback(
            3,
            ['sub' => self::SUB, 'email' => self::ESS],
            ['sub' => self::SUB, 'email' => self::ESS],
            '1'
        );
        $this->assertRefused($result);
    }

    public function testUserinfoForAnotherSubjectIsRefused(): void
    {
        $result = $this->runCallback(
            1,
            ['sub' => self::SUB],
            ['sub' => 'another-subject', 'email' => self::ESS, 'email_verified' => true]
        );
        $this->assertRefused($result);
    }

    public function testDisabledProviderCallbackIsRefused(): void
    {
        $result = $this->runCallback(
            2,
            ['sub' => self::SUB, 'email' => self::ESS, 'email_verified' => true],
            ['sub' => self::SUB, 'email' => self::ESS, 'email_verified' => true]
        );
        $this->assertRefused($result);
        $this->assertSame('invalid_credentials', $result[2]);
    }

    public function testCallbackWithoutStartedFlowIsRefused(): void
    {
        $this->mockServices(null);
        $response = $this->callbackController()->handle(new Request());
        $this->assertSame(self::LOGIN_URL, $response->getTargetUrl());
        $this->assertFalse(AuthUser::getInstance()->isAuthenticated());
        $this->assertFalse($this->getEntityManager()->getConnection()->isTransactionActive());
    }

    public function testCallbackWithoutCodeIsRefused(): void
    {
        $client = $this->getMockBuilder(OpenIDConnectClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate', 'getVerifiedClaims', 'requestUserInfo'])
            ->getMock();
        // no ?code= : the library starts a new authorization request and returns false
        $client->method('authenticate')->willReturn(false);
        $client->expects($this->never())->method('requestUserInfo');
        $this->mockServices($client);
        $this->session->set(AuthUser::OPENID_PROVIDER_ID, 1);
        $response = $this->callbackController()->handle(new Request());
        $this->assertSame(self::LOGIN_URL, $response->getTargetUrl());
        $this->assertFalse(AuthUser::getInstance()->isAuthenticated());
    }

    public function testStartWithEnabledProviderStoresProviderId(): void
    {
        $client = $this->getMockBuilder(OpenIDConnectClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate', 'getGeneratedAuthUrl'])
            ->getMock();
        $client->method('authenticate')->willReturn(false);
        $client->method('getGeneratedAuthUrl')->willReturn('https://idp.example/auth?state=s&nonce=n');
        $this->mockServices($client);

        $request = new Request();
        $request->attributes->set('providerId', 1);
        $response = (new OpenIdConnectLoginController())->handle($request);
        $this->assertSame('https://idp.example/auth?state=s&nonce=n&prompt=consent', $response->getTargetUrl());
        $this->assertSame(1, $this->session->get(AuthUser::OPENID_PROVIDER_ID));
    }

    public function testStartWithDisabledProviderIsRefused(): void
    {
        $this->mockServices(null);
        $request = new Request();
        $request->attributes->set('providerId', 2);
        $this->expectException(BadRequestException::class);
        (new OpenIdConnectLoginController())->handle($request);
    }

    public function testStartWithUnknownProviderIsRefused(): void
    {
        $this->mockServices(null);
        $request = new Request();
        $request->attributes->set('providerId', 99);
        $this->expectException(BadRequestException::class);
        (new OpenIdConnectLoginController())->handle($request);
    }
}
