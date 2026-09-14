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
    private function buildControllerWithEnabledList(array $enabledNames): McpController
    {
        $dao = $this->getMockBuilder(ModuleDao::class)->disableOriginalConstructor()->getMock();
        $dao->method('getEnabledModuleNameList')->willReturn($enabledNames);

        $service = $this->getMockBuilder(ModuleService::class)->getMock();
        $service->method('getModuleDao')->willReturn($dao);

        $this->createKernelWithMockServices([Services::MODULE_SERVICE => $service]);

        return new McpController();
    }

    private function decode(string $body): array
    {
        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    public function testReturnsMethodNotFoundWhenModuleEnabled(): void
    {
        $controller = $this->buildControllerWithEnabledList(['mcp', 'admin']);
        $response = $controller->handle(Request::create('/api/mcp', 'POST', [], [], [], [], '{}'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $body = $this->decode($response->getContent());
        $this->assertSame(-32601, $body['error']['code']);
        $this->assertSame('Method not found', $body['error']['message']);
    }

    public function testReturnsModuleDisabledWhenModuleDisabled(): void
    {
        $controller = $this->buildControllerWithEnabledList(['admin', 'pim']);
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
        $controller = $this->buildControllerWithEnabledList([]);
        $response = $controller->handle(Request::create('/api/mcp', 'POST', [], [], [], [], '{}'));

        $body = $this->decode($response->getContent());
        $this->assertSame(-32000, $body['error']['code']);
        $this->assertSame('module_disabled', $body['error']['data']['reason']);
    }
}
