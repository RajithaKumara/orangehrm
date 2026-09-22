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

use OrangeHRM\Mcp\Tool\ApiCatalog;
use OrangeHRM\Mcp\Tool\ApiSensitivity;
use OrangeHRM\Mcp\Tool\ApiPermissionDefaults;
use OrangeHRM\Mcp\Tool\ApiVerb;
use OrangeHRM\Tests\Util\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @group Mcp
 * @group Tool
 */
class ApiCatalogTest extends KernelTestCase
{
    /**
     * The shipped catalog, as the permission service would return it.
     *
     * @return array<string,array<string,mixed>>
     */
    private function shippedMap(): array
    {
        $map = [];
        foreach (ApiPermissionDefaults::rows() as $apiClass => $row) {
            $map[$apiClass] = [
                ApiVerb::READ => $row['read'],
                ApiVerb::CREATE => $row['create'],
                ApiVerb::UPDATE => $row['update'],
                ApiVerb::DELETE => $row['delete'],
                'sensitivity' => $row['sensitivity'],
            ];
        }
        return $map;
    }

    public function testEveryShippedClassLandsInExactlyOneModuleGroup(): void
    {
        $catalog = new ApiCatalog();
        $groups = $catalog->getGroupedByModule($this->shippedMap());

        $seen = [];
        foreach ($groups as $group) {
            $this->assertNotSame('', $group['label']);
            $this->assertNotEmpty($group['apis'], sprintf('module `%s` has no rows', $group['module']));
            foreach ($group['apis'] as $api) {
                $this->assertArrayNotHasKey(
                    $api['apiName'],
                    $seen,
                    sprintf('`%s` appears in two module groups', $api['apiName'])
                );
                $seen[$api['apiName']] = true;
            }
        }

        $this->assertCount(count(ApiPermissionDefaults::ALL), $seen);
    }

    /**
     * A label stored on the row wins over the derived one, and clearing it
     * falls back. Without a test the fallback hides a broken read: the screen
     * would show sensible names either way.
     */
    public function testRowLabelOverridesTheDerivedName(): void
    {
        $catalog = new ApiCatalog();
        $apiClass = 'OrangeHRM\\Admin\\Api\\LocationAPI';

        $renamed = $catalog->getGroupedByModule([
            $apiClass => ['label' => 'Office', ApiVerb::READ => true, 'sensitivity' => ApiSensitivity::NORMAL],
        ]);
        $this->assertSame('Office', $renamed[0]['apis'][0]['label']);

        $cleared = $catalog->getGroupedByModule([
            $apiClass => ['label' => null, ApiVerb::READ => true, 'sensitivity' => ApiSensitivity::NORMAL],
        ]);
        $this->assertSame('Location', $cleared[0]['apis'][0]['label']);
    }

    /**
     * The table is the catalog. A routable class with no row is not a row the
     * administrator can see, and a row for a class this build no longer serves
     * is not one either.
     */
    public function testOnlyClassesWithARowAreShown(): void
    {
        $catalog = new ApiCatalog();
        $apiClass = 'OrangeHRM\\Admin\\Api\\LocationAPI';

        $this->assertSame([], $catalog->getGroupedByModule([]), 'no rows means nothing to show');

        $groups = $catalog->getGroupedByModule([
            $apiClass => [ApiVerb::READ => true, 'sensitivity' => ApiSensitivity::NORMAL],
            'OrangeHRM\\Not\\A\\RealAPI' => [ApiVerb::READ => true, 'sensitivity' => ApiSensitivity::NORMAL],
        ]);

        $shown = [];
        foreach ($groups as $group) {
            foreach ($group['apis'] as $api) {
                $shown[] = $api['apiName'];
            }
        }
        $this->assertSame([$apiClass], $shown, 'a row without a route has nothing to enable');
    }

