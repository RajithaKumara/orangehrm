<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

namespace OrangeHRM\Tests\Leave\Service;

use Exception;
use InvalidArgumentException;
use OrangeHRM\Config\Config;
use OrangeHRM\Leave\Dao\LeavePeriodDao;
use OrangeHRM\Leave\Service\LeavePeriodService;
use OrangeHRM\Tests\Util\TestCase;
use Throwable;
use TypeError;

/**
 * @group Leave
 * @group Service
 */
class LeavePeriodServiceTest extends TestCase
{
    private LeavePeriodService $leavePeriodService;
    private string $fixture;

    protected function setUp(): void
    {
        $this->fixture = Config::get(Config::PLUGINS_DIR) .
            '/orangehrmLeavePlugin/test/fixtures/LeavePeriodService.yml';
        $this->leavePeriodService = new LeavePeriodService();
    }

    public function testGetListOfMonths(): void
    {
        $expected = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        );

        $result = $this->leavePeriodService->getListOfMonths();
        $this->assertEquals($expected, $result);
    }

    public function testGetListOfDates(): void
    {
        /* Checking for days with 31 days */
        $expected = range(1, 31);
        $result = $this->leavePeriodService->getListOfDates(1); // January
        $this->assertEquals($expected, $result, 'Wrong date range fetched for January');
        $result = $this->leavePeriodService->getListOfDates(3); // March
        $this->assertEquals($expected, $result, 'Wrong date range fetched for March');
        $result = $this->leavePeriodService->getListOfDates(5); // May
        $this->assertEquals($expected, $result, 'Wrong date range fetched for May');
        $result = $this->leavePeriodService->getListOfDates(7); // July
        $this->assertEquals($expected, $result, 'Wrong date range fetched for July');
        $result = $this->leavePeriodService->getListOfDates(8); // August
        $this->assertEquals($expected, $result, 'Wrong date range fetched for August');
        $result = $this->leavePeriodService->getListOfDates(10); // October
        $this->assertEquals($expected, $result, 'Wrong date range fetched for October');
        $result = $this->leavePeriodService->getListOfDates(12); // December
        $this->assertEquals($expected, $result, 'Wrong date range fetched for December');

        /* Checking for days with 30 days */
        $expected = range(1, 30);
        $result = $this->leavePeriodService->getListOfDates(4); // April
        $this->assertEquals($expected, $result, 'Wrong date range fetched for April');
        $result = $this->leavePeriodService->getListOfDates(6); // June
        $this->assertEquals($expected, $result, 'Wrong date range fetched for June');
        $result = $this->leavePeriodService->getListOfDates(9); // September
        $this->assertEquals($expected, $result, 'Wrong date range fetched for September');
        $result = $this->leavePeriodService->getListOfDates(11); // November
        $this->assertEquals($expected, $result, 'Wrong date range fetched for November');

        /* Checking for February; Should return maximum 29 days */
        $expected = range(1, 29);
        $result = $this->leavePeriodService->getListOfDates(2);
        $this->assertEquals($expected, $result, 'Wrong date range fetched for February');

        /* Checking for February; Should return maximum 28 days if $isLeapYear parameter is false */
        $expected = range(1, 29);
        $result = $this->leavePeriodService->getListOfDates(2, true);
        $this->assertEquals($expected, $result, 'Wrong date range fetched for February for leap years');

        $expected = range(1, 28);
        $result = $this->leavePeriodService->getListOfDates(2, false);
        $this->assertEquals($expected, $result, 'Wrong date range fetched for February non leap years');


        /* Checking for invalid month values */
        try {
            $this->leavePeriodService->getListOfDates(-1);
            $this->fail('getListOfDates() should not accept invalid month values');
        } catch (Exception $e) {
            $this->assertTrue($e instanceof InvalidArgumentException);
            $this->assertEquals(
                'Invalid value passed for month in ' . LeavePeriodService::class . '::getListOfDates',
                $e->getMessage()
            );
        }

        try {
            $this->leavePeriodService->getListOfDates(13);
            $this->fail('getListOfDates() should not accept invalid month values');
        } catch (Exception $e) {
            $this->assertTrue($e instanceof InvalidArgumentException);
            $this->assertEquals(
                'Invalid value passed for month in ' . LeavePeriodService::class . '::getListOfDates',
                $e->getMessage()
            );
        }
        /* Checking for non numeric values */
        try {
            $this->leavePeriodService->getListOfDates('abcd asdf');
            $this->fail('getListOfDates() should not accept non-numeric values');
        } catch (Throwable $e) {
            $this->assertTrue($e instanceof TypeError);
        }
    }

    public function testCalculateEndDate(): void
    {
        $result = $this->leavePeriodService->calculateEndDate(1, 1, null, 'F d');
        $this->assertEquals('December 31', $result);

        $result = $this->leavePeriodService->calculateEndDate(1, 1, 1999, 'F d');
        $this->assertEquals('December 31', $result);

        /* Test for leap years */
        $result = $this->leavePeriodService->calculateEndDate(1, 1, 2004, 'F d');
        $this->assertEquals('December 31', $result);

        $result = $this->leavePeriodService->calculateEndDate(2, 28, 2004, 'F d');
        $this->assertEquals('February 27', $result);

        $result = $this->leavePeriodService->calculateEndDate(2, 29, 2004, 'F d');
        $this->assertEquals('February 28', $result);

        /* Test for format */
        $result = $this->leavePeriodService->calculateEndDate(1, 1, 1999, 'F d');
        $this->assertEquals('December 31', $result);

        $result = $this->leavePeriodService->calculateEndDate(1, 1, 1999, 'Y-m-d');
        $this->assertEquals('1999-12-31', $result);

        $result = $this->leavePeriodService->calculateEndDate(1, 1, 1999, 'm/d/Y');
        $this->assertEquals('12/31/1999', $result);

        $result = $this->leavePeriodService->calculateEndDate(1, 1, 1999, 'd.m.Y');
        $this->assertEquals('31.12.1999', $result);

        /* Test for days other than Ja1 1st
         * (End date should always in next year) */

        $currentYear = date('Y');
        $nextYear = date('Y') + 1;

        if (strtotime("$currentYear-12-22") > strtotime(date('Y-m-d'))) {
            $nextYear = $currentYear;
        }

        $result = $this->leavePeriodService->calculateEndDate(12, 22);
        $this->assertEquals("$nextYear-12-21", $result);

        $result = $this->leavePeriodService->calculateEndDate(12, 22, $currentYear);
        $this->assertEquals("{$nextYear}-12-21", $result);

        /* Test for Ja1 1st
         * (End date should be same year Dec 31) */

        $currentYear = (int)date('Y');

        $result = $this->leavePeriodService->calculateEndDate(01, 01);
        $this->assertEquals("$currentYear-12-31", $result);

        $result = $this->leavePeriodService->calculateEndDate(01, 01, $currentYear);
        $this->assertEquals("$currentYear-12-31", $result);
    }

    public function testCalculateStartDate(): void
    {
        $currentYear = (int)date('Y'); // TODO: Remove this dependancy on getting the system date by using a mock

        $result = $this->leavePeriodService->calculateStartDate(1, 1);
        $this->assertEquals("{$currentYear}-01-01", $result);

        $result = $this->leavePeriodService->calculateStartDate(8, 1, 2006);
        $this->assertEquals("2006-08-01", $result);

        $currentYear = date('Y');

        if (strtotime("$currentYear-05-12") > strtotime(date('Y-m-d'))) {
            $currentYear = $currentYear - 1;
        }

        $result = $this->leavePeriodService->calculateStartDate(05, 12);
        $exprected = $currentYear . "-05-12";
        $this->assertEquals($exprected, $result);

        $result = $this->leavePeriodService->calculateStartDate(05, 12, $currentYear);
        $exprected = $currentYear . "-05-12";
        $this->assertEquals($exprected, $result);
    }

    public function testGetLeavePeriodDao(): void
    {
        $leavePeriodDao = $this->leavePeriodService->getLeavePeriodDao();
        $this->assertTrue($leavePeriodDao instanceof LeavePeriodDao);
    }
}
