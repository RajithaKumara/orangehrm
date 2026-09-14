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
use OrangeHRM\Mcp\Tool\ApiVerb;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Tool
 */
class ApiCatalogTest extends KernelTestCase
{
    public function testEveryRouteLandsInExactlyOneModuleGroup(): void
    {
        $catalog = new ApiCatalog();
        $groups = $catalog->getGroupedByModule([]);

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

        $this->assertCount(count($catalog->getClasses()), $seen);
    }

    /**
     * A row offers only the verbs its API class actually exposes. Rendering a
     * checkbox that can never turn anything on would be noise in a list of 190.
     */
    public function testRowsOnlyOfferVerbsThatExist(): void
    {
        foreach ((new ApiCatalog())->getGroupedByModule([]) as $group) {
            foreach ($group['apis'] as $api) {
                $this->assertNotEmpty($api['verbs'], sprintf('`%s` offers no verbs', $api['apiName']));
                foreach ($api['verbs'] as $verb) {
                    $this->assertContains($verb['verb'], ApiVerb::all());
                    $this->assertGreaterThan(0, $verb['tools']);
                    $this->assertFalse($verb['enabled'], 'an empty permission map enables nothing');
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
                'sensitivity' => ApiSensitivity::HIGH,
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
        $this->assertSame(ApiSensitivity::HIGH, $row['sensitivity']);

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
        $counts = $catalog->getCounts([]);

        $available = 0;
        foreach ($counts['tools'] as $verb => $pair) {
            $this->assertSame(0, $pair['enabled'], 'nothing is enabled by an empty map');
            $available += $pair['available'];
        }

        $this->assertGreaterThan(
            $counts['classes'],
            $available,
            'there are more operations than API classes; a class exposes several'
        );
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
}
