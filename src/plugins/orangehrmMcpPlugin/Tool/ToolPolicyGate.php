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

namespace OrangeHRM\Mcp\Tool;

use OrangeHRM\Mcp\Service\McpAccessPolicy;

/**
 * Decides whether the administrator's configuration permits a given tool.
 *
 * The two layers are governed differently, and deliberately so:
 *
 *  - Curated tools are named individually in the enabled-tools list. There are
 *    a few dozen at most, each written for a purpose, so per-tool control is
 *    meaningful and the admin screen can show them.
 *  - Generic tools are governed by one switch for the whole surface. There are
 *    hundreds; a screen with hundreds of checkboxes is not a control, it is a
 *    wall, and an admin would never review it honestly.
 *
 * Both layers are additionally subject to read-only mode and, at dispatch, to
 * the user's own RBAC. This gate only ever narrows.
 */
class ToolPolicyGate
{
    private McpAccessPolicy $policy;

    private ToolRegistry $registry;

    public function __construct(McpAccessPolicy $policy, ToolRegistry $registry)
    {
        $this->policy = $policy;
        $this->registry = $registry;
    }

    public function isAllowed(ToolInterface $tool): bool
    {
        if ($tool->isWriteOperation() && $this->policy->isReadOnly()) {
            return false;
        }

        return $this->isCurated($tool)
            ? $this->policy->isToolEnabled($tool->getName())
            : $this->policy->isGenericRouterEnabled();
    }

    /**
     * Why the call was refused, for an error message that tells the caller
     * something they can act on.
     */
    public function refusalReason(ToolInterface $tool): string
    {
        if ($tool->isWriteOperation() && $this->policy->isReadOnly()) {
            return 'This instance is in read-only mode; write tools are disabled';
        }
        if ($this->isCurated($tool)) {
            return sprintf('Tool `%s` is not enabled on this instance', $tool->getName());
        }
        return 'Direct REST tools are not enabled on this instance';
    }

    private function isCurated(ToolInterface $tool): bool
    {
        return array_key_exists($tool->getName(), $this->registry->curated());
    }
}
