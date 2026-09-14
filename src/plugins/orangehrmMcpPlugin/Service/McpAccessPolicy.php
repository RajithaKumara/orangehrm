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

namespace OrangeHRM\Mcp\Service;

use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;

/**
 * Who may reach the MCP surface, in what mode, through which client, and with
 * which tools.
 *
 * This is the organisation's control panel over AI access, and it is deliberately
 * separate from the per-call RBAC that `RestDispatcher` enforces. The two answer
 * different questions:
 *
 *   - RestDispatcher: "may this user read this endpoint?" (OrangeHRM's own model)
 *   - McpAccessPolicy: "should this user be reaching OrangeHRM through an AI
 *     assistant at all?" (the organisation's policy about the channel)
 *
 * A user must pass both. Policy narrows access; it can never widen it, because
 * the per-call RBAC check still runs afterwards regardless of what is configured
 * here.
 *
 * Storage is `mcp.*` config values holding JSON, so no schema change is needed
 * and the existing configuration mechanism stays the single source of truth
 * (FR41). The read/write split of this class is narrow enough that moving to
 * dedicated tables later would not ripple outwards.
 */
class McpAccessPolicy
{
    use ConfigServiceTrait;

    public const KEY_ENABLED = 'mcp.access.enabled';
    public const KEY_MODE = 'mcp.access.mode';
    public const KEY_ALLOWED_ROLE_IDS = 'mcp.access.allowed_role_ids';
    public const KEY_USER_OVERRIDES = 'mcp.access.user_overrides';
    public const KEY_ENABLED_TOOLS = 'mcp.access.enabled_tools';
    public const KEY_APPROVED_CLIENT_IDS = 'mcp.access.approved_client_ids';

    public const MODE_READ_ONLY = 'read_only';
    public const MODE_READ_WRITE = 'read_write';

    /**
     * Off until an administrator turns it on. Enabling MCP is the deliberate act
     * that the "before you enable this" advisory is written about.
     */
    public const DEFAULT_ENABLED = false;

    /**
     * Any tool not named in the enabled list is unavailable. An empty list means
     * "nothing enabled", not "everything enabled" -- a misconfiguration should
     * fail closed.
     */
    public const DEFAULT_ENABLED_TOOLS = [];

    /**
     * Empty means no role may connect. Same fail-closed reasoning.
     */
    public const DEFAULT_ALLOWED_ROLE_IDS = [];

    /**
     * Empty means every approved-client check fails, which would lock everyone
     * out of a freshly enabled instance. Approved clients are therefore only
     * enforced once at least one client has been approved; see isClientApproved().
     */
    public const DEFAULT_APPROVED_CLIENT_IDS = [];

    public function isEnabled(): bool
    {
        $raw = $this->getConfigService()->getConfigDao()->getValue(self::KEY_ENABLED);
        if ($raw === null || $raw === '') {
            return self::DEFAULT_ENABLED;
        }
        return in_array(strtolower($raw), ['1', 'true', 'yes', 'on'], true);
    }

    public function getMode(): string
    {
        $raw = $this->getConfigService()->getConfigDao()->getValue(self::KEY_MODE);
        return $raw === self::MODE_READ_WRITE ? self::MODE_READ_WRITE : self::MODE_READ_ONLY;
    }

    public function isReadOnly(): bool
    {
        return $this->getMode() === self::MODE_READ_ONLY;
    }

    /**
     * @return int[] user role ids permitted to use MCP
     */
    public function getAllowedRoleIds(): array
    {
        return array_values(array_map('intval', $this->decodeList(self::KEY_ALLOWED_ROLE_IDS)));
    }

