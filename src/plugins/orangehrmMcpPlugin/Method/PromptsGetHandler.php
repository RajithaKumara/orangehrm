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
use OrangeHRM\Mcp\JsonRpc\EnvelopeException;
use OrangeHRM\Mcp\JsonRpc\ErrorCode;
use OrangeHRM\Mcp\Service\McpPromptService;

/**
 * MCP `prompts/get`: renders one stored prompt with the caller's arguments.
 *
 * Returns a single user message. Multi-turn prompts are a column away if they
 * are ever wanted, and one text template is what keeps a row editable by
 * someone working in a SQL client rather than a JSON editor.
 */
class PromptsGetHandler implements MethodHandlerInterface
{
    private McpPromptService $prompts;

    public function __construct(McpPromptService $prompts)
    {
        $this->prompts = $prompts;
    }

    public function getMethodName(): string
    {
        return 'prompts/get';
    }

    public function handle(Envelope $envelope)
    {
        $params = $envelope->getParams();
        $name = $params['name'] ?? null;

        if (!is_string($name) || $name === '') {
            throw new EnvelopeException(
                ErrorCode::INVALID_PARAMS,
                'Invalid params: `name` is required',
                ['category' => ErrorCode::CATEGORY_VALIDATION]
            );
        }

        $prompt = $this->prompts->getEnabledPromptByName($name);
        if ($prompt === null) {
            throw new EnvelopeException(
                ErrorCode::METHOD_NOT_FOUND,
                sprintf('Unknown prompt `%s`', $name),
                ['category' => ErrorCode::CATEGORY_NOT_FOUND, 'prompt' => $name]
            );
        }

        $arguments = $params['arguments'] ?? [];
        if (!is_array($arguments)) {
            throw new EnvelopeException(
                ErrorCode::INVALID_PARAMS,
                'Invalid params: `arguments` must be an object',
                ['category' => ErrorCode::CATEGORY_VALIDATION, 'prompt' => $name]
            );
        }

        $missing = $this->prompts->getMissingArguments($prompt, $arguments);
        if ($missing !== []) {
            throw new EnvelopeException(
                ErrorCode::INVALID_PARAMS,
                sprintf('Missing required argument(s): %s', implode(', ', $missing)),
                ['category' => ErrorCode::CATEGORY_VALIDATION, 'prompt' => $name, 'missing' => $missing]
            );
        }

        $result = ['messages' => [[
            'role' => 'user',
            'content' => ['type' => 'text', 'text' => $this->prompts->render($prompt, $arguments)],
        ]]];

        if ($prompt->getDescription() !== null && $prompt->getDescription() !== '') {
            $result['description'] = $prompt->getDescription();
        }

        return $result;
    }
}
