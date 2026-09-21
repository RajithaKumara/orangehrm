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
use OrangeHRM\Entity\McpPrompt;

class McpPromptDao extends BaseDao
{
    /**
     * Enabled prompts, in name order so `prompts/list` is stable between calls.
     *
     * @return McpPrompt[]
     */
    public function getEnabledPrompts(): array
    {
        $qb = $this->createQueryBuilder(McpPrompt::class, 'prompt');
        $qb->andWhere('prompt.enabled = :enabled')
            ->setParameter('enabled', true)
            ->addOrderBy('prompt.name', 'ASC');

        return $qb->getQuery()->execute();
    }

    /**
     * A disabled prompt is not found on purpose: switching one off should make
     * it behave exactly as though it had been deleted.
     */
    public function getEnabledPromptByName(string $name): ?McpPrompt
    {
        return $this->getRepository(McpPrompt::class)->findOneBy(['name' => $name, 'enabled' => true]);
    }
}