    /**
     * A row offers only the verbs its API class actually exposes. Rendering a
     * checkbox that can never turn anything on would be noise in a list of 190.
     */
    public function testRowsOnlyOfferVerbsThatExist(): void
    {
        foreach ((new ApiCatalog())->getGroupedByModule($this->shippedMap()) as $group) {
            foreach ($group['apis'] as $api) {
                $this->assertNotEmpty($api['verbs'], sprintf('`%s` offers no verbs', $api['apiName']));
                foreach ($api['verbs'] as $verb) {
                    $this->assertContains($verb['verb'], ApiVerb::all());
                    $this->assertGreaterThan(0, $verb['tools']);
                    if (ApiVerb::isWrite($verb['verb'])) {
                        $this->assertFalse($verb['enabled'], 'no write ships enabled');
                    }
                }
            }
        }
    }

    public function testFlagsAndSensitivityComeFromThePermissionMap(): void
    {
        $catalog = new ApiCatalog();
        $apiClass = 'OrangeHRM\\Admin\\Api\\LocationAPI';

        $groups = $catalog->getGroupedByModule([
            $apiClass => [
                ApiVerb::READ => true,
                ApiVerb::CREATE => false,
                ApiVerb::UPDATE => false,
                ApiVerb::DELETE => true,
                'sensitivity' => ApiSensitivity::SENSITIVE,
            ],
        ]);

        $row = null;
        foreach ($groups as $group) {
            foreach ($group['apis'] as $api) {
                if ($api['apiName'] === $apiClass) {
                    $row = $api;
                }
            }
        }

        $this->assertNotNull($row, 'LocationAPI should be in the catalog');
        $this->assertSame(ApiSensitivity::SENSITIVE, $row['sensitivity']);

        $enabled = [];
        foreach ($row['verbs'] as $verb) {
            $enabled[$verb['verb']] = $verb['enabled'];
        }
        $this->assertTrue($enabled[ApiVerb::READ]);
        $this->assertFalse($enabled[ApiVerb::UPDATE]);
        $this->assertTrue($enabled[ApiVerb::DELETE]);
    }

    /**
     * The counts drive the "68 of 490 tools" line and the too-many-tools
     * warning, so they must count tools rather than API classes.
     */
    public function testCountsAreToolCountsNotClassCounts(): void
    {
        $catalog = new ApiCatalog();
        $counts = $catalog->getCounts($this->shippedMap());

        $available = 0;
        foreach ($counts['tools'] as $pair) {
            $available += $pair['available'];
        }

        $this->assertSame(count(ApiPermissionDefaults::ALL), $counts['classes']);
        $this->assertGreaterThan(
            $counts['classes'],
            $available,
            'there are more operations than API classes; a class exposes several'
        );
        $this->assertSame(
            0,
            $counts['tools'][ApiVerb::DELETE]['enabled'],
            'no delete ships enabled'
        );
    }

    /**
     * Counts describe the catalog the administrator is looking at, so a class
     * with no row contributes to neither side of "66 of 463".
     */
    public function testCountsIgnoreClassesWithNoRow(): void
    {
        $counts = (new ApiCatalog())->getCounts([]);

        $this->assertSame(0, $counts['classes']);
        foreach ($counts['tools'] as $pair) {
            $this->assertSame(0, $pair['available']);
            $this->assertSame(0, $pair['enabled']);
        }
    }

    public function testRiskIsCarriedByTheVerb(): void
    {
        $this->assertSame('none', ApiVerb::risk(ApiVerb::READ));
        $this->assertSame('warn', ApiVerb::risk(ApiVerb::CREATE));
        $this->assertSame('warn', ApiVerb::risk(ApiVerb::UPDATE));
        $this->assertSame('danger', ApiVerb::risk(ApiVerb::DELETE));
    }

