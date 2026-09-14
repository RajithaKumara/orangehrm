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

namespace OrangeHRM\Tests\Mcp\Tool;

use OrangeHRM\Mcp\Service\McpAccessPolicy;
use OrangeHRM\Mcp\Tool\Curated\CurrentEmployeeTool;
use OrangeHRM\Mcp\Tool\Rest\GenericTool;
use OrangeHRM\Mcp\Tool\Rest\RestRoute;
use OrangeHRM\Mcp\Tool\ToolPolicyGate;
use OrangeHRM\Mcp\Tool\ToolRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group Tool
 */
class ToolPolicyGateTest extends TestCase
{
    private function policy(array $overrides = []): McpAccessPolicy
    {
        // Stubbed rather than mocked through the container: the gate only reads
        // decisions, so the behaviour under test is the branching, not storage.
        return new class ($overrides) extends McpAccessPolicy {
            private array $o;

            public function __construct(array $overrides)
            {
                $this->o = $overrides;
            }

            public function isReadOnly(): bool
            {
                return $this->o['readOnly'] ?? true;
            }

            public function isToolEnabled(string $toolName): bool
            {
                return in_array($toolName, $this->o['enabledTools'] ?? [], true);
            }

            public function isGenericRouterEnabled(): bool
            {
                return $this->o['generic'] ?? false;
            }
        };
    }

    private function genericTool(string $method = 'GET'): GenericTool
    {
        return new GenericTool(
            new RestRoute($method, '/api/v2/admin/locations', 'OrangeHRM\\Admin\\Api\\LocationAPI', [])
        );
    }

    private function registryWithCurated(): ToolRegistry
    {
        return new ToolRegistry([new CurrentEmployeeTool()]);
    }

    public function testCuratedToolsFollowTheEnabledList(): void
    {
        $registry = $this->registryWithCurated();
        $curated = new CurrentEmployeeTool();

        $off = new ToolPolicyGate($this->policy(['enabledTools' => []]), $registry);
        $this->assertFalse($off->isAllowed($curated));

        $on = new ToolPolicyGate(
            $this->policy(['enabledTools' => ['mcp_get_current_employee']]),
            $registry
        );
        $this->assertTrue($on->isAllowed($curated));
    }

    /**
     * Generic tools are governed by one switch, not the per-tool list. Naming
     * hundreds of them individually is not a control an admin would ever
     * review honestly.
     */
    public function testGenericToolsFollowTheRouterSwitchNotTheEnabledList(): void
    {
        $registry = $this->registryWithCurated();
        $generic = $this->genericTool();

        $off = new ToolPolicyGate($this->policy(['generic' => false]), $registry);
        $this->assertFalse($off->isAllowed($generic));

        // Naming it in the curated list must not help: it is not curated.
        $named = new ToolPolicyGate(
            $this->policy(['generic' => false, 'enabledTools' => [$generic->getName()]]),
            $registry
        );
        $this->assertFalse($named->isAllowed($generic));

        $on = new ToolPolicyGate($this->policy(['generic' => true]), $registry);
        $this->assertTrue($on->isAllowed($generic));
    }

    /**
     * Read-only mode outranks both switches. It is the circuit breaker, so
     * nothing below it may re-enable a write.
     */
    public function testReadOnlyModeOutranksEverything(): void
    {
        $registry = $this->registryWithCurated();
        $write = $this->genericTool('POST');

        $readOnly = new ToolPolicyGate(
            $this->policy(['generic' => true, 'readOnly' => true]),
            $registry
        );
        $this->assertFalse($readOnly->isAllowed($write));

        $readWrite = new ToolPolicyGate(
            $this->policy(['generic' => true, 'readOnly' => false]),
            $registry
        );
        $this->assertTrue($readWrite->isAllowed($write));
    }

    public function testRefusalReasonNamesTheActualCause(): void
    {
        $registry = $this->registryWithCurated();

        $gate = new ToolPolicyGate($this->policy(['generic' => true, 'readOnly' => true]), $registry);
        $this->assertStringContainsString('read-only', $gate->refusalReason($this->genericTool('DELETE')));

        $gate = new ToolPolicyGate($this->policy(['generic' => false]), $registry);
        $this->assertStringContainsString('REST tools', $gate->refusalReason($this->genericTool()));

        $gate = new ToolPolicyGate($this->policy(['enabledTools' => []]), $registry);
        $this->assertStringContainsString(
            'mcp_get_current_employee',
            $gate->refusalReason(new CurrentEmployeeTool())
        );
    }
}