    /**
     * Per-user exceptions on top of the role default, keyed by user id.
     * true grants access to a user whose role would not; false removes access
     * from a user whose role would grant it.
     *
     * @return array<int,bool>
     */
    public function getUserOverrides(): array
    {
        $raw = $this->getConfigService()->getConfigDao()->getValue(self::KEY_USER_OVERRIDES);
        if ($raw === null || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $overrides = [];
        foreach ($decoded as $userId => $allowed) {
            $overrides[(int) $userId] = (bool) $allowed;
        }
        return $overrides;
    }

    /**
     * @return string[] tool names an administrator has switched on
     */
    public function getEnabledTools(): array
    {
        return array_values(array_filter(
            array_map('strval', $this->decodeList(self::KEY_ENABLED_TOOLS)),
            static function (string $name): bool {
                return $name !== '';
            }
        ));
    }

    /**
     * @return string[] OAuth client ids approved to reach the MCP endpoint
     */
    public function getApprovedClientIds(): array
    {
        return array_values(array_filter(
            array_map('strval', $this->decodeList(self::KEY_APPROVED_CLIENT_IDS)),
            static function (string $id): bool {
                return $id !== '';
            }
        ));
    }

    /**
     * Role grants the baseline; a per-user override wins in either direction.
     *
     * An explicit deny beats the role because that is the lever an administrator
     * reaches for in an incident: revoke one person without disturbing everyone
     * who shares their role.
     */
    public function mayConnect(?int $userId, ?int $userRoleId): bool
    {
        if (!$this->isEnabled() || $userId === null) {
            return false;
        }

        $overrides = $this->getUserOverrides();
        if (array_key_exists($userId, $overrides)) {
            return $overrides[$userId];
        }

        if ($userRoleId === null) {
            return false;
        }

        return in_array($userRoleId, $this->getAllowedRoleIds(), true);
    }

    public function isToolEnabled(string $toolName): bool
    {
        return in_array($toolName, $this->getEnabledTools(), true);
    }

    /**
     * Closes the token-audience gap (README 4.13): a token minted for some other
     * OrangeHRM application must not be usable against the AI surface.
     *
     * Enforced only once at least one client has been approved. An empty list on
     * a freshly enabled instance would otherwise lock out the very administrator
     * trying to configure it, and the connection gate above still applies.
     */
    public function isClientApproved(?string $clientId): bool
    {
        $approved = $this->getApprovedClientIds();
        if ($approved === []) {
            return true;
        }
        if ($clientId === null || $clientId === '') {
            return false;
        }
        return in_array($clientId, $approved, true);
    }

    public function isClientApprovalEnforced(): bool
    {
        return $this->getApprovedClientIds() !== [];
    }

    public function setEnabled(bool $enabled): void
    {
        $this->setValue(self::KEY_ENABLED, $enabled ? '1' : '0');
    }

    public function setMode(string $mode): void
    {
        $this->setValue(
            self::KEY_MODE,
            $mode === self::MODE_READ_WRITE ? self::MODE_READ_WRITE : self::MODE_READ_ONLY
        );
    }

    /**
     * @param int[] $roleIds
     */
    public function setAllowedRoleIds(array $roleIds): void
    {
        $clean = array_values(array_unique(array_map('intval', $roleIds)));
        $this->setValue(self::KEY_ALLOWED_ROLE_IDS, (string) json_encode($clean));
    }

    /**
     * @param array<int|string,bool> $overrides
     */
    public function setUserOverrides(array $overrides): void
    {
        $clean = [];
        foreach ($overrides as $userId => $allowed) {
            $clean[(string) (int) $userId] = (bool) $allowed;
        }
        $this->setValue(self::KEY_USER_OVERRIDES, (string) json_encode((object) $clean));
    }

    /**
     * @param string[] $toolNames
     */
    public function setEnabledTools(array $toolNames): void
    {
        $clean = array_values(array_unique(array_map('strval', $toolNames)));
        $this->setValue(self::KEY_ENABLED_TOOLS, (string) json_encode($clean));
    }

    /**
     * @param string[] $clientIds
     */
    public function setApprovedClientIds(array $clientIds): void
    {
        $clean = array_values(array_unique(array_map('strval', $clientIds)));
        $this->setValue(self::KEY_APPROVED_CLIENT_IDS, (string) json_encode($clean));
    }

    /**
     * @return array<int,mixed>
     */
    private function decodeList(string $key): array
    {
        $raw = $this->getConfigService()->getConfigDao()->getValue($key);
        if ($raw === null || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function setValue(string $key, string $value): void
    {
        $this->getConfigService()->getConfigDao()->setValue($key, $value);
    }
}
