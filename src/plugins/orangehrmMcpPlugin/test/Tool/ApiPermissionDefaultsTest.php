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

use OrangeHRM\Mcp\Tool\ApiPermissionDefaults;
use OrangeHRM\Mcp\Tool\ApiSensitivity;
use OrangeHRM\Mcp\Tool\ApiVerb;
use OrangeHRM\Mcp\Tool\Rest\RouteMap;
use OrangeHRM\Mcp\Tool\RestrictedApis;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Tool
 */
class ApiPermissionDefaultsTest extends KernelTestCase
{
    /**
     * @return string[] API classes the live route collection exposes to MCP
     */
    private function liveClasses(): array
    {
        $classes = [];
        foreach ((new RouteMap())->all() as $route) {
            $classes[$route->getApiClass()] = true;
        }
        $classes = array_keys($classes);
        sort($classes);
        return $classes;
    }

    /**
     * The seed list is authored by hand, so it can fall behind the routes. A
     * class missing from it is simply unreachable -- absence of a row is refusal
     * -- which degrades safely but silently. This turns that into a failure.
     *
     * If this fails after adding or removing an endpoint, update ALL in
     * ApiPermissionDefaults and add a migration for the new rows.
     */
    public function testShippedListMatchesTheLiveRouteCollection(): void
    {
        $live = $this->liveClasses();
        $shipped = ApiPermissionDefaults::ALL;
        sort($shipped);

        $this->assertSame(
            [],
            array_values(array_diff($live, $shipped)),
            'these API classes are routable but have no shipped permission row'
        );
        $this->assertSame(
            [],
            array_values(array_diff($shipped, $live)),
            'these shipped permission rows no longer match any route'
        );
    }

    /**
     * Shipping a restricted class would quietly reverse a decision taken on
     * security grounds, and would put a row on the admin screen for something
     * that can never be enabled.
     */
    public function testNoShippedRowTargetsARestrictedApi(): void
    {
        foreach (ApiPermissionDefaults::ALL as $apiClass) {
            $this->assertFalse(
                RestrictedApis::isForbidden($apiClass),
                sprintf('`%s` is excluded on product grounds and must not have a row', $apiClass)
            );
        }
    }

    public function testEveryDefaultOnClassIsInTheFullList(): void
    {
        foreach (ApiPermissionDefaults::READ_ENABLED as $apiClass) {
            $this->assertContains(
                $apiClass,
                ApiPermissionDefaults::ALL,
                sprintf('`%s` ships enabled but is not in the catalog', $apiClass)
            );
        }
    }

    /**
     * No write ships enabled, ever. An administrator turns one on against a
     * screen that says what it does; nothing arrives that way.
     */
    public function testNoWriteShipsEnabled(): void
    {
        foreach (ApiPermissionDefaults::rows() as $apiClass => $row) {
            foreach ([ApiVerb::CREATE, ApiVerb::UPDATE, ApiVerb::DELETE] as $verb) {
                $this->assertFalse(
                    $row[$verb],
                    sprintf('`%s` ships with %s enabled', $apiClass, $verb)
                );
            }
        }
    }

    public function testEveryRowCarriesAValidSensitivity(): void
    {
        foreach (ApiPermissionDefaults::rows() as $apiClass => $row) {
            $this->assertTrue(
                ApiSensitivity::isValid($row['sensitivity']),
                sprintf('`%s` has an unknown sensitivity `%s`', $apiClass, $row['sensitivity'])
            );
        }
    }

    /**
     * Sensitivity is only useful if it points at the things that are actually
     * sensitive. These are the categories the classification exists for, so a
     * refactor that renames one of them should fail here rather than silently
     * downgrade it to `normal`.
     */
    public function testTheCategoriesThatMatterAreClassifiedHigh(): void
    {
        $rows = ApiPermissionDefaults::rows();

        foreach (
            [
                'OrangeHRM\\Pim\\Api\\EmployeeSalaryComponentAPI',
                'OrangeHRM\\Pim\\Api\\EmpUsTaxExemptionAPI',
                'OrangeHRM\\Pim\\Api\\EmployeeTerminationAPI',
                'OrangeHRM\\Pim\\Api\\EmployeeImmigrationRecordAPI',
                'OrangeHRM\\Pim\\Api\\EmployeeAttachmentAPI',
                'OrangeHRM\\Admin\\Api\\UserAPI',
            ] as $apiClass
        ) {
            $this->assertArrayHasKey($apiClass, $rows, sprintf('`%s` is no longer in the catalog', $apiClass));
            $this->assertSame(
                ApiSensitivity::HIGH,
                $rows[$apiClass]['sensitivity'],
                sprintf('`%s` must be classified as highly sensitive', $apiClass)
            );
        }
    }

    /**
     * None of the compensation, exit or evaluation classes ships switched on.
     * They are reachable -- an administrator can enable them -- but inheriting
     * them is a decision nobody made.
     */
    public function testNoHighlySensitiveClassShipsEnabled(): void
    {
        foreach (ApiPermissionDefaults::rows() as $apiClass => $row) {
            if ($row['sensitivity'] !== ApiSensitivity::HIGH) {
                continue;
            }
            $this->assertFalse(
                $row['read'],
                sprintf('`%s` is highly sensitive and must not ship enabled', $apiClass)
            );
        }
    }

    public function testEntriesAreUnique(): void
    {
        $this->assertSame(
            count(ApiPermissionDefaults::ALL),
            count(array_unique(ApiPermissionDefaults::ALL)),
            'duplicate API class in the shipped catalog'
        );
        $this->assertSame(
            count(ApiPermissionDefaults::READ_ENABLED),
            count(array_unique(ApiPermissionDefaults::READ_ENABLED)),
            'duplicate API class in the default-on list'
        );
    }
}
