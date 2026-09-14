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

namespace OrangeHRM\Tests\Mcp\Tool;

use OrangeHRM\Core\Authorization\Dto\ResourcePermission;
use OrangeHRM\Core\Authorization\Manager\BasicUserRoleManager;
use OrangeHRM\Core\Dao\ModuleDao;
use OrangeHRM\Core\Service\ModuleService;
use OrangeHRM\Framework\Http\Request as HttpRequest;
use OrangeHRM\Framework\Http\Response as HttpResponse;
use OrangeHRM\Framework\Services;
use OrangeHRM\Mcp\JsonRpc\EnvelopeException;
use OrangeHRM\Mcp\Tool\Rest\GenericTool;
use OrangeHRM\Mcp\Tool\Rest\RestRoute;
use OrangeHRM\Mcp\Tool\RestDispatcher;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Tool
 */
class RestDispatcherTest extends KernelTestCase
{
    private const LEAVE_TYPE_API = 'OrangeHRM\\Leave\\Api\\LeaveTypeAPI';

    /**
     * Captures what would have been dispatched instead of running it, so the
     * request the pipeline would receive is visible to assertions.
     */
    private function dispatcher(ResourcePermission $permission): RestDispatcher
    {
        $moduleDao = $this->getMockBuilder(ModuleDao::class)->disableOriginalConstructor()->getMock();
        $moduleDao->method('getEnabledModuleNameList')->willReturn(['leave', 'pim', 'admin']);
        $moduleService = $this->getMockBuilder(ModuleService::class)->getMock();
        $moduleService->method('getModuleDao')->willReturn($moduleDao);

        $roleManager = $this->getMockBuilder(BasicUserRoleManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $roleManager->method('getApiPermissions')->willReturn($permission);

        $this->createKernelWithMockServices([
            Services::MODULE_SERVICE => $moduleService,
            Services::USER_ROLE_MANAGER => $roleManager,
        ]);

        return new class () extends RestDispatcher {
            public ?HttpRequest $dispatched = null;

            protected function handleInProcess(HttpRequest $httpRequest): HttpResponse
            {
                $this->dispatched = $httpRequest;
                return new HttpResponse((string) json_encode(['data' => []]), 200);
            }
        };
    }

    private function tool(string $method, string $path = '/api/v2/leave/leave-types'): GenericTool
    {
        return new GenericTool(new RestRoute($method, $path, self::LEAVE_TYPE_API, []));
    }

    private function allow(bool $read, bool $create, bool $update, bool $delete): ResourcePermission
    {
        return new ResourcePermission($read, $create, $update, $delete);
    }

    /**
     * The bug this guards: every tool used to dispatch as GET regardless of its
     * verb, so enabling a write tool would have quietly performed a read and
     * reported success.
     */
    public function testEachToolDispatchesWithItsOwnHttpMethod(): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
            $dispatcher = $this->dispatcher($this->allow(true, true, true, true));
            $dispatcher->dispatch($this->tool($method), []);

            $this->assertSame($method, $dispatcher->dispatched->getMethod());
        }
    }

    /**
     * A GET carries its parameters in the query string; a write carries them
     * where Api\V2\Request reads body parameters from. Putting a write's
     * arguments in the query would silently drop every field.
     */
    public function testParametersGoToTheQueryForReadsAndTheBodyForWrites(): void
    {
        $read = $this->dispatcher($this->allow(true, true, true, true));
        $read->dispatch($this->tool('GET'), ['limit' => 5]);
        $this->assertSame('5', (string) $read->dispatched->query->get('limit'));
        $this->assertNull($read->dispatched->request->get('limit'));

        $write = $this->dispatcher($this->allow(true, true, true, true));
        $write->dispatch($this->tool('POST'), ['name' => 'Casual']);
        $this->assertSame('Casual', $write->dispatched->request->get('name'));
        $this->assertNull($write->dispatched->query->get('name'));
    }

    /**
     * Each verb is checked against its own grant. Checking canRead() for a
     * delete would look like enforcement while letting anyone who may read an
     * endpoint destroy through it.
     */
    public function testEachVerbIsCheckedAgainstItsOwnGrant(): void
    {
        $readOnlyUser = $this->allow(true, false, false, false);

        $allowed = $this->dispatcher($readOnlyUser);
        $allowed->dispatch($this->tool('GET'), []);
        $this->assertNotNull($allowed->dispatched, 'a read grant permits a read');

        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $refused = $this->dispatcher($readOnlyUser);
            try {
                $refused->dispatch($this->tool($method), []);
                $this->fail(sprintf('%s should have been refused for a read-only user', $method));
            } catch (EnvelopeException $e) {
                $this->assertStringContainsString('permission', $e->getMessage());
            }
            $this->assertNull($refused->dispatched, 'nothing may reach the pipeline after a refusal');
        }
    }

    public function testDeleteGrantAloneDoesNotPermitAnUpdate(): void
    {
        $dispatcher = $this->dispatcher($this->allow(true, false, false, true));

        $dispatcher->dispatch($this->tool('DELETE'), ['ids' => [1]]);
        $this->assertSame('DELETE', $dispatcher->dispatched->getMethod());

        $this->expectException(EnvelopeException::class);
        $this->dispatcher($this->allow(true, false, false, true))->dispatch($this->tool('PUT'), []);
    }

    /**
     * A restricted class is refused at dispatch as well as at the gate. This is
     * the layer that catches a curated tool pointed somewhere it should not be.
     */
    public function testRestrictedApisAreRefusedAtDispatch(): void
    {
        $dispatcher = $this->dispatcher($this->allow(true, true, true, true));
        $tool = new GenericTool(
            new RestRoute('PUT', '/api/v2/admin/users/{id}', 'OrangeHRM\\Admin\\Api\\UserAPI', [])
        );

        $this->expectException(EnvelopeException::class);
        $dispatcher->dispatch($tool, ['id' => 1]);
    }

    public function testDisabledModuleIsRefused(): void
    {
        $dispatcher = $this->dispatcher($this->allow(true, true, true, true));
        $tool = new GenericTool(
            new RestRoute('GET', '/api/v2/buzz/feed', 'OrangeHRM\\Buzz\\Api\\BuzzFeedAPI', [])
        );

        $this->expectException(EnvelopeException::class);
        $dispatcher->dispatch($tool, []);
    }

    public function testMissingPathParameterIsAClientError(): void
    {
        $dispatcher = $this->dispatcher($this->allow(true, true, true, true));
        $tool = $this->tool('GET', '/api/v2/leave/leave-types/{id}');

        $this->expectException(EnvelopeException::class);
        $dispatcher->dispatch($tool, []);
    }
}
