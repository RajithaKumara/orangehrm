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

use OrangeHRM\Mcp\Tool\RestrictedApis;
use PHPUnit\Framework\TestCase;

/**
 * Guards the product decision about which areas of OrangeHRM an AI assistant
 * may reach. These are not permission checks -- an administrator holds every
 * grant these endpoints need -- so nothing else in the system would stop them.
 *
 * @group Mcp
 * @group Tool
 */
class RestrictedApisTest extends TestCase
{
    /**
     * @dataProvider forbiddenAreaProvider
     */
    public function testForbiddenAreasAreUnreachableByAnyVerb(string $apiClass, string $area): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
            $this->assertFalse(
                RestrictedApis::isAllowed($apiClass, $method),
                sprintf('%s must not be reachable over MCP by %s', $area, $method)
            );
        }
    }

    public function forbiddenAreaProvider(): array
    {
        return [
            'Email Configuration' => ['OrangeHRM\\Admin\\Api\\EmailConfigurationAPI', 'Email Configuration'],
            'Social Media Auth' => [
                'OrangeHRM\\OpenidAuthentication\\Api\\ProviderAPI',
                'Social Media Authentication',
            ],
            'Register OAuth Client' => ['OrangeHRM\\OAuth\\Api\\OAuthClientAPI', 'Register OAuth Client'],
            'LDAP config' => ['OrangeHRM\\LDAP\\Api\\LDAPConfigAPI', 'LDAP Configuration'],
            'LDAP test' => ['OrangeHRM\\LDAP\\Api\\LDAPTestConnectionAPI', 'LDAP connection test'],
            'LDAP sync' => ['OrangeHRM\\LDAP\\Api\\LDAPUserSyncAPI', 'LDAP user sync'],
            'Workspace config' => [
                'OrangeHRM\\WorkspaceNotifications\\Api\\WorkspaceNotificationConfigAPI',
                'Workspace Notification Configuration',
            ],
            'Workspace registrations' => [
                'OrangeHRM\\WorkspaceNotifications\\Api\\WorkspaceNotificationRegistrationAPI',
                'Workspace Notification registrations',
            ],
            'Workspace webhook test' => [
                'OrangeHRM\\WorkspaceNotifications\\Api\\WorkspaceNotificationTestWebhookAPI',
                'Workspace Notification webhook test',
            ],
            'MCP Access' => ['OrangeHRM\\Mcp\\Api\\McpConfigAPI', 'MCP Access'],
            'Purge candidates' => ['OrangeHRM\\Maintenance\\Api\\PurgeCandidateAPI', 'Maintenance Purge'],
            'Purge employees' => ['OrangeHRM\\Maintenance\\Api\\PurgeEmployeeAPI', 'Maintenance Purge'],
        ];
    }

    /**
     * The user-account case is deliberately asymmetric. Listing users is
     * ordinary HR work; a write sets passwords and roles.
     */
    public function testUserAccountsAreReadableButNeverWritable(): void
    {
        $api = 'OrangeHRM\\Admin\\Api\\UserAPI';

        $this->assertTrue(RestrictedApis::isAllowed($api, 'GET'));
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $this->assertFalse(
                RestrictedApis::isAllowed($api, $method),
                sprintf('User accounts must not be writable over MCP (%s)', $method)
            );
        }
    }

    public function testOrdinaryEndpointsAreUnaffected(): void
    {
        foreach (['OrangeHRM\\Pim\\Api\\EmployeeAPI', 'OrangeHRM\\Leave\\Api\\LeaveTypeAPI'] as $api) {
            foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
                $this->assertTrue(
                    RestrictedApis::isAllowed($api, $method),
                    'The restriction list must not catch ordinary endpoints'
                );
            }
        }
    }

    /**
     * Callers may hold a leading-slash FQCN; the check must not depend on it.
     */
    public function testLeadingBackslashIsTolerated(): void
    {
        $this->assertFalse(RestrictedApis::isAllowed('\\OrangeHRM\\Mcp\\Api\\McpConfigAPI', 'GET'));
    }

    /**
     * The refusal must not describe what the endpoint does, or the refusal
     * itself becomes a way to enumerate the sensitive surface.
     */
    public function testRefusalReasonLeaksNothingAboutTheEndpoint(): void
    {
        $reason = RestrictedApis::refusalReason('OrangeHRM\\LDAP\\Api\\LDAPConfigAPI', 'GET');

        $this->assertNotSame('', $reason);
        foreach (['LDAP', 'password', 'credential', 'secret', 'bind'] as $leak) {
            $this->assertStringNotContainsStringIgnoringCase($leak, $reason);
        }
    }

    /**
     * Exclusion is keyed on the API class, so a new endpoint added to an
     * already-restricted class is excluded with no change to the list. That is
     * the property that makes this a standard rather than a one-off patch.
     */
    public function testExclusionIsKeyedOnApiClassNotOnRouteOrToolName(): void
    {
        $api = 'OrangeHRM\\LDAP\\Api\\LDAPConfigAPI';

        // A hypothetical future verb on the same class needs no list change.
        $this->assertFalse(RestrictedApis::isAllowed($api, 'PATCH'));
        $this->assertTrue(RestrictedApis::isForbidden($api));
    }
}
