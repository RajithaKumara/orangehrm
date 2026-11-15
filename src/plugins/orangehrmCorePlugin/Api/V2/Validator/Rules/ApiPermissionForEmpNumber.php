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

namespace OrangeHRM\Core\Api\V2\Validator\Rules;

use InvalidArgumentException;
use OrangeHRM\Core\Api\V2\Exception\ForbiddenException;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;

/**
 * Checks the user's permission on an API for the given employee's record, counting permissions
 * granted only for the user's own record (self) only when the employee is the logged-in employee.
 * Throws ForbiddenException (403) when the operation is not permitted.
 *
 * This rule does not check whether the employee is accessible to the user (e.g. a subordinate);
 * always place it after Rules::IN_ACCESSIBLE_EMP_NUMBERS in the same ParamRule.
 */
class ApiPermissionForEmpNumber extends AbstractRule
{
    use UserRoleManagerTrait;

    public const OPERATION_READ = 'read';
    public const OPERATION_CREATE = 'create';
    public const OPERATION_UPDATE = 'update';
    public const OPERATION_DELETE = 'delete';

    private const PERMISSION_GETTERS = [
        self::OPERATION_READ => 'canRead',
        self::OPERATION_CREATE => 'canCreate',
        self::OPERATION_UPDATE => 'canUpdate',
        self::OPERATION_DELETE => 'canDelete',
    ];

    private string $apiClassName;
    private string $operation;

    /**
     * @param string $apiClassName Fully qualified API class name, as in the route `_api` attribute
     * @param string $operation One of self::OPERATION_*
     */
    public function __construct(string $apiClassName, string $operation = self::OPERATION_READ)
    {
        if (!isset(self::PERMISSION_GETTERS[$operation])) {
            throw new InvalidArgumentException("Invalid operation `$operation`");
        }
        $this->apiClassName = $apiClassName;
        $this->operation = $operation;
    }

    /**
     * @inheritDoc
     */
    public function validate($input): bool
    {
        if (!(is_numeric($input) && $input > 0)) {
            return false;
        }

        $permission = $this->getUserRoleManager()->getApiPermissionsForEmployee($this->apiClassName, (int)$input);
        $getter = self::PERMISSION_GETTERS[$this->operation];
        if (!$permission->$getter()) {
            throw new ForbiddenException();
        }
        return true;
    }
}
