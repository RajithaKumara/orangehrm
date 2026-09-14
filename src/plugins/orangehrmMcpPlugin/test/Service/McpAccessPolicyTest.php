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

namespace OrangeHRM\Tests\Mcp\Service;

use OrangeHRM\Core\Dao\ConfigDao;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Framework\Services;
use OrangeHRM\Mcp\Service\McpAccessPolicy;
use OrangeHRM\Mcp\Tool\DefaultTools;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Service
 */
class McpAccessPolicyTest extends KernelTestCase
{
    /**
     * @param array<string,string> $values
     */
    private function policyWith(array $values): McpAccessPolicy
    {
        $dao = $this->getMockBuilder(ConfigDao::class)->disableOriginalConstructor()->getMock();
        $dao->method('getValue')->willReturnCallback(
            static function (string $key) use ($values): ?string {
                return $values[$key] ?? null;
            }
        );

        $configService = $this->getMockBuilder(ConfigService::class)->getMock();
        $configService->method('getConfigDao')->willReturn($dao);

        $this->createKernelWithMockServices([Services::CONFIG_SERVICE => $configService]);

        return new McpAccessPolicy();
    }

    /**
     * A misconfigured or freshly installed instance must expose nothing. Every
     * default here fails closed on purpose.
     */
    public function testDefaultsFailClosed(): void
    {
        $policy = $this->policyWith([]);

        $this->assertFalse($policy->isEnabled());
        $this->assertSame(McpAccessPolicy::MODE_READ_ONLY, $policy->getMode());
        $this->assertTrue($policy->isReadOnly());
        $this->assertSame([], $policy->getAllowedRoleIds());
        $this->assertFalse($policy->mayConnect(1, 1), 'disabled instance must refuse everyone');
    }

    public function testModeOnlyAcceptsTheTwoKnownValues(): void
    {
        $this->assertSame(
            McpAccessPolicy::MODE_READ_WRITE,
            $this->policyWith([McpAccessPolicy::KEY_MODE => 'read_write'])->getMode()
        );
        // Anything unrecognised degrades to the safe mode rather than throwing.
        $this->assertSame(
            McpAccessPolicy::MODE_READ_ONLY,
            $this->policyWith([McpAccessPolicy::KEY_MODE => 'anything_else'])->getMode()
        );
    }

    public function testRoleGrantsAccess(): void
    {
        $policy = $this->policyWith([
            McpAccessPolicy::KEY_ENABLED => '1',
            McpAccessPolicy::KEY_ALLOWED_ROLE_IDS => '[1,3]',
        ]);

        $this->assertTrue($policy->mayConnect(10, 1), 'Admin role allowed');
        $this->assertTrue($policy->mayConnect(11, 3), 'Supervisor role allowed');
        $this->assertFalse($policy->mayConnect(12, 2), 'ESS role not in the list');
    }

    /**
     * The deny override is the lever an administrator reaches for in an incident:
     * cut off one person without disturbing everyone who shares their role.
     */
    public function testUserOverrideBeatsTheRoleInBothDirections(): void
    {
        $policy = $this->policyWith([
            McpAccessPolicy::KEY_ENABLED => '1',
            McpAccessPolicy::KEY_ALLOWED_ROLE_IDS => '[1]',
            McpAccessPolicy::KEY_USER_OVERRIDES => '{"7":false,"9":true}',
        ]);

        $this->assertFalse($policy->mayConnect(7, 1), 'explicit deny beats an allowed role');
        $this->assertTrue($policy->mayConnect(9, 2), 'explicit allow beats a disallowed role');
        $this->assertTrue($policy->mayConnect(8, 1), 'users without an override follow their role');
    }

    public function testOverridesCannotReviveADisabledInstance(): void
    {
        $policy = $this->policyWith([
            McpAccessPolicy::KEY_ENABLED => '0',
            McpAccessPolicy::KEY_ALLOWED_ROLE_IDS => '[1]',
            McpAccessPolicy::KEY_USER_OVERRIDES => '{"9":true}',
        ]);

        $this->assertFalse($policy->mayConnect(9, 1), 'the master switch wins over any override');
    }

    public function testToolsMustBeExplicitlyEnabled(): void
    {
        $policy = $this->policyWith([
            McpAccessPolicy::KEY_ENABLED_TOOLS => '["mcp_get_current_employee"]',
        ]);

        $this->assertTrue($policy->isToolEnabled('mcp_get_current_employee'));
        $this->assertFalse($policy->isToolEnabled('mcp_list_job_titles'));
    }

