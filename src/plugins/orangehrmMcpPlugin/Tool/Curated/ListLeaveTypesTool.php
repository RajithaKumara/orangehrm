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

namespace OrangeHRM\Mcp\Tool\Curated;

use OrangeHRM\Leave\Api\LeaveTypeAPI;
use OrangeHRM\Mcp\Tool\ToolInterface;

/**
 * Master data for the leave module. Exercises a third module, which makes the
 * per-module RBAC and disabled-module checks observable in the prototype.
 */
class ListLeaveTypesTool implements ToolInterface
{
    public function getName(): string
    {
        return 'mcp_list_leave_types';
    }

    public function getDescription(): string
    {
        return 'List the leave types configured in this OrangeHRM instance, with their ids. '
            . 'Use this to answer "what kinds of leave can I take" or to resolve a leave '
            . 'type name to its id.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of leave types to return (default 100, max 200).',
                    'minimum' => 1,
                    'maximum' => 200,
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function getApiClass(): string
    {
        return LeaveTypeAPI::class;
    }

    public function getPath(): string
    {
        return '/api/v2/leave/leave-types';
    }

    public function getModule(): string
    {
        return 'leave';
    }

    public function getRouteAttributes(): array
    {
        return [];
    }

    public function buildQueryParams(array $arguments): array
    {
        return [
            'limit' => isset($arguments['limit']) ? (int) $arguments['limit'] : 100,
            'offset' => 0,
        ];
    }
}
