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

namespace OrangeHRM\Mcp\Service;

use OrangeHRM\Mcp\Dao\McpApiPermissionDao;
use OrangeHRM\Mcp\Tool\ApiSensitivity;
use OrangeHRM\Mcp\Tool\ApiVerb;
use Throwable;

/**
 * The per-API-class half of the access decision: for this API class, may MCP
 * read, create, update or delete?
 *
 * Reads `ohrm_mcp_api_permission` once per request and answers from memory.
 * tools/list asks about every tool in the catalog and the generic router
 * contributes hundreds, so the query must happen once, not per tool.
 *
 * Absence is refusal. An API class with no row -- a new endpoint family added by
 * an upgrade whose migration has not run, or a class removed from the shipped
 * list -- is not reachable until someone decides it should be. The alternative,
 * treating an unknown class as permitted, would mean every future endpoint
 * arrives switched on without anyone looking at it.
 */
class McpApiPermissionService
{
    private ?McpApiPermissionDao $dao = null;

    /**
     * @var array<string,array<string,mixed>>|null
     */
    private ?array $map = null;

    public function getDao(): McpApiPermissionDao
    {
        if ($this->dao === null) {
            $this->dao = new McpApiPermissionDao();
        }
        return $this->dao;
    }

    /**
     * @return array<string,array{read:bool,create:bool,update:bool,delete:bool,sensitivity:int}>
     */
    public function getPermissionMap(): array
    {
        if ($this->map === null) {
            try {
                $this->map = $this->getDao()->getPermissionMap();
            } catch (Throwable $e) {
                // The table is missing or unreadable -- an install whose
                // migration has not completed, most likely. Refuse everything
                // rather than guessing; the doctor command reports the cause.
                $this->map = [];
            }
        }
        return $this->map;
    }

    public function isAllowed(string $apiClass, string $verb): bool
    {
        $row = $this->getPermissionMap()[ltrim($apiClass, '\\')] ?? null;
        if ($row === null) {
            return false;
        }
        return (bool) ($row[$verb] ?? false);
    }

    public function getSensitivity(string $apiClass): int
    {
        $row = $this->getPermissionMap()[ltrim($apiClass, '\\')] ?? null;

        return $row === null ? ApiSensitivity::NORMAL : (int) ($row['sensitivity'] ?? ApiSensitivity::NORMAL);
    }

    /**
     * Whether this class's payload carries text written outside the company.
     *
     * Unknown classes answer false. That is the safe direction here: the flag
     * adds a warning, and inventing one for an endpoint nobody has classified
     * would teach administrators to ignore it.
     */
    public function hasExternalContent(string $apiClass): bool
    {
        $row = $this->getPermissionMap()[ltrim($apiClass, '\\')] ?? null;

        return $row !== null && !empty($row['externalContent']);
    }

    /**
     * How many classes have at least one verb enabled, and how many verbs are
     * enabled in total. Shown on the admin screen so the size of the surface is
     * visible without counting checkboxes.
     *
     * @return array{classes:int,enabledClasses:int,enabledVerbs:array<string,int>}
     */
    public function getSummary(): array
    {
        $enabledVerbs = array_fill_keys(ApiVerb::all(), 0);
        $enabledClasses = 0;

        foreach ($this->getPermissionMap() as $row) {
            $any = false;
            foreach (ApiVerb::all() as $verb) {
                if (!empty($row[$verb])) {
                    $enabledVerbs[$verb]++;
                    $any = true;
                }
            }
            if ($any) {
                $enabledClasses++;
            }
        }

        return [
            'classes' => count($this->getPermissionMap()),
            'enabledClasses' => $enabledClasses,
            'enabledVerbs' => $enabledVerbs,
        ];
    }

    /**
     * Forget the cached map. Only needed after a save inside the same request,
     * where the screen re-reads what it just wrote.
     */
    public function reset(): void
    {
        $this->map = null;
    }
}
