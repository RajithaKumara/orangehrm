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
 * How sensitive the data behind an API class is, as a level.
 *
 * Stored as an integer in `ohrm_mcp_api_permission.sensitivity`, spaced so a
 * level can be added between the two without renumbering the rows that already
 * exist -- a middle tier would be 5, and every comparison below still holds.
 *
 * A plain integer column rather than a database ENUM on purpose: DBAL has no
 * `enum` type, so introspecting a table with one can fail outright, and the
 * set of levels then lives in a schema change rather than in a seed file.
 * Validation belongs here, where a bad value is a programming error, not in
 * the column, where it is a portability problem.
 *
 * These constants are the whole enumeration. **Which class sits at which level
 * is not here** -- that is seed data, in
 * `installer/Migration/V5_10_0/api-catalog.yaml`, and the runtime reads the
 * column rather than the file.
 */
final class ApiSensitivity
{
    /** Ordinary HR data: the name on the screen says what it is. */
    public const NORMAL = 0;

    /** Compensation, legal status, evaluation, attachments, accounts. */
    public const SENSITIVE = 10;

    /**
     * Names for the levels, for the screen and for `tools/list` -- a client
     * reading `"sensitivity": 10` learns nothing.
     */
    private const NAMES = [
        self::NORMAL => 'normal',
        self::SENSITIVE => 'sensitive',
    ];

    /**
     * @return int[] every level, least sensitive first
     */
    public static function levels(): array
    {
        return array_keys(self::NAMES);
    }

    public static function isValid(int $level): bool
    {
        return array_key_exists($level, self::NAMES);
    }

    /**
     * An unknown level reads as the nearest known one below it, so a row
     * carrying a level from a newer release degrades to the closest thing this
     * build understands rather than disappearing.
     */
    public static function nameOf(int $level): string
    {
        $name = self::NAMES[self::NORMAL];
        foreach (self::NAMES as $value => $candidate) {
            if ($level >= $value) {
                $name = $candidate;
            }
        }
        return $name;
    }
}
