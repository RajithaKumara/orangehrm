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
use OrangeHRM\Pim\Api\MyInfoAPI;

/**
 * Identity primitive. Lets the assistant answer "my ..." questions without
 * asking the human for an employee number.
 */
class CurrentEmployeeTool implements ToolInterface
{
    public function getName(): string
    {
        return 'mcp_get_current_employee';
    }

    public function getDescription(): string
    {
        return 'Get the OrangeHRM employee record of the currently authenticated user, '
            . 'including employee number, full name, job title, sub unit and supervisors. '
            . 'Call this first to resolve who "I" or "me" refers to before using tools '
            . 'that need an employee number.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object) [],
            'additionalProperties' => false,
        ];
    }

    public function getApiClass(): string
    {
        return MyInfoAPI::class;
    }

    public function getPath(): string
    {
        return '/api/v2/pim/myself';
    }

    public function getModule(): string
    {
        return 'pim';
    }

    public function getRouteAttributes(): array
    {
        // MyInfoAPI is a ResourceEndpoint with no id in its path; its route
        // supplies `id: 0` so GenericRestController routes GET to getOne().
        return ['id' => 0];
    }

    public function buildQueryParams(array $arguments): array
    {
        return [];
    }
}
