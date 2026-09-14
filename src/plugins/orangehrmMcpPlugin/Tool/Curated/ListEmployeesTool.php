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

use OrangeHRM\Mcp\Tool\ToolInterface;
use OrangeHRM\Pim\Api\EmployeeAPI;

/**
 * Employee directory search. Results are already limited to the employees the
 * authenticated user may see, because the dispatch runs through the same
 * endpoint and UserRoleManager scope the web UI uses.
 */
class ListEmployeesTool implements ToolInterface
{
    public function getName(): string
    {
        return 'mcp_list_employees';
    }

    public function getDescription(): string
    {
        return 'Search employees visible to the current user. Optionally filter by name. '
            . 'Returns employee number, first and last name, job title and sub unit. '
            . 'Use this to find the employee number for a person referred to by name.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Full or partial employee name to search for.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of employees to return (default 50, max 200).',
                    'minimum' => 1,
                    'maximum' => 200,
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function getApiClass(): string
    {
        return EmployeeAPI::class;
    }

    public function getPath(): string
    {
        return '/api/v2/pim/employees';
    }

    public function getModule(): string
    {
        return 'pim';
    }

    public function getVerb(): string
    {
        return 'GET';
    }

    public function isWriteOperation(): bool
    {
        return false;
    }

    public function isDestructive(): bool
    {
        return false;
    }

    public function getRouteAttributes(): array
    {
        return ['_key' => 'empNumber'];
    }

    public function buildParams(array $arguments): array
    {
        $params = [
            'limit' => isset($arguments['limit']) ? (int) $arguments['limit'] : 50,
            'offset' => 0,
        ];
        if (isset($arguments['name']) && $arguments['name'] !== '') {
            $params['name'] = (string) $arguments['name'];
        }
        return $params;
    }
}
