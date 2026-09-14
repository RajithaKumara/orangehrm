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

use OrangeHRM\Mcp\Tool\Curated\CurrentEmployeeTool;
use OrangeHRM\Mcp\Tool\Curated\ListEmployeesTool;
use OrangeHRM\Mcp\Tool\Curated\ListJobTitlesTool;
use OrangeHRM\Mcp\Tool\Curated\ListLeaveTypesTool;
use OrangeHRM\Mcp\Tool\DefaultTools;
use OrangeHRM\Mcp\Tool\Rest\RouteMap;
use OrangeHRM\Mcp\Tool\RestrictedApis;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Tool
 */
class DefaultToolsTest extends KernelTestCase
{
    /**
     * A generic tool's name encodes its method and path, so renaming a route
     * silently orphans the entry here: the tool simply stops being offered, with
     * no error anywhere. This turns that into a failing test.
     *
     * If this fails after a route change, fix the name in DefaultTools rather
     * than deleting the assertion.
     */
    public function testEveryDefaultGenericToolResolvesToALiveRoute(): void
    {
        $live = (new RouteMap())->all();

        $orphaned = array_values(array_filter(
            DefaultTools::GENERIC,
            static function (string $name) use ($live): bool {
                return !array_key_exists($name, $live);
            }
        ));

        $this->assertSame(
            [],
            $orphaned,
            'These default tools no longer match a route -- they would silently stop being offered'
        );
    }

    /**
     * Curated names are not derived from routes, so they need their own check
     * against the classes that actually exist.
     */
    public function testEveryDefaultCuratedToolExists(): void
    {
        $existing = [];
        foreach (
            [
                new CurrentEmployeeTool(),
                new ListEmployeesTool(),
                new ListJobTitlesTool(),
                new ListLeaveTypesTool(),
            ] as $tool
        ) {
            $existing[] = $tool->getName();
        }

        foreach (DefaultTools::CURATED as $name) {
            $this->assertContains($name, $existing, sprintf('Default curated tool `%s` does not exist', $name));
        }
    }

    /**
     * Defaults must never contradict the restriction list. If an area is
     * excluded on security grounds, shipping it switched on would quietly
     * reverse that decision.
     */
    public function testNoDefaultTargetsARestrictedApi(): void
    {
        $live = (new RouteMap())->all();

        foreach (DefaultTools::GENERIC as $name) {
            if (!isset($live[$name])) {
                continue;
            }
            $route = $live[$name];
            $this->assertTrue(
                RestrictedApis::isAllowed($route->getApiClass(), $route->getMethod()),
                sprintf('Default tool `%s` targets a restricted API class', $name)
            );
        }
    }

    public function testDefaultsAreReadsOnly(): void
    {
        $live = (new RouteMap())->all();

        foreach (DefaultTools::GENERIC as $name) {
            if (!isset($live[$name])) {
                continue;
            }
            $this->assertFalse(
                $live[$name]->isWrite(),
                sprintf('Default tool `%s` is a write; writes are enabled deliberately or not at all', $name)
            );
        }
    }

    /**
     * The whole point of a default set is that it is small. If it drifts toward
     * the full catalog, model tool-selection accuracy degrades and nobody
     * notices until answers get worse.
     */
    public function testDefaultSetStaysSmall(): void
    {
        $this->assertLessThan(
            80,
            DefaultTools::count(),
            'The default set has grown large enough to hurt tool selection -- prune it or justify the increase'
        );
    }

    public function testNamesAreUniqueAndNonEmpty(): void
    {
        $all = DefaultTools::all();

        $this->assertSame(array_values(array_unique($all)), $all, 'duplicate default tool names');
        foreach ($all as $name) {
            $this->assertNotSame('', trim($name));
        }
    }
}
