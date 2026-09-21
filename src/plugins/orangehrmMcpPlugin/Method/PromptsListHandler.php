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

namespace OrangeHRM\Mcp\Method;

use OrangeHRM\Mcp\JsonRpc\Envelope;
use OrangeHRM\Mcp\Service\McpPromptService;

/**
 * MCP `prompts/list`.
 *
 * The catalog is `ohrm_mcp_prompt`, so what a client sees is whatever the
 * customer has in that table: ours edited, theirs added, any of them switched
 * off. Nothing here is compiled in.
 */
class PromptsListHandler implements MethodHandlerInterface
{
    private McpPromptService $prompts;

    public function __construct(McpPromptService $prompts)
    {
        $this->prompts = $prompts;
    }

    public function getMethodName(): string
    {
        return 'prompts/list';
    }

    public function handle(Envelope $envelope)
    {
        $listed = [];
        foreach ($this->prompts->getEnabledPrompts() as $prompt) {
            $entry = ['name' => $prompt->getName()];

            if ($prompt->getTitle() !== null && $prompt->getTitle() !== '') {
                $entry['title'] = $prompt->getTitle();
            }
            if ($prompt->getDescription() !== null && $prompt->getDescription() !== '') {
                $entry['description'] = $prompt->getDescription();
            }

            $arguments = $this->prompts->getArgumentSpec($prompt);
            if ($arguments !== []) {
                $entry['arguments'] = $arguments;
            }

            $listed[] = $entry;
        }

        return ['prompts' => $listed];
    }
}
