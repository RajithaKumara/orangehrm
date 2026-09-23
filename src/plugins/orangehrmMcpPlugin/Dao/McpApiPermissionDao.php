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

namespace OrangeHRM\Mcp\Dao;

use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\McpApiPermission;
use OrangeHRM\Mcp\Tool\ApiVerb;

class McpApiPermissionDao extends BaseDao
{
    /**
     * Every row, as plain arrays keyed by API class.
     *
     * Array hydration rather than entities because this is on the hot path:
     * tools/list asks about several hundred tools, and the answer for all of
     * them comes from these ~190 rows. Hydrating 190 objects to read five
     * scalars off each is work nobody needs.
     *
     * @return array<string,array{label:?string,read:bool,create:bool,update:bool,delete:bool,sensitivity:int}>
     */
    public function getPermissionMap(): array
    {
        $rows = $this->createQueryBuilder(McpApiPermission::class, 'p')
            ->select(
                'p.apiName',
                'p.label',
                'p.canRead',
                'p.canCreate',
                'p.canUpdate',
                'p.canDelete',
                'p.sensitivity',
                'p.externalContent'
            )
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['apiName']] = [
                'label' => is_string($row['label']) && $row['label'] !== '' ? $row['label'] : null,
                ApiVerb::READ => (bool) $row['canRead'],
                ApiVerb::CREATE => (bool) $row['canCreate'],
                ApiVerb::UPDATE => (bool) $row['canUpdate'],
                ApiVerb::DELETE => (bool) $row['canDelete'],
                // Kept as stored rather than validated away: a level this
                // build does not know is a newer release's, and nameOf()
                // degrades it to the nearest one below.
                'sensitivity' => (int) $row['sensitivity'],
                'externalContent' => (bool) $row['externalContent'],
            ];
        }
        return $map;
    }

    /**
     * Apply an administrator's changes.
     *
     * Only the four verb flags are writable. Sensitivity is not, and a row for
     * an API class that does not exist is not created -- the screen renders from
     * the live catalog, so a name outside it is either stale or invented, and
     * neither deserves a row.
     *
     * @param array<string,array<string,bool>> $flagsByApiClass
     * @return int rows actually changed
     */
    public function saveFlags(array $flagsByApiClass): int
    {
        if ($flagsByApiClass === []) {
            return 0;
        }

        // Every row in one query, then matched in memory. The screen posts all
        // ~190 classes on every save, and looking each one up individually would
        // be 190 round trips to change two checkboxes.
        $rows = [];
        foreach ($this->getRepository(McpApiPermission::class)->findAll() as $entity) {
            /** @var McpApiPermission $entity */
            $rows[$entity->getApiName()] = $entity;
        }

        $changed = 0;

        foreach ($flagsByApiClass as $apiClass => $flags) {
            $row = $rows[$apiClass] ?? null;
            if ($row === null) {
                continue;
            }

            $read = (bool) ($flags[ApiVerb::READ] ?? false);
            $create = (bool) ($flags[ApiVerb::CREATE] ?? false);
            $update = (bool) ($flags[ApiVerb::UPDATE] ?? false);
            $delete = (bool) ($flags[ApiVerb::DELETE] ?? false);

            if (
                $row->canRead() === $read
                && $row->canCreate() === $create
                && $row->canUpdate() === $update
                && $row->canDelete() === $delete
            ) {
                continue;
            }

            $row->setCanRead($read);
            $row->setCanCreate($create);
            $row->setCanUpdate($update);
            $row->setCanDelete($delete);
            $this->getEntityManager()->persist($row);
            $changed++;
        }

        if ($changed > 0) {
            $this->getEntityManager()->flush();
        }
        return $changed;
    }
}
