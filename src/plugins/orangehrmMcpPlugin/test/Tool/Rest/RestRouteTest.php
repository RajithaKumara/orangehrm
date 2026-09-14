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

namespace OrangeHRM\Tests\Mcp\Tool\Rest;

use OrangeHRM\Mcp\Tool\Rest\RestRoute;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group Tool
 */
class RestRouteTest extends TestCase
{
    private function route(string $method, string $path, array $defaults = []): RestRoute
    {
        return new RestRoute($method, $path, 'OrangeHRM\\Some\\Api', $defaults + ['_api' => 'OrangeHRM\\Some\\Api']);
    }

    /**
     * Tool names are a public contract: a model's saved prompt may reference
     * them, so the rule must be pure and stable across builds.
     *
     * @dataProvider namingProvider
     */
    public function testToolNamingIsDeterministic(string $method, string $path, string $expected): void
    {
        $this->assertSame($expected, $this->route($method, $path)->getToolName());
    }

    public function namingProvider(): array
    {
        return [
            'simple collection' => ['GET', '/api/v2/admin/job-titles', 'rest_get_admin_job_titles'],
            'path parameter' => [
                'GET',
                '/api/v2/pim/employees/{empNumber}',
                'rest_get_pim_employees_by_empNumber',
            ],
            'several parameters' => [
                'GET',
                '/api/v2/performance/trackers/{trackerId}/logs/{id}',
                'rest_get_performance_trackers_by_trackerId_logs_by_id',
            ],
            'kebab becomes snake' => [
                'GET',
                '/api/v2/leave/leave-entitlements',
                'rest_get_leave_leave_entitlements',
            ],
            'verb is part of the name' => ['DELETE', '/api/v2/admin/job-titles', 'rest_delete_admin_job_titles'],
        ];
    }

    public function testMethodDrivesWriteAndDestructiveFlags(): void
    {
        $this->assertFalse($this->route('GET', '/api/v2/admin/x')->isWrite());
        $this->assertTrue($this->route('POST', '/api/v2/admin/x')->isWrite());
        $this->assertTrue($this->route('PUT', '/api/v2/admin/x')->isWrite());

        $this->assertFalse($this->route('PUT', '/api/v2/admin/x')->isDestructive());
        $this->assertTrue($this->route('DELETE', '/api/v2/admin/x')->isDestructive());
    }

    public function testModuleComesFromTheFirstPathSegment(): void
    {
        $this->assertSame('admin', $this->route('GET', '/api/v2/admin/job-titles')->getModule());
        $this->assertSame('leave', $this->route('GET', '/api/v2/leave/leave-types')->getModule());
        $this->assertSame('pim', $this->route('GET', '/api/v2/pim/employees/{empNumber}')->getModule());
    }

    public function testPathParametersAreExtracted(): void
    {
        $this->assertSame([], $this->route('GET', '/api/v2/admin/job-titles')->getPathParams());
        $this->assertSame(
            ['trackerId', 'id'],
            $this->route('GET', '/api/v2/performance/trackers/{trackerId}/logs/{id}')->getPathParams()
        );
    }

    /**
     * The framework's own keys must not be replayed as request attributes, but
     * everything else must -- `id: 0` on a route is what makes
     * GenericRestController call getOne() rather than getAll().
     */
    public function testRouteAttributesDropFrameworkKeysAndKeepTheRest(): void
    {
        $attributes = $this->route('GET', '/api/v2/pim/myself', [
            '_api' => 'OrangeHRM\\Pim\\Api\\MyInfoAPI',
            '_controller' => 'Some\\Controller',
            'id' => 0,
            '_key' => 'empNumber',
        ])->getRouteAttributes();

        $this->assertArrayNotHasKey('_api', $attributes);
        $this->assertArrayNotHasKey('_controller', $attributes);
        $this->assertSame(0, $attributes['id']);
        $this->assertSame('empNumber', $attributes['_key'], '_key drives getOne vs getAll and must survive');
    }
}
