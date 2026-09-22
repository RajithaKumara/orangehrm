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

namespace OrangeHRM\Mcp\Tool;

/**
 * The two values `ohrm_mcp_api_permission.sensitivity` can hold.
 *
 * A native PHP enum is what this wants to be, and it cannot be one: enums need
 * PHP 8.1 and `src/composer.json` still declares `^7.4|^8.0`. The codebase has
 * no native enum anywhere for the same reason, and has been bitten before by a
 * language feature that looked available -- constants in traits, which need
 * 8.2. So the enumeration lives where it can be enforced today: the column is
 * `ENUM('normal','sensitive')`, and these constants are the names the runtime
 * compares against.
 *
 * **Which classes are sensitive is not here.** That is seed data, and it lives
 * with the migration that seeds it, in
 * `installer/Migration/V5_10_0/api-sensitivity.yaml`. Nothing reads it at
 * runtime: the screen and `tools/list` read the column.
 *
 * Unlike a label, an upgrade rewrites this column. A warning a tenant can
 * quietly downgrade is not a warning.
 */
final class ApiSensitivity
{
    /** Everything the flag below does not cover. */
    public const NORMAL = 'normal';

    /** Compensation, legal status, evaluation, attachments, accounts. */
    public const SENSITIVE = 'sensitive';

    /**
     * @return string[] both values, least sensitive first
     */
    public static function levels(): array
    {
        return [self::NORMAL, self::SENSITIVE];
    }

    public static function isValid(string $level): bool
    {
        return in_array($level, self::levels(), true);
    }
}
