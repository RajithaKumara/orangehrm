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

use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Core\Dao\ModuleDao;
use OrangeHRM\Core\Service\ModuleService;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Services;
use OrangeHRM\Mcp\Controller\McpController;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Controller
 */
class McpControllerTest extends KernelTestCase
{
    private function buildControllerWithEnabledList(
        array $enabledNames,
        bool $authenticated = false
    ): McpController {
        $dao = $this->getMockBuilder(ModuleDao::class)->disableOriginalConstructor()->getMock();
        $dao->method('getEnabledModuleNameList')->willReturn($enabledNames);

        $service = $this->getMockBuilder(ModuleService::class)->getMock();
        $service->method('getModuleDao')->willReturn($dao);

        $authUser = $this->getMockBuilder(AuthUser::class)->disableOriginalConstructor()->getMock();
        $authUser->method('isAuthenticated')->willReturn($authenticated);

        $this->createKernelWithMockServices([
            Services::MODULE_SERVICE => $service,
            Services::AUTH_USER => $authUser,
        ]);

        return new McpController();
    }

    private function decode(string $body): array
    {
        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * The 401 branch is what bootstraps OAuth discovery: an MCP client reads
     * `resource_metadata` off the WWW-Authenticate header and follows it to the
     * well-known documents. If this regresses, no client can ever connect.
     */
    public function testUnauthenticatedRequestReturns401WithResourceMetadataPointer(): void
    {
        $controller = $this->buildControllerWithEnabledList(['mcp', 'admin'], false);
        $response = $controller->handle(Request::create('/api/mcp', 'POST', [], [], [], [], '{}'));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $header = $response->headers->get('WWW-Authenticate');
        $this->assertStringContainsString('Bearer realm="OrangeHRM MCP"', $header);
        $this->assertStringContainsString('resource_metadata=', $header);
        $this->assertStringContainsString('/.well-known/oauth-protected-resource', $header);

        $body = $this->decode($response->getContent());
        $this->assertSame(-32001, $body['error']['code']);
        $this->assertSame('Unauthenticated', $body['error']['message']);
        $this->assertSame('authorization', $body['error']['data']['category']);
        $this->assertArrayHasKey('correlation_id', $body['error']['data']);
    }

    /**
     * The module gate runs before the auth gate, so a disabled module answers
     * the same way whether or not a token was supplied.
     */
    public function testModuleGateRunsBeforeAuthGate(): void
    {
        $controller = $this->buildControllerWithEnabledList(['admin'], true);
        $response = $controller->handle(Request::create('/api/mcp', 'POST', [], [], [], [], '{}'));

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decode($response->getContent());
        $this->assertSame(-32000, $body['error']['code']);
        $this->assertSame('module_disabled', $body['error']['data']['reason']);
    }

    public function testReturnsModuleDisabledWhenModuleDisabled(): void
    {
        $controller = $this->buildControllerWithEnabledList(['admin', 'pim'], false);
        $response = $controller->handle(Request::create('/api/mcp', 'POST', [], [], [], [], '{}'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $body = $this->decode($response->getContent());
        $this->assertSame(-32000, $body['error']['code']);
        $this->assertSame('MCP module is disabled', $body['error']['message']);
        $this->assertSame('module_disabled', $body['error']['data']['reason']);
    }

    public function testReturnsModuleDisabledWhenEnabledListIsEmpty(): void
    {
        $controller = $this->buildControllerWithEnabledList([], false);
        $response = $controller->handle(Request::create('/api/mcp', 'POST', [], [], [], [], '{}'));

        $body = $this->decode($response->getContent());
        $this->assertSame(-32000, $body['error']['code']);
        $this->assertSame('module_disabled', $body['error']['data']['reason']);
    }
}
