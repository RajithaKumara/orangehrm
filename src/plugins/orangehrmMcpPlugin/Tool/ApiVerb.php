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
 * The four things a tool can do, and the mapping from HTTP methods onto them.
 *
 * OrangeHRM's own permission model is already read/create/update/delete
 * (`ohrm_api_permission` -> `ohrm_user_role_data_group`), so MCP uses the same
 * four words rather than inventing a parallel vocabulary. An administrator who
 * understands the data-group screen understands this one.
 *
 * PATCH maps to update. No `/api/v2` route uses it today; mapping it anyway
 * means a future PATCH endpoint is governed rather than unclassified, and
 * unclassified would mean refused-with-a-confusing-message.
 */
final class ApiVerb
{
    public const READ = 'read';
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    private const BY_METHOD = [
        'GET' => self::READ,
        'POST' => self::CREATE,
        'PUT' => self::UPDATE,
        'PATCH' => self::UPDATE,
        'DELETE' => self::DELETE,
    ];

    /**
     * @return string[] in escalating order of consequence
     */
    public static function all(): array
    {
        return [self::READ, self::CREATE, self::UPDATE, self::DELETE];
    }

    /**
     * Anything unrecognised is treated as an update rather than a read: an
     * unknown method is not a safe method, and the conservative answer is the
     * one that stays behind a switch that ships off.
     */
    public static function fromMethod(string $method): string
    {
        return self::BY_METHOD[strtoupper($method)] ?? self::UPDATE;
    }

    public static function isWrite(string $verb): bool
    {
        return $verb !== self::READ;
    }

    /**
     * How much attention the verb itself deserves, independent of the data
     * behind it. Drives the colour of the admin screen's column headings.
     */
    public static function risk(string $verb): string
    {
        if ($verb === self::DELETE) {
            return 'danger';
        }
        if ($verb === self::CREATE || $verb === self::UPDATE) {
            return 'warn';
        }
        return 'none';
    }
}
