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

use OrangeHRM\Mcp\Tool\Rest\GenericTool;
use OrangeHRM\Mcp\Tool\RestDispatcher;
use OrangeHRM\Mcp\Tool\ToolInterface;
use OrangeHRM\Mcp\Tool\ToolPolicyGate;
use OrangeHRM\Mcp\Tool\ToolRegistry;

/**
 * Ranked search across the tool catalog.
 *
 * Exists because the generic router contributes hundreds of tools, and putting
 * all of them in front of a model is worse than useless: it burns the context
 * window and degrades selection accuracy. A model calls this with a phrase and
 * gets back a handful of candidates it can then invoke by name.
 *
 * Results are filtered by the same gate and permission checks as `tools/list`,
 * so discovery can never reveal a tool the caller could not call.
 */
class DiscoverToolsHandler
{
    public const TOOL_NAME = 'mcp_discover_tools';

    public const DEFAULT_LIMIT = 10;
    public const MAX_LIMIT = 50;

    private ToolRegistry $registry;

    private RestDispatcher $dispatcher;

    private ToolPolicyGate $gate;

    public function __construct(ToolRegistry $registry, RestDispatcher $dispatcher, ToolPolicyGate $gate)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
        $this->gate = $gate;
    }

    public function getName(): string
    {
        return self::TOOL_NAME;
    }

    public function getDescription(): string
    {
        return 'Search the full OrangeHRM tool catalog by keyword and get back matching '
            . 'tool names you can then call. Use this when no listed tool obviously fits '
            . 'the question. The catalog covers most of the OrangeHRM REST API, so try '
            . 'terms like "leave balance", "attendance", "job title", "vacancy" or '
            . '"payslip" before concluding something is unavailable.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Words describing what you are looking for, e.g. "leave balance".',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum results to return (default 10, max 50).',
                    'minimum' => 1,
                    'maximum' => self::MAX_LIMIT,
                ],
            ],
            'required' => ['query'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @return array{content:array,isError:bool}
     */
    public function search(array $arguments): array
    {
        $query = isset($arguments['query']) ? trim((string) $arguments['query']) : '';
        $limit = isset($arguments['limit'])
            ? max(1, min(self::MAX_LIMIT, (int) $arguments['limit']))
            : self::DEFAULT_LIMIT;

        $terms = array_values(array_filter(preg_split('/[^a-z0-9]+/i', strtolower($query)) ?: []));

        $scored = [];
        foreach ($this->registry->all() as $tool) {
            if ($tool->getName() === self::TOOL_NAME) {
                continue;
            }
            if (!$this->gate->isAllowed($tool) || !$this->dispatcher->isVisibleTo($tool)) {
                continue;
            }
            $score = $this->score($tool, $terms);
            if ($score > 0) {
                $scored[] = ['tool' => $tool, 'score' => $score];
            }
        }

        // Stable order: score first, then name, so identical queries return
        // identical lists run to run.
        usort($scored, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score']
                ?: strcmp($a['tool']->getName(), $b['tool']->getName());
        });

        $matches = [];
        foreach (array_slice($scored, 0, $limit) as $entry) {
            /** @var ToolInterface $tool */
            $tool = $entry['tool'];
            $matches[] = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'module' => $tool->getModule(),
                'readOnly' => !$tool->isWriteOperation(),
                'score' => $entry['score'],
            ];
        }

        $payload = [
            'query' => $query,
            'matchCount' => count($matches),
            // An empty result is a legitimate answer, not an error: it tells the
            // model to stop guessing at tool names.
            'matches' => $matches,
        ];

        $text = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return [
            'content' => [['type' => 'text', 'text' => $text === false ? '{}' : $text]],
            'isError' => false,
        ];
    }

    /**
     * Term-frequency scoring, weighted by where the term appears. A name match
     * beats a description match, because tool names encode the endpoint and are
     * the more precise signal.
     *
     * @param string[] $terms
     */
    private function score(ToolInterface $tool, array $terms): int
    {
        if ($terms === []) {
            return 0;
        }

        $name = strtolower($tool->getName());
        $description = strtolower($tool->getDescription());
        $tags = $tool instanceof GenericTool ? implode(' ', $tool->getSearchTags()) : $tool->getModule();

        $score = 0;
        foreach ($terms as $term) {
            if (strpos($name, $term) !== false) {
                $score += 10;
            }
            if (strpos($tags, $term) !== false) {
                $score += 4;
            }
            if (strpos($description, $term) !== false) {
                $score += 2;
            }
        }

        // Require every term to have landed somewhere, so "leave balance" does
        // not match every tool that merely mentions leave.
        foreach ($terms as $term) {
            if (
                strpos($name, $term) === false
                && strpos($tags, $term) === false
                && strpos($description, $term) === false
            ) {
                return 0;
            }
        }

        return $score;
    }
}