    /**
     * An unknown method must not be classified as a read: reads are the one
     * thing that ships switched on.
     */
    public function testUnknownMethodsAreTreatedAsWrites(): void
    {
        $this->assertSame(ApiVerb::READ, ApiVerb::fromMethod('get'));
        $this->assertSame(ApiVerb::CREATE, ApiVerb::fromMethod('POST'));
        $this->assertSame(ApiVerb::UPDATE, ApiVerb::fromMethod('PATCH'));
        $this->assertSame(ApiVerb::DELETE, ApiVerb::fromMethod('DELETE'));
        $this->assertSame(ApiVerb::UPDATE, ApiVerb::fromMethod('PURGE'));
        $this->assertTrue(ApiVerb::isWrite(ApiVerb::fromMethod('PURGE')));
    }

    public function testLabelsAreReadable(): void
    {
        $this->assertSame(
            'Employee Salary Component',
            ApiCatalog::toLabel('OrangeHRM\\Pim\\Api\\EmployeeSalaryComponentAPI')
        );
        // Acronyms keep their casing rather than being split letter by letter.
        $this->assertSame('I18N Group', ApiCatalog::toLabel('OrangeHRM\\Admin\\Api\\I18NGroupAPI'));
        $this->assertSame('Employee CSV Import', ApiCatalog::toLabel('OrangeHRM\\Pim\\Api\\EmployeeCSVImportAPI'));
    }

    /**
     * The shipped wording, as the migration seeds it.
     *
     * @return array<string,string>
     */
    private function seededLabels(): array
    {
        $parsed = Yaml::parseFile(
            __DIR__ . '/../../../../../installer/Migration/V5_10_0/api-label.yaml'
        );
        return is_array($parsed) ? $parsed : [];
    }

    /**
     * Every class the migration inserts a row for needs a name to put in it.
     */
    public function testEveryShippedClassHasASeededLabel(): void
    {
        $labels = $this->seededLabels();

        foreach (ApiPermissionDefaults::ALL as $apiClass) {
            $this->assertArrayHasKey($apiClass, $labels, sprintf('`%s` has no seeded label', $apiClass));
            $this->assertNotSame('', trim($labels[$apiClass]));
        }
    }

    /**
     * A label for a class nobody ships is dead weight, and usually means a
     * class was renamed while the seed file was not.
     */
    public function testNoSeededLabelIsOrphaned(): void
    {
        $shipped = array_flip(ApiPermissionDefaults::ALL);

        foreach (array_keys($this->seededLabels()) as $apiClass) {
            $this->assertArrayHasKey(
                $apiClass,
                $shipped,
                sprintf('`%s` has a label but is not in the shipped catalog', $apiClass)
            );
        }
    }

    /**
     * The abbreviations a class name carries and an administrator does not know.
     */
    public function testSeededLabelsExpandAbbreviations(): void
    {
        $labels = $this->seededLabels();

        $this->assertSame(
            'Employee Emergency Contact',
            $labels['OrangeHRM\\Pim\\Api\\EmpEmergencyContactAPI']
        );
        $this->assertSame(
            'Employee US Tax Exemption',
            $labels['OrangeHRM\\Pim\\Api\\EmpUsTaxExemptionAPI']
        );
        $this->assertSame('PIM Report', $labels['OrangeHRM\\Pim\\Api\\PimReportAPI']);
        $this->assertSame('KPI', $labels['OrangeHRM\\Performance\\Api\\KpiAPI']);
        // Named after the route it serves rather than the class it lives in.
        $this->assertSame('Interviewer', $labels['OrangeHRM\\Recruitment\\Api\\EmployeeListAPI']);
    }

    /**
     * Whatever the source, a label has to be something a person can read.
     */
    public function testNoSeededLabelKeepsADeveloperAbbreviation(): void
    {
        foreach ($this->seededLabels() as $apiClass => $label) {
            $this->assertDoesNotMatchRegularExpression(
                '/\\b(Emp|Pim|Kpi|Us|Csv|Api|I18N)\\b/',
                $label,
                sprintf('`%s` still reads as `%s`', $apiClass, $label)
            );
        }
    }
}
