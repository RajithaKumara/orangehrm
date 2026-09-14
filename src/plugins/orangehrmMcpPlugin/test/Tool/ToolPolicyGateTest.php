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
use OrangeHRM\Mcp\Service\McpApiPermissionService;
use OrangeHRM\Mcp\Tool\ApiVerb;
use OrangeHRM\Mcp\Tool\Curated\CurrentEmployeeTool;
use OrangeHRM\Mcp\Tool\Rest\GenericTool;
use OrangeHRM\Mcp\Tool\Rest\RestRoute;
use OrangeHRM\Mcp\Tool\ToolPolicyGate;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group Tool
 */
class ToolPolicyGateTest extends TestCase
{
    private const LOCATION_API = 'OrangeHRM\\Admin\\Api\\LocationAPI';

    /**
     * @param array<string,bool> $switches
     */
    private function policy(array $switches = []): McpAccessPolicy
    {
        // Stubbed rather than mocked through the container: the gate only reads
        // decisions, so the behaviour under test is the branching, not storage.
        return new class ($switches) extends McpAccessPolicy {
            private array $switches;

            public function __construct(array $switches)
            {
                $this->switches = $switches;
            }

            public function isVerbEnabled(string $verb): bool
            {
                return $this->switches[$verb] ?? false;
            }
        };
    }

    /**
     * @param array<string,array<string,mixed>> $rows
     */
    private function permissions(array $rows = []): McpApiPermissionService
    {
        return new class ($rows) extends McpApiPermissionService {
            private array $rows;

            public function __construct(array $rows)
            {
                $this->rows = $rows;
            }

            public function getPermissionMap(): array
            {
                return $this->rows;
            }
        };
    }

    /**
     * @param array<string,bool> $verbs
     */
    private function row(array $verbs): array
    {
        return array_merge(
            [ApiVerb::READ => false, ApiVerb::CREATE => false, ApiVerb::UPDATE => false, ApiVerb::DELETE => false],
            $verbs
        );
    }

    private function genericTool(string $method = 'GET', string $apiClass = self::LOCATION_API): GenericTool
    {
        return new GenericTool(new RestRoute($method, '/api/v2/admin/locations', $apiClass, []));
    }

    public function testATargetedRowIsRequired(): void
    {
        $tool = $this->genericTool();

        $off = new ToolPolicyGate(
            $this->policy([ApiVerb::READ => true]),
            $this->permissions([])
        );
        $this->assertFalse($off->isAllowed($tool), 'a class with no row is refused');

        $on = new ToolPolicyGate(
            $this->policy([ApiVerb::READ => true]),
            $this->permissions([self::LOCATION_API => $this->row([ApiVerb::READ => true])])
        );
        $this->assertTrue($on->isAllowed($tool));
    }

    /**
     * The row is per verb, not per class. Enabling reads on an API must not make
     * its deletes reachable -- that is the whole point of four columns.
     */
    public function testEnablingOneVerbDoesNotEnableAnother(): void
    {
        $gate = new ToolPolicyGate(
            $this->policy([ApiVerb::READ => true, ApiVerb::DELETE => true]),
            $this->permissions([self::LOCATION_API => $this->row([ApiVerb::READ => true])])
        );

        $this->assertTrue($gate->isAllowed($this->genericTool('GET')));
        $this->assertFalse($gate->isAllowed($this->genericTool('DELETE')));
    }

    /**
     * And the reverse: a row is not enough on its own. The verb switch is the
     * circuit breaker, so it must outrank every row.
     */
    public function testVerbSwitchOutranksTheRow(): void
    {
        $rows = $this->permissions([
            self::LOCATION_API => $this->row([ApiVerb::READ => true, ApiVerb::DELETE => true]),
        ]);

        $off = new ToolPolicyGate($this->policy([ApiVerb::READ => true]), $rows);
        $this->assertFalse(
            $off->isAllowed($this->genericTool('DELETE')),
            'delete enabled on the row but the switch is off'
        );
        $this->assertTrue($off->isBlockedByVerbSwitch($this->genericTool('DELETE')));

        $on = new ToolPolicyGate(
            $this->policy([ApiVerb::READ => true, ApiVerb::DELETE => true]),
            $rows
        );
        $this->assertTrue($on->isAllowed($this->genericTool('DELETE')));
        $this->assertFalse($on->isBlockedByVerbSwitch($this->genericTool('DELETE')));
    }

    /**
     * Curated tools carry no special status: they are governed by the row for
     * the API class behind them, exactly like a generic tool.
     */
    public function testCuratedToolsFollowTheirBackingApiClass(): void
    {
        $curated = new CurrentEmployeeTool();

        $off = new ToolPolicyGate($this->policy([ApiVerb::READ => true]), $this->permissions([]));
        $this->assertFalse($off->isAllowed($curated));

        $on = new ToolPolicyGate(
            $this->policy([ApiVerb::READ => true]),
            $this->permissions([$curated->getApiClass() => $this->row([ApiVerb::READ => true])])
        );
        $this->assertTrue($on->isAllowed($curated));
    }

    /**
     * A restricted area stays refused however the tenant configures itself. The
     * row exists for UserAPI -- reads are legitimate -- but a write must not be
     * reachable even with the row and the switch both on.
     */
    public function testProductExclusionsCannotBeConfiguredAway(): void
    {
        $userApi = 'OrangeHRM\\Admin\\Api\\UserAPI';
        $gate = new ToolPolicyGate(
            $this->policy([ApiVerb::READ => true, ApiVerb::UPDATE => true]),
            $this->permissions([
                $userApi => $this->row([ApiVerb::READ => true, ApiVerb::UPDATE => true]),
            ])
        );

        $this->assertTrue($gate->isAllowed($this->genericTool('GET', $userApi)));
        $this->assertFalse(
            $gate->isAllowed($this->genericTool('PUT', $userApi)),
            'user writes are excluded in code and no configuration may re-admit them'
        );
    }

    public function testRefusalReasonNamesTheControlToChange(): void
    {
        $verbOff = new ToolPolicyGate(
            $this->policy([ApiVerb::READ => true]),
            $this->permissions([self::LOCATION_API => $this->row([ApiVerb::DELETE => true])])
        );
        $this->assertStringContainsString(
            'delete',
            $verbOff->refusalReason($this->genericTool('DELETE'))
        );

        $rowOff = new ToolPolicyGate(
            $this->policy([ApiVerb::READ => true]),
            $this->permissions([])
        );
        $this->assertStringContainsString(
            'rest_get_admin_locations',
            $rowOff->refusalReason($this->genericTool())
        );

        $restricted = new ToolPolicyGate(
            $this->policy([ApiVerb::UPDATE => true]),
            $this->permissions([])
        );
        $this->assertStringContainsString(
            'read-only through MCP',
            $restricted->refusalReason($this->genericTool('PUT', 'OrangeHRM\\Admin\\Api\\UserAPI'))
        );
    }
}
