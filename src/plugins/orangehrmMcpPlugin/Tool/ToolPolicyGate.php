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
use OrangeHRM\Mcp\Service\McpApiPermissionService;

/**
 * Whether the administrator's configuration permits a given tool.
 *
 * Three gates, checked in escalating order of specificity, and a tool must pass
 * all three:
 *
 *  1. **Product exclusion** (RestrictedApis). Areas that are never reachable
 *     through MCP whatever anyone configures. Code, not data, so no tenant can
 *     turn it off.
 *  2. **The verb switch** (McpAccessPolicy). One switch per verb, above the
 *     grid. Delete off means no delete tool is offered, whatever its row says.
 *  3. **The per-class row** (ohrm_mcp_api_permission). Which API classes this
 *     organisation has enabled for that verb.
 *
 * All three narrow; none widens. After this gate the caller's own RBAC still
 * runs in RestDispatcher, so a tool being *offered* never implies the person
 * asking may actually use it.
 *
 * Curated and generic tools go through exactly this path. A curated tool is
 * governed by its backing API class like any other, so enabling PIM reads
 * enables both `mcp_list_employees` and the generic employee reads, and an
 * administrator has one thing to reason about rather than two lists.
 */
class ToolPolicyGate
{
    private McpAccessPolicy $policy;

    private McpApiPermissionService $permissions;

    public function __construct(McpAccessPolicy $policy, ?McpApiPermissionService $permissions = null)
    {
        $this->policy = $policy;
        $this->permissions = $permissions ?? new McpApiPermissionService();
    }

    public function isAllowed(ToolInterface $tool): bool
    {
        $apiClass = $tool->getApiClass();
        $method = $tool->getVerb();
        $verb = ApiVerb::fromMethod($method);

        if (!RestrictedApis::isAllowed($apiClass, $method)) {
            return false;
        }
        if (!$this->policy->isVerbEnabled($verb)) {
            return false;
        }
        return $this->permissions->isAllowed($apiClass, $verb);
    }

    /**
     * Why the call was refused, in terms of the control an administrator would
     * have to change.
     *
     * Deliberately names the switch rather than the endpoint: a caller who is
     * refused should learn what to ask for, not what exists behind the refusal.
     */
    public function refusalReason(ToolInterface $tool): string
    {
        $apiClass = $tool->getApiClass();
        $method = $tool->getVerb();
        $verb = ApiVerb::fromMethod($method);

        if (!RestrictedApis::isAllowed($apiClass, $method)) {
            return RestrictedApis::refusalReason($apiClass, $method);
        }
        if (!$this->policy->isVerbEnabled($verb)) {
            return sprintf(
                'This instance does not allow %s operations through MCP',
                $verb
            );
        }
        return sprintf('Tool `%s` is not enabled on this instance', $tool->getName());
    }

    /**
     * True when the refusal was the verb switch rather than an individual row,
     * so tools/call can return the read-only error code the MCP client
     * understands instead of a generic authorization failure.
     */
    public function isBlockedByVerbSwitch(ToolInterface $tool): bool
    {
        $verb = ApiVerb::fromMethod($tool->getVerb());
        return ApiVerb::isWrite($verb) && !$this->policy->isVerbEnabled($verb);
    }
}