    /**
     * An unset value means "never configured", so the shipped defaults apply --
     * otherwise a fresh instance would expose nothing and the feature would look
     * broken. A value that *is* set, including an empty list, is the
     * administrator's decision and must be honoured exactly.
     */
    public function testUnsetToolListFallsBackToDefaultsButAnEmptyListDoesNot(): void
    {
        $unconfigured = $this->policyWith([]);
        $this->assertSame(DefaultTools::all(), $unconfigured->getEnabledTools());
        $this->assertTrue($unconfigured->isToolEnabled('mcp_get_current_employee'));

        $clearedByAdmin = $this->policyWith([McpAccessPolicy::KEY_ENABLED_TOOLS => '[]']);
        $this->assertSame([], $clearedByAdmin->getEnabledTools());
        $this->assertFalse(
            $clearedByAdmin->isToolEnabled('mcp_get_current_employee'),
            'an administrator who cleared the list must not have defaults reinstated'
        );
    }

    /**
     * Defaults are reads only. A write is enabled deliberately or not at all.
     */
    public function testNoWriteToolIsEnabledByDefault(): void
    {
        foreach (DefaultTools::all() as $name) {
            $this->assertStringStartsNotWith('rest_post_', $name);
            $this->assertStringStartsNotWith('rest_put_', $name);
            $this->assertStringStartsNotWith('rest_delete_', $name);
            $this->assertStringStartsNotWith('rest_patch_', $name);
        }
    }

    /**
     * Closes README 4.13. Verified against the running instance: before this,
     * a token minted for the mobile-app client was accepted on /api/mcp.
     */
    public function testApprovedClientListGatesTheAudience(): void
    {
        $policy = $this->policyWith([
            McpAccessPolicy::KEY_APPROVED_CLIENT_IDS => '["claude-code-id"]',
        ]);

        $this->assertTrue($policy->isClientApprovalEnforced());
        $this->assertTrue($policy->isClientApproved('claude-code-id'));
        $this->assertFalse($policy->isClientApproved('orangehrm_mobile_app'));
        $this->assertFalse($policy->isClientApproved(null), 'an unresolvable client is not approved');
    }

    /**
     * An empty approved list must not lock out the administrator who is still
     * configuring the instance; the connection gate already applies.
     */
    public function testEmptyApprovedListDoesNotEnforceAudience(): void
    {
        $policy = $this->policyWith([]);

        $this->assertFalse($policy->isClientApprovalEnforced());
        $this->assertTrue($policy->isClientApproved('anything'));
        $this->assertTrue($policy->isClientApproved(null));
    }

    public function testMalformedJsonDegradesToEmptyRatherThanThrowing(): void
    {
        $policy = $this->policyWith([
            McpAccessPolicy::KEY_ENABLED => '1',
            McpAccessPolicy::KEY_ALLOWED_ROLE_IDS => 'not json',
            McpAccessPolicy::KEY_ENABLED_TOOLS => '{"not":"a list"}',
            McpAccessPolicy::KEY_USER_OVERRIDES => 'also not json',
        ]);

        $this->assertSame([], $policy->getAllowedRoleIds());
        $this->assertSame([], $policy->getUserOverrides());
        $this->assertFalse($policy->mayConnect(1, 1), 'a corrupt config must fail closed');
    }

    /**
     * Read-only mode is the circuit breaker described in the PRD: it must refuse
     * writes regardless of the caller's own permissions, so it is checked
     * independently of RBAC.
     */
    public function testReadOnlyIsTheDefaultAndReadWriteIsOptIn(): void
    {
        $this->assertTrue($this->policyWith([])->isReadOnly());
        $this->assertTrue(
            $this->policyWith([McpAccessPolicy::KEY_MODE => 'read_only'])->isReadOnly()
        );
        $this->assertFalse(
            $this->policyWith([McpAccessPolicy::KEY_MODE => McpAccessPolicy::MODE_READ_WRITE])->isReadOnly()
        );
    }

    public function testNullUserIdCannotConnect(): void
    {
        $policy = $this->policyWith([
            McpAccessPolicy::KEY_ENABLED => '1',
            McpAccessPolicy::KEY_ALLOWED_ROLE_IDS => '[1]',
        ]);

        $this->assertFalse($policy->mayConnect(null, 1));
    }
}
