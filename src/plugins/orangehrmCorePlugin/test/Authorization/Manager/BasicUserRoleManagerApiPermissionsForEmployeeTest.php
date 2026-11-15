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

namespace OrangeHRM\Tests\Core\Authorization\Manager;

use OrangeHRM\Admin\Service\UserService;
use OrangeHRM\Config\Config;
use OrangeHRM\Core\Authorization\Dto\ResourcePermission;
use OrangeHRM\Core\Authorization\Manager\BasicUserRoleManager;
use OrangeHRM\Core\Helper\ClassHelper;
use OrangeHRM\Entity\User;
use OrangeHRM\Framework\Services;
use OrangeHRM\Pim\Service\EmployeeService;
use OrangeHRM\Tests\Util\KernelTestCase;
use OrangeHRM\Tests\Util\TestDataService;
use OrangeHRM\Time\Service\ProjectService;

/**
 * @group Core
 * @group Manager
 */
class BasicUserRoleManagerApiPermissionsForEmployeeTest extends KernelTestCase
{
    private const SALARY_API = 'OrangeHRM\Pim\Api\EmployeeSalaryComponentAPI';
    private const WORK_EMAIL_VALIDATION_API = 'OrangeHRM\Pim\Api\ValidationEmployeeEmailAPI';

    private const ADMIN_EMP = 1;
    private const SUPERVISOR_EMP = 2;
    private const SUBORDINATE_EMP = 3;
    private const UNRELATED_EMP = 4;

    protected function setUp(): void
    {
        $fixture = Config::get(Config::PLUGINS_DIR)
            . '/orangehrmCorePlugin/test/fixtures/BasicUserRoleManagerApiPermissionsForEmployee.yml';
        TestDataService::populate($fixture);
        $this->createKernelWithMockServices([
            Services::CLASS_HELPER => new ClassHelper(),
            Services::EMPLOYEE_SERVICE => new EmployeeService(),
            Services::USER_SERVICE => new UserService(),
            Services::PROJECT_SERVICE => new ProjectService(),
        ]);
    }

    private function getManagerForUser(int $userId): BasicUserRoleManager
    {
        $manager = new BasicUserRoleManager();
        $manager->setUser($this->getEntityManager()->getRepository(User::class)->find($userId));
        return $manager;
    }

    public function testSupervisorSelfGrantDoesNotApplyToSubordinate(): void
    {
        $manager = $this->getManagerForUser(2);
        $this->assertEquals(
            new ResourcePermission(false, false, false, false),
            $manager->getApiPermissionsForEmployee(self::SALARY_API, self::SUBORDINATE_EMP)
        );
        $this->assertEquals(
            new ResourcePermission(false, false, false, false),
            $manager->getApiPermissionsForEmployee(self::SALARY_API, self::UNRELATED_EMP)
        );
    }

    public function testSupervisorSelfGrantAppliesToOwnRecord(): void
    {
        $manager = $this->getManagerForUser(2);
        $this->assertEquals(
            new ResourcePermission(true, false, false, false),
            $manager->getApiPermissionsForEmployee(self::SALARY_API, self::SUPERVISOR_EMP)
        );
    }

    public function testEssSelfGrant(): void
    {
        $manager = $this->getManagerForUser(3);
        $this->assertEquals(
            new ResourcePermission(true, false, false, false),
            $manager->getApiPermissionsForEmployee(self::SALARY_API, self::SUBORDINATE_EMP)
        );
        $this->assertEquals(
            new ResourcePermission(false, false, false, false),
            $manager->getApiPermissionsForEmployee(self::SALARY_API, self::SUPERVISOR_EMP)
        );
    }

    public function testAdminNonSelfGrantAppliesToEveryRecordIncludingOwn(): void
    {
        $manager = $this->getManagerForUser(1);
        foreach ([self::ADMIN_EMP, self::SUPERVISOR_EMP, self::SUBORDINATE_EMP, self::UNRELATED_EMP] as $empNumber) {
            $this->assertEquals(
                new ResourcePermission(true, true, true, true),
                $manager->getApiPermissionsForEmployee(self::SALARY_API, $empNumber),
                "empNumber $empNumber"
            );
        }
    }

    public function testUserWithoutEmployeeNeverMatchesSelf(): void
    {
        $manager = $this->getManagerForUser(4);
        $this->assertEquals(
            new ResourcePermission(true, true, true, true),
            $manager->getApiPermissionsForEmployee(self::SALARY_API, self::ADMIN_EMP)
        );
        $this->assertEquals(
            new ResourcePermission(false, false, false, false),
            $manager->getApiPermissionsForEmployee(self::WORK_EMAIL_VALIDATION_API, self::ADMIN_EMP)
        );
        $this->assertEquals(
            new ResourcePermission(false, false, false, false),
            $manager->getApiPermissionsForEmployee(self::WORK_EMAIL_VALIDATION_API, null)
        );
    }

    public function testNullEmployeeOnlyCountsNonSelfGrants(): void
    {
        $this->assertEquals(
            new ResourcePermission(false, false, false, false),
            $this->getManagerForUser(2)->getApiPermissionsForEmployee(self::SALARY_API, null)
        );
        $this->assertEquals(
            new ResourcePermission(true, true, true, true),
            $this->getManagerForUser(1)->getApiPermissionsForEmployee(self::SALARY_API, null)
        );
    }

    public function testGatePermissionsAreUnchanged(): void
    {
        // The coarse gate (getApiPermissions) still merges self and non-self grants
        $this->assertEquals(
            new ResourcePermission(true, false, false, false),
            $this->getManagerForUser(2)->getApiPermissions(self::SALARY_API)
        );
        $this->assertEquals(
            new ResourcePermission(true, false, false, false),
            $this->getManagerForUser(2)->getApiPermissions(self::WORK_EMAIL_VALIDATION_API)
        );
        $this->assertEquals(
            new ResourcePermission(true, false, false, false),
            $this->getManagerForUser(1)->getApiPermissions(self::WORK_EMAIL_VALIDATION_API)
        );
    }
}
