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

namespace OrangeHRM\Tests\Core\Installer;

use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Installer\Util\AppSetupUtility;
use OrangeHRM\Installer\Util\ConfigHelper;
use OrangeHRM\Tests\Util\TestCase;

/**
 * Base URL recorded by the installer/upgrader for links in emails.
 *
 * @group Core
 * @group Installer
 */
class AppSetupUtilityInstanceBaseUrlTest extends TestCase
{
    /**
     * @dataProvider requestDataProvider
     */
    public function testGetInstanceBaseUrlFromRequest(Request $request, ?string $expected): void
    {
        $this->assertSame($expected, (new AppSetupUtility())->getInstanceBaseUrlFromRequest($request));
    }

    public function requestDataProvider(): array
    {
        $installerRequest = static function (string $uri, string $scriptName, array $server = []): Request {
            return Request::create(
                $uri,
                'POST',
                [],
                [],
                [],
                array_merge(['SCRIPT_NAME' => $scriptName, 'SCRIPT_FILENAME' => '/var/www' . $scriptName], $server)
            );
        };
        return [
            'web installer in a subpath' => [
                $installerRequest(
                    'http://hr.example.com/orangehrm/installer/index.php/installer/api/config-file',
                    '/orangehrm/installer/index.php'
                ),
                'http://hr.example.com/orangehrm/web/index.php',
            ],
            'web upgrader at the root, https, non-default port' => [
                $installerRequest(
                    'https://hr.example.com:8443/installer/index.php/upgrader/api/config-file',
                    '/installer/index.php'
                ),
                'https://hr.example.com:8443/web/index.php',
            ],
            'installer behind URL rewriting (no index.php)' => [
                $installerRequest(
                    'http://hr.example.com/orangehrm/installer/upgrader/api/config-file',
                    '/orangehrm/installer'
                ),
                'http://hr.example.com/orangehrm/web/index.php',
            ],
            'X-Forwarded-Host is ignored (no trusted proxies)' => [
                $installerRequest(
                    'http://hr.example.com/installer/index.php/upgrader/api/config-file',
                    '/installer/index.php',
                    ['HTTP_X_FORWARDED_HOST' => 'other.example', 'HTTP_X_FORWARDED_PROTO' => 'https']
                ),
                'http://hr.example.com/web/index.php',
            ],
            'CLI (synthetic request, no host)' => [
                (static function (): Request {
                    $request = new Request();
                    $request->setMethod(Request::METHOD_POST);
                    return $request;
                })(),
                null,
            ],
            'not served from the installer front controller' => [
                $installerRequest(
                    'http://hr.example.com/orangehrm/web/index.php/auth/login',
                    '/orangehrm/web/index.php'
                ),
                null,
            ],
        ];
    }

    public function testSaveInstanceBaseUrlStoresWhenUnset(): void
    {
        $configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfigValue', 'setConfigValue'])
            ->getMock();
        $configHelper->method('getConfigValue')
            ->with(ConfigService::KEY_INSTANCE_BASE_URL)
            ->willReturn(null);
        $configHelper->expects($this->once())
            ->method('setConfigValue')
            ->with(ConfigService::KEY_INSTANCE_BASE_URL, 'https://hr.example.com/orangehrm/web/index.php');

        $this->assertTrue(
            $this->getAppSetupUtility($configHelper)
                ->saveInstanceBaseUrl('https://hr.example.com/orangehrm/web/index.php/')
        );
    }

    public function testSaveInstanceBaseUrlKeepsExistingValue(): void
    {
        $configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfigValue', 'setConfigValue'])
            ->getMock();
        $configHelper->method('getConfigValue')
            ->willReturn('https://hr.example.com/orangehrm/web/index.php');
        $configHelper->expects($this->never())->method('setConfigValue');

        $this->assertFalse(
            $this->getAppSetupUtility($configHelper)->saveInstanceBaseUrl('http://staging.example.com/web/index.php')
        );
    }

    public function testSaveInstanceBaseUrlReplacesStoredValueWhenEnteredByAdmin(): void
    {
        $configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfigValue', 'setConfigValue'])
            ->getMock();
        $configHelper->method('getConfigValue')
            ->willReturn('http://staging.example.com/web/index.php');
        $configHelper->expects($this->once())
            ->method('setConfigValue')
            ->with(ConfigService::KEY_INSTANCE_BASE_URL, 'https://hr.example.com/orangehrm/web/index.php');

        $this->assertTrue(
            $this->getAppSetupUtility($configHelper)
                ->saveInstanceBaseUrl('https://hr.example.com/orangehrm/web/index.php', true)
        );
    }

    public function testGetStoredInstanceBaseUrl(): void
    {
        $configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfigValue'])
            ->getMock();
        $configHelper->method('getConfigValue')
            ->with(ConfigService::KEY_INSTANCE_BASE_URL)
            ->willReturnOnConsecutiveCalls('HTTPS://HR.example.com/web/index.php/', 'not a url', null);
        $appSetupUtility = $this->getAppSetupUtility($configHelper);

        $this->assertSame('https://hr.example.com/web/index.php', $appSetupUtility->getStoredInstanceBaseUrl());
        $this->assertNull($appSetupUtility->getStoredInstanceBaseUrl());
        $this->assertNull($appSetupUtility->getStoredInstanceBaseUrl());
    }

    public function testSaveInstanceBaseUrlReplacesInvalidStoredValue(): void
    {
        $configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfigValue', 'setConfigValue'])
            ->getMock();
        $configHelper->method('getConfigValue')->willReturn('not a url');
        $configHelper->expects($this->once())
            ->method('setConfigValue')
            ->with(ConfigService::KEY_INSTANCE_BASE_URL, 'http://hr.example.com/web/index.php');

        $this->assertTrue(
            $this->getAppSetupUtility($configHelper)->saveInstanceBaseUrl('http://hr.example.com/web/index.php')
        );
    }

    /**
     * @dataProvider invalidBaseUrlDataProvider
     */
    public function testSaveInstanceBaseUrlIgnoresMissingOrInvalidValue(?string $baseUrl): void
    {
        $configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfigValue', 'setConfigValue'])
            ->getMock();
        $configHelper->expects($this->never())->method('getConfigValue');
        $configHelper->expects($this->never())->method('setConfigValue');

        $this->assertFalse($this->getAppSetupUtility($configHelper)->saveInstanceBaseUrl($baseUrl));
    }

    public function invalidBaseUrlDataProvider(): array
    {
        return [[null], [''], ['hr.example.com'], ['ftp://hr.example.com/'], ['https://hr.example.com/?a=b']];
    }

    private function getAppSetupUtility(ConfigHelper $configHelper): AppSetupUtility
    {
        return new class ($configHelper) extends AppSetupUtility {
            private ConfigHelper $mockConfigHelper;

            public function __construct(ConfigHelper $configHelper)
            {
                $this->mockConfigHelper = $configHelper;
            }

            protected function getConfigHelper(): ConfigHelper
            {
                return $this->mockConfigHelper;
            }
        };
    }
}
