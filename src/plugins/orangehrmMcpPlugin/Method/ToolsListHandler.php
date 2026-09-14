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
use OrangeHRM\Mcp\Service\McpAccessPolicy;
use OrangeHRM\Mcp\Tool\RestDispatcher;
use OrangeHRM\Mcp\Tool\ToolRegistry;

/**
 * MCP `tools/list`, projected through the caller's effective permissions so the
 * catalog never advertises a tool the user cannot invoke.
 *
 * This filtering is a usability measure, not the security boundary -- tools/call
 * re-checks every grant at dispatch (defense in depth, NFR-S1).
 */
class ToolsListHandler implements MethodHandlerInterface
{
    private ToolRegistry $registry;

    private RestDispatcher $dispatcher;

    private McpAccessPolicy $policy;

    public function __construct(ToolRegistry $registry, RestDispatcher $dispatcher, McpAccessPolicy $policy)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
        $this->policy = $policy;
    }

    public function getMethodName(): string
    {
        return 'tools/list';
    }

    public function handle(Envelope $envelope)
    {
        $tools = [];
        foreach ($this->registry->all() as $tool) {
            // Administrator's switch first: a tool nobody enabled is not offered
            // even to a user whose permissions would allow it.
            if (!$this->policy->isToolEnabled($tool->getName())) {
                continue;
            }
            if (!$this->dispatcher->isVisibleTo($tool)) {
                continue;
            }
            $tools[] = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'inputSchema' => $tool->getInputSchema(),
            ];
        }

        // The curated catalog is small enough to return in one page. Cursor
        // pagination arrives with the generic router (Story 2.8).
        return ['tools' => $tools];
    }
}
