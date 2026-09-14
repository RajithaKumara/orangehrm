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

/**
 * API classes that must never be reachable through MCP, or must never be
 * writable through it.
 *
 * This is a product decision, not a permission check. An administrator holds
 * every grant these endpoints require, so RBAC alone would happily expose them.
 * These areas are excluded because reaching them through an AI assistant is a
 * different risk from reaching them through the admin screens:
 *
 *  - They hold or set credentials and connection secrets. Even a read can leak
 *    a host, a bind DN, a client id, or the shape of an integration. Several
 *    return fields that are encrypted at rest precisely because they are
 *    sensitive.
 *  - They configure authentication itself. A write could redirect sign-in to an
 *    attacker-controlled identity provider, or mint an OAuth client, which is a
 *    privilege-escalation path that does not look like one.
 *  - MCP Access is excluded from its own surface deliberately. An assistant that
 *    can widen its own access is not bounded by the policy at all.
 *  - Purge destroys employee data irreversibly under GDPR workflows. There is no
 *    version of "the assistant misunderstood" that is acceptable there.
 *
 * The user-account case is deliberately asymmetric: reading the user list is
 * ordinary HR work, but a write can set a password or change a role, so writes
 * are refused while reads are allowed.
 *
 * **This list is the standard.** A new endpoint in one of these areas is
 * excluded automatically because exclusion is keyed on the API class, not on a
 * tool name or a route. Adding a write endpoint to an existing restricted class
 * requires no change here. Adding a genuinely new restricted area does, and that
 * is the moment to think about it.
 */
final class RestrictedApis
{
    /**
     * Fully excluded: neither read nor write reaches MCP.
     */
    public const FORBIDDEN = [
        // Admin > Configuration > Email Configuration. Holds SMTP host,
        // credentials and an encrypted password.
        'OrangeHRM\\Admin\\Api\\EmailConfigurationAPI',

        // Admin > Configuration > Social Media Authentication. Configures the
        // OIDC providers sign-in trusts; a write is an authentication bypass.
        'OrangeHRM\\OpenidAuthentication\\Api\\ProviderAPI',

        // Admin > Configuration > Register OAuth Client. Minting a client is a
        // route to issuing tokens, including tokens for MCP itself.
        'OrangeHRM\\OAuth\\Api\\OAuthClientAPI',

        // Admin > Configuration > LDAP Configuration, plus its connection test
        // and user sync. Directory host, bind credentials, and a sync that can
        // create or deactivate user accounts wholesale.
        'OrangeHRM\\LDAP\\Api\\LDAPConfigAPI',
        'OrangeHRM\\LDAP\\Api\\LDAPTestConnectionAPI',
        'OrangeHRM\\LDAP\\Api\\LDAPUserSyncAPI',

        // Admin > Configuration > Workspace Notification Configuration. Holds
        // outbound webhook URLs, which are themselves secrets.
        'OrangeHRM\\WorkspaceNotifications\\Api\\WorkspaceNotificationConfigAPI',
        'OrangeHRM\\WorkspaceNotifications\\Api\\WorkspaceNotificationRegistrationAPI',
        'OrangeHRM\\WorkspaceNotifications\\Api\\WorkspaceNotificationTestWebhookAPI',

        // Admin > Configuration > MCP Access. The assistant must not be able to
        // read or rewrite the policy that bounds it.
        'OrangeHRM\\Mcp\\Api\\McpAccessConfigAPI',

        // Maintenance > Purge. Irreversible destruction of employee records.
        'OrangeHRM\\Maintenance\\Api\\PurgeCandidateAPI',
        'OrangeHRM\\Maintenance\\Api\\PurgeEmployeeAPI',
    ];

    /**
     * Readable through MCP, but never writable.
     */
    public const READ_ONLY = [
        // Admin > User Management > Users. Listing users is ordinary HR work.
        // A write sets passwords and roles, so it stays out.
        'OrangeHRM\\Admin\\Api\\UserAPI',
    ];

    public static function isForbidden(string $apiClass): bool
    {
        return in_array(ltrim($apiClass, '\\'), self::FORBIDDEN, true);
    }

    public static function isReadOnly(string $apiClass): bool
    {
        return in_array(ltrim($apiClass, '\\'), self::READ_ONLY, true);
    }

    /**
     * The single question every caller should ask.
     */
    public static function isAllowed(string $apiClass, string $method): bool
    {
        if (self::isForbidden($apiClass)) {
            return false;
        }
        if (strtoupper($method) !== 'GET' && self::isReadOnly($apiClass)) {
            return false;
        }
        return true;
    }

    /**
     * Why it was refused, for an error a caller can act on. Deliberately does
     * not name the endpoint's purpose, so the refusal itself leaks nothing.
     */
    public static function refusalReason(string $apiClass, string $method): string
    {
        if (self::isForbidden($apiClass)) {
            return 'This area of OrangeHRM is not available through MCP';
        }
        if (strtoupper($method) !== 'GET' && self::isReadOnly($apiClass)) {
            return 'This area of OrangeHRM is read-only through MCP';
        }
        return '';
    }
}
