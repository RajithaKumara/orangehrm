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

use OrangeHRM\Entity\McpPrompt;
use OrangeHRM\Mcp\Dao\McpPromptDao;

/**
 * Reads the prompt catalog and renders one.
 *
 * Everything here tolerates a hand-edited row, because hand-editing is the
 * point: a customer changing wording in a SQL client should never be able to
 * take the prompt listing down. A malformed `arguments` column degrades to "no
 * arguments" and an unknown placeholder is left in place rather than throwing.
 */
class McpPromptService
{
    private ?McpPromptDao $dao = null;

    public function getDao(): McpPromptDao
    {
        if ($this->dao === null) {
            $this->dao = new McpPromptDao();
        }
        return $this->dao;
    }

    /**
     * @return McpPrompt[]
     */
    public function getEnabledPrompts(): array
    {
        return $this->getDao()->getEnabledPrompts();
    }

    public function getEnabledPromptByName(string $name): ?McpPrompt
    {
        return $this->getDao()->getEnabledPromptByName($name);
    }

    /**
     * The argument declarations a prompt publishes.
     *
     * @return array<int,array{name:string,description?:string,required?:bool}>
     */
    public function getArgumentSpec(McpPrompt $prompt): array
    {
        $raw = $prompt->getArguments();
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $spec = [];
        foreach ($decoded as $argument) {
            if (!is_array($argument) || !isset($argument['name']) || !is_string($argument['name'])) {
                continue;
            }
            $entry = ['name' => $argument['name']];
            if (isset($argument['description']) && is_string($argument['description'])) {
                $entry['description'] = $argument['description'];
            }
            if (isset($argument['required'])) {
                $entry['required'] = (bool) $argument['required'];
            }
            $spec[] = $entry;
        }
        return $spec;
    }

    /**
     * @return string[] names of declared arguments the caller did not supply
     */
    public function getMissingArguments(McpPrompt $prompt, array $supplied): array
    {
        $missing = [];
        foreach ($this->getArgumentSpec($prompt) as $argument) {
            if (empty($argument['required'])) {
                continue;
            }
            $value = $supplied[$argument['name']] ?? null;
            if ($value === null || (is_string($value) && trim($value) === '')) {
                $missing[] = $argument['name'];
            }
        }
        return $missing;
    }

    /**
     * Substitutes `{{name}}` placeholders.
     *
     * A placeholder with no matching argument is left as written. The
     * alternative -- blanking it -- silently changes what the prompt asks for,
     * and a visible `{{employee}}` in the output tells whoever edited the row
     * exactly what they got wrong.
     */
    public function render(McpPrompt $prompt, array $arguments): string
    {
        $text = $prompt->getTemplate();

        foreach ($arguments as $name => $value) {
            if (!is_string($name) || is_array($value) || is_object($value)) {
                continue;
            }
            $text = str_replace('{{' . $name . '}}', (string) $value, $text);
        }

        return $text;
    }
}
