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
use OrangeHRM\Mcp\Service\McpConfigPolicy;
use OrangeHRM\Mcp\Tool\ApiVerb;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Service
 */
class McpConfigPolicyTest extends KernelTestCase
{
    /**
     * @param array<string,string> $values
     */
    private function policyWith(array $values): McpConfigPolicy
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

        return new McpConfigPolicy();
    }

    /**
     * A misconfigured or freshly installed instance must expose nothing. Every
     * default here fails closed on purpose.
     */
    public function testDefaultsFailClosed(): void
    {
        $policy = $this->policyWith([]);

        $this->assertFalse($policy->isEnabled());
        $this->assertTrue($policy->isReadOnly());
        $this->assertSame([], $policy->getAllowedRoleIds());
        $this->assertFalse($policy->mayConnect(1, 1), 'disabled instance must refuse everyone');
    }

    /**
     * Reads on, writes off, for an instance that has never been configured. The
     * migration writes the same values; this is what happens if it did not.
     */
    public function testVerbSwitchesDefaultToReadOnly(): void
    {
        $policy = $this->policyWith([]);

        $this->assertTrue($policy->isVerbEnabled(ApiVerb::READ));
        $this->assertFalse($policy->isVerbEnabled(ApiVerb::CREATE));
        $this->assertFalse($policy->isVerbEnabled(ApiVerb::UPDATE));
        $this->assertFalse($policy->isVerbEnabled(ApiVerb::DELETE));
    }

    public function testVerbSwitchesAreReadIndependently(): void
    {
        $policy = $this->policyWith([
            McpConfigPolicy::KEY_VERB_PREFIX . ApiVerb::READ => '1',
            McpConfigPolicy::KEY_VERB_PREFIX . ApiVerb::CREATE => '1',
            McpConfigPolicy::KEY_VERB_PREFIX . ApiVerb::UPDATE => '0',
            McpConfigPolicy::KEY_VERB_PREFIX . ApiVerb::DELETE => '0',
        ]);

        $this->assertSame(
            [
                ApiVerb::READ => true,
                ApiVerb::CREATE => true,
                ApiVerb::UPDATE => false,
                ApiVerb::DELETE => false,
            ],
            $policy->getVerbSwitches()
        );
        // One write verb on is enough to leave read-only.
        $this->assertFalse($policy->isReadOnly());
    }

    public function testRoleGrantsAccess(): void
    {
        $policy = $this->policyWith([
            McpConfigPolicy::KEY_ENABLED => '1',
            McpConfigPolicy::KEY_ALLOWED_ROLE_IDS => '[1,3]',
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
            McpConfigPolicy::KEY_ENABLED => '1',
            McpConfigPolicy::KEY_ALLOWED_ROLE_IDS => '[1]',
            McpConfigPolicy::KEY_USER_OVERRIDES => '{"7":false,"9":true}',
        ]);

        $this->assertFalse($policy->mayConnect(7, 1), 'explicit deny beats an allowed role');
        $this->assertTrue($policy->mayConnect(9, 2), 'explicit allow beats a disallowed role');
        $this->assertTrue($policy->mayConnect(8, 1), 'users without an override follow their role');
    }

    public function testOverridesCannotReviveADisabledInstance(): void
    {
        $policy = $this->policyWith([
            McpConfigPolicy::KEY_ENABLED => '0',
            McpConfigPolicy::KEY_ALLOWED_ROLE_IDS => '[1]',
            McpConfigPolicy::KEY_USER_OVERRIDES => '{"9":true}',
        ]);

        $this->assertFalse($policy->mayConnect(9, 1), 'the master switch wins over any override');
    }

    public function testMalformedJsonDegradesToEmptyRatherThanThrowing(): void
    {
        $policy = $this->policyWith([
            McpConfigPolicy::KEY_ENABLED => '1',
            McpConfigPolicy::KEY_ALLOWED_ROLE_IDS => 'not json',
            McpConfigPolicy::KEY_USER_OVERRIDES => 'also not json',
        ]);

        $this->assertSame([], $policy->getAllowedRoleIds());
        $this->assertSame([], $policy->getUserOverrides());
        $this->assertFalse($policy->mayConnect(1, 1), 'a corrupt config must fail closed');
    }

    /**
     * Read-only is derived from the three write switches rather than stored
     * separately, so it cannot drift out of step with them. Each write verb on
     * its own is enough to leave read-only.
     */
    public function testReadOnlyIsDerivedFromTheWriteSwitches(): void
    {
        $this->assertTrue($this->policyWith([])->isReadOnly());

        foreach ([ApiVerb::CREATE, ApiVerb::UPDATE, ApiVerb::DELETE] as $verb) {
            $this->assertFalse(
                $this->policyWith([McpConfigPolicy::KEY_VERB_PREFIX . $verb => '1'])->isReadOnly(),
                sprintf('%s switched on must leave read-only mode', $verb)
            );
        }

        // Read on its own changes nothing: reading is not writing.
        $this->assertTrue(
            $this->policyWith([McpConfigPolicy::KEY_VERB_PREFIX . ApiVerb::READ => '1'])->isReadOnly()
        );
    }

    public function testNullUserIdCannotConnect(): void
    {
        $policy = $this->policyWith([
            McpConfigPolicy::KEY_ENABLED => '1',
            McpConfigPolicy::KEY_ALLOWED_ROLE_IDS => '[1]',
        ]);

        $this->assertFalse($policy->mayConnect(null, 1));
    }

    /**
     * Any registered OAuth client may be used, with one exception that is a
     * rule rather than a setting.
     *
     * The mobile app's registration is seeded by the product and cannot be
     * updated or deleted, so its redirect URI can never be pointed at an
     * assistant -- there is no configuration in which a token issued to it
     * belongs on this surface.
     */
    public function testOnlyTheMobileAppIsRefused(): void
    {
        $policy = $this->policyWith([]);

        $this->assertFalse($policy->isClientApproved('orangehrm_mobile_app'));
        $this->assertTrue($policy->isClientApproved('claude-code-id'));
        $this->assertTrue($policy->isClientApproved('any-other-registered-client'));
    }
}
