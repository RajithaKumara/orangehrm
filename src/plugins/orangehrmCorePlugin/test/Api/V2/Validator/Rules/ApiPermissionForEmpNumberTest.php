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

namespace OrangeHRM\Tests\Core\Api\V2\Validator\Rules;

use InvalidArgumentException;
use OrangeHRM\Core\Api\V2\Exception\ForbiddenException;
use OrangeHRM\Core\Api\V2\Validator\Rules\ApiPermissionForEmpNumber;
use OrangeHRM\Core\Authorization\Dto\ResourcePermission;
use OrangeHRM\Core\Authorization\Manager\BasicUserRoleManager;
use OrangeHRM\Framework\Services;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Core
 * @group Api
 * @group Validator
 */
class ApiPermissionForEmpNumberTest extends KernelTestCase
{
    private const API = 'OrangeHRM\Pim\Api\EmployeeSalaryComponentAPI';

    private function mockPermission(ResourcePermission $permission, ?int $expectedEmpNumber = null): void
    {
        $userRoleManager = $this->getMockBuilder(BasicUserRoleManager::class)
            ->onlyMethods(['getApiPermissionsForEmployee'])
            ->disableOriginalConstructor()
            ->getMock();
        $mocker = is_null($expectedEmpNumber)
            ? $userRoleManager->expects($this->never())
            : $userRoleManager->expects($this->once());
        $mocker->method('getApiPermissionsForEmployee')
            ->with(self::API, $expectedEmpNumber)
            ->willReturn($permission);
        $this->createKernelWithMockServices([Services::USER_ROLE_MANAGER => $userRoleManager]);
    }

    public function operationDataProvider(): array
    {
        return [
            [ApiPermissionForEmpNumber::OPERATION_READ, new ResourcePermission(true, false, false, false)],
            [ApiPermissionForEmpNumber::OPERATION_CREATE, new ResourcePermission(false, true, false, false)],
            [ApiPermissionForEmpNumber::OPERATION_UPDATE, new ResourcePermission(false, false, true, false)],
            [ApiPermissionForEmpNumber::OPERATION_DELETE, new ResourcePermission(false, false, false, true)],
        ];
    }

    /**
     * @dataProvider operationDataProvider
     */
    public function testAllowedOperation(string $operation, ResourcePermission $permission): void
    {
        $this->mockPermission($permission, 5);
        $this->assertTrue((new ApiPermissionForEmpNumber(self::API, $operation))->validate('5'));
    }

    /**
     * @dataProvider operationDataProvider
     */
    public function testOtherOperationsDoNotSatisfy(string $operation, ResourcePermission $permission): void
    {
        // Every bit except the one this operation needs
        $inverse = new ResourcePermission(
            !$permission->canRead(),
            !$permission->canCreate(),
            !$permission->canUpdate(),
            !$permission->canDelete()
        );
        $this->mockPermission($inverse, 5);
        $this->expectException(ForbiddenException::class);
        (new ApiPermissionForEmpNumber(self::API, $operation))->validate(5);
    }

    public function testDefaultOperationIsRead(): void
    {
        $this->mockPermission(new ResourcePermission(true, false, false, false), 7);
        $this->assertTrue((new ApiPermissionForEmpNumber(self::API))->validate(7));
    }

    public function invalidInputDataProvider(): array
    {
        return [[null], [''], ['abc'], [0], [-1], ['0']];
    }

    /**
     * @dataProvider invalidInputDataProvider
     */
    public function testInvalidInputFailsValidation($input): void
    {
        $this->mockPermission(new ResourcePermission(true, true, true, true));
        $this->assertFalse((new ApiPermissionForEmpNumber(self::API))->validate($input));
    }

    public function testInvalidOperation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ApiPermissionForEmpNumber(self::API, 'view');
    }
}
