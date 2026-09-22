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

use OrangeHRM\Mcp\Tool\ApiSensitivity;
use OrangeHRM\Mcp\Tool\Rest\RouteMap;
use OrangeHRM\Mcp\Tool\RestrictedApis;
use OrangeHRM\Tests\Util\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * The shipped catalog is seed data in the migration folder, so the checks that
 * it is well formed belong with it rather than scattered through the tests
 * that happen to read it.
 *
 * @group Mcp
 * @group Tool
 */
class ApiCatalogSeedTest extends KernelTestCase
{
    public const SEED_FILE = __DIR__ . '/../../../../../installer/Migration/V5_10_0/api-catalog.yaml';

    /**
     * The API classes the product seeds a row for.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function seed(): array
    {
        $parsed = Yaml::parseFile(self::SEED_FILE);
        return is_array($parsed) && isset($parsed['apis']) ? (array) $parsed['apis'] : [];
    }

    /**
     * Routable classes the product deliberately ships no row for.
     *
     * @return string[]
     */
    public static function notSeeded(): array
    {
        $parsed = Yaml::parseFile(self::SEED_FILE);
        return is_array($parsed) && isset($parsed['notSeeded']) ? (array) $parsed['notSeeded'] : [];
    }

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
     * The seed is authored by hand, so it can fall behind the routes. A class
     * missing from it ships no row and is therefore unreachable until someone
     * adds one -- which degrades safely, but silently. This turns it into a
     * failure.
     *
     * Every routable class must be accounted for as either seeded or
     * deliberately not.
     */
    public function testEveryRoutableClassIsAccountedFor(): void
    {
        $live = $this->liveClasses();
        $accounted = array_merge(array_keys(self::seed()), self::notSeeded());

        $this->assertSame(
            [],
            array_values(array_diff($live, $accounted)),
            'these API classes are routable but are in neither apis nor notSeeded'
        );
        $this->assertSame(
            [],
            array_values(array_diff($accounted, $live)),
            'these listed classes no longer match any route'
        );
    }

    /**
     * The two lists are a partition, not overlapping sets. A class in both
     * makes "does this ship a row" unanswerable from the file.
     */
    public function testSeededAndNotSeededDoNotOverlap(): void
    {
        $this->assertSame(
            [],
            array_values(array_intersect(array_keys(self::seed()), self::notSeeded())),
            'these classes are listed as both seeded and not seeded'
        );
    }

    /**
     * Seeding a restricted class would quietly reverse a decision taken on
     * security grounds, and would put a row on the screen for something that
     * can never be enabled.
     */
    public function testNoSeededClassIsRestricted(): void
    {
        foreach (array_keys(self::seed()) as $apiClass) {
            $this->assertFalse(
                RestrictedApis::isForbidden($apiClass),
                sprintf('`%s` is excluded on product grounds and must not be seeded', $apiClass)
            );
        }
    }

    /**
     * No write ships enabled, ever, and the file has no way to say otherwise:
     * only `read` is honoured. This proves nobody added a create/update/delete
     * key expecting it to work.
     */
    public function testTheSeedCannotShipAWriteEnabled(): void
    {
        foreach (self::seed() as $apiClass => $entry) {
            foreach (['create', 'update', 'delete'] as $verb) {
                $this->assertArrayNotHasKey(
                    $verb,
                    $entry,
                    sprintf('`%s` carries a `%s` key, which the migration ignores', $apiClass, $verb)
                );
            }
        }
    }

    /**
     * None of the sensitive classes ships switched on. They are reachable --
     * an administrator can enable them -- but inheriting them is a decision
     * nobody made.
     */
    public function testNoSensitiveClassShipsEnabled(): void
    {
        foreach (self::seed() as $apiClass => $entry) {
            if ((int) ($entry['sensitivity'] ?? 0) <= ApiSensitivity::NORMAL) {
                continue;
            }
            $this->assertArrayNotHasKey(
                'read',
                $entry,
                sprintf('`%s` is sensitive and must not ship enabled', $apiClass)
            );
        }
    }

    public function testEveryEntryCarriesALabelAndALevel(): void
    {
        foreach (self::seed() as $apiClass => $entry) {
            $this->assertIsArray($entry, sprintf('`%s` is not a mapping', $apiClass));
            $this->assertArrayHasKey('label', $entry, sprintf('`%s` has no label', $apiClass));
            $this->assertNotSame('', trim((string) $entry['label']));
            $this->assertArrayHasKey('sensitivity', $entry, sprintf('`%s` has no sensitivity', $apiClass));
            $this->assertIsInt($entry['sensitivity'], sprintf('`%s` has a non-numeric level', $apiClass));
        }
    }

    /**
     * A level the code does not define would be stored and then rendered as
     * whatever tier sits below it -- quietly, which is the problem.
     */
    public function testEveryLevelIsOneTheCodeDefines(): void
    {
        foreach (self::seed() as $apiClass => $entry) {
            $this->assertTrue(
                ApiSensitivity::isValid((int) $entry['sensitivity']),
                sprintf('`%s` uses level %d, which ApiSensitivity does not define', $apiClass, $entry['sensitivity'])
            );
        }
    }

    /**
     * The gap between the levels is the point: a tier added later slots in
     * without renumbering rows that already exist.
     */
    public function testLevelsLeaveRoomBetweenThem(): void
    {
        $levels = ApiSensitivity::levels();
        sort($levels);

        for ($i = 1; $i < count($levels); $i++) {
            $this->assertGreaterThan(
                1,
                $levels[$i] - $levels[$i - 1],
                'levels should be spaced so another can be inserted between them'
            );
        }
    }
}
