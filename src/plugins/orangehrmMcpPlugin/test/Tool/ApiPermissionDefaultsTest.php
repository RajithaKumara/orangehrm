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
     * class missing from it ships no row and is therefore unreachable until
     * someone adds one -- which degrades safely, but silently. This turns it
     * into a failure.
     *
     * Every routable class must be accounted for as either shipped (ALL) or
     * deliberately not shipped (NOT_SEEDED). If this fails after adding an
     * endpoint, put its class in one of the two and add a migration row if it
     * belongs in ALL.
     */
    public function testEveryRoutableClassIsAccountedFor(): void
    {
        $live = $this->liveClasses();
        $accounted = array_merge(ApiPermissionDefaults::ALL, ApiPermissionDefaults::NOT_SEEDED);

        $this->assertSame(
            [],
            array_values(array_diff($live, $accounted)),
            'these API classes are routable but are in neither ALL nor NOT_SEEDED'
        );
        $this->assertSame(
            [],
            array_values(array_diff($accounted, $live)),
            'these listed classes no longer match any route'
        );
    }

    /**
     * The two lists are a partition, not overlapping sets. A class in both would
     * make "do we ship a row for this" unanswerable from the code.
     */
    public function testShippedAndUnshippedListsDoNotOverlap(): void
    {
        $this->assertSame(
            [],
            array_values(array_intersect(ApiPermissionDefaults::ALL, ApiPermissionDefaults::NOT_SEEDED)),
            'these classes are listed as both shipped and not shipped'
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

    /**
     * The API classes the seed file puts above the ordinary level.
     *
     * @return string[]
     */
    private function sensitiveApis(): array
    {
        $sensitive = [];
        foreach (ApiCatalogSeedTest::seed() as $apiClass => $entry) {
            if ((int) ($entry['sensitivity'] ?? 0) > 0) {
                $sensitive[] = $apiClass;
            }
        }
        return $sensitive;
    }

    /**
     * The flag is only useful if it points at the things it exists for, so a
     * refactor that renames one of these should fail here rather than silently
     * downgrade it to normal.
     */
    public function testTheCategoriesThatMatterAreFlagged(): void
    {
        $sensitive = array_flip($this->sensitiveApis());

        foreach (
            [
                'OrangeHRM\\Pim\\Api\\EmployeeSalaryComponentAPI',
                'OrangeHRM\\Pim\\Api\\EmpUsTaxExemptionAPI',
                'OrangeHRM\\Pim\\Api\\EmployeeImmigrationRecordAPI',
                'OrangeHRM\\Pim\\Api\\EmployeeAttachmentAPI',
                'OrangeHRM\\Admin\\Api\\UserAPI',
            ] as $apiClass
        ) {
            $this->assertArrayHasKey(
                $apiClass,
                $sensitive,
                sprintf('`%s` must be flagged sensitive', $apiClass)
            );
        }
    }

    /**
     * A flag on a class nobody ships is dead weight, and usually means the
     * class was renamed while the seed file was not.
     */
    public function testNoSensitiveEntryIsOrphaned(): void
    {
        $shipped = array_flip(ApiPermissionDefaults::ALL);

        foreach ($this->sensitiveApis() as $apiClass) {
            $this->assertArrayHasKey(
                $apiClass,
                $shipped,
                sprintf('`%s` is flagged sensitive but is not in the shipped catalog', $apiClass)
            );
        }
    }

    /**
     * None of the compensation, exit or evaluation classes ships switched on.
     * They are reachable -- an administrator can enable them -- but inheriting
     * them is a decision nobody made.
     */
    public function testNoSensitiveClassShipsEnabled(): void
    {
        $rows = ApiPermissionDefaults::rows();

        foreach ($this->sensitiveApis() as $apiClass) {
            $this->assertFalse(
                $rows[$apiClass]['read'] ?? false,
                sprintf('`%s` is sensitive and must not ship enabled', $apiClass)
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
        $this->assertSame(
            count(ApiPermissionDefaults::NOT_SEEDED),
            count(array_unique(ApiPermissionDefaults::NOT_SEEDED)),
            'duplicate API class in the not-shipped list'
        );
    }
}
