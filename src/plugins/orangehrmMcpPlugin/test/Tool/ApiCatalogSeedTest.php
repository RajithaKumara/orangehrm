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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * The shipped catalog is seed data in the migration folder, so the checks that
 * it is well formed belong with it rather than scattered through the tests
 * that happen to read it.
 *
 * @group Mcp
 * @group Tool
 */
class ApiCatalogSeedTest extends TestCase
{
    public const SEED_FILE = __DIR__ . '/../../../../../installer/Migration/V5_10_0/api-catalog.yaml';

    /**
     * @return array<string,array<string,mixed>>
     */
    public static function seed(): array
    {
        $parsed = Yaml::parseFile(self::SEED_FILE);
        return is_array($parsed) ? $parsed : [];
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
