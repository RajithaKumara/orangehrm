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
use OrangeHRM\Mcp\Tool\ApiVerb;
use OrangeHRM\OAuth\Service\OAuthService;

/**
 * Who may reach the MCP surface, through which client, and which of the four
 * verbs the surface offers at all.
 *
 * This is the organisation's control panel over AI access, deliberately separate
 * from the per-call RBAC that `RestDispatcher` enforces. The two answer different
 * questions:
 *
 *   - RestDispatcher: "may this user read this endpoint?" (OrangeHRM's own model)
 *   - McpConfigPolicy: "should this user be reaching OrangeHRM through an AI
 *     assistant at all, and may the assistant do this kind of thing?"
 *
 * A call must pass both. Policy narrows access and can never widen it, because
 * the per-call RBAC check still runs afterwards regardless of what is set here.
 *
 * The four verb switches are the top-level control. They sit above the per-class
 * rows in `ohrm_mcp_api_permission`: turning Delete off refuses every delete tool
 * in one move, whatever any individual row says, which is the lever an
 * administrator wants during an incident. Turning it back on restores exactly the
 * rows that were already enabled, so the switch is a circuit breaker rather than
 * a setting whose state has to be rebuilt afterwards.
 *
 * Storage is `mcp.config.*` config values, so the existing configuration
 * mechanism stays the single source of truth for settings (FR41). The per-class
 * grid is a table instead, because a few hundred rows of four booleans is data,
 * not a setting.
 */
class McpConfigPolicy
{
    use ConfigServiceTrait;

    public const KEY_ENABLED = 'mcp.config.enabled';
    public const KEY_ALLOWED_ROLE_IDS = 'mcp.config.allowed_role_ids';
    /**
     * One switch per verb, named so the stored keys read the same way the screen
     * does.
     */
    public const KEY_VERB_PREFIX = 'mcp.config.verb.';

    /**
     * Off until an administrator turns it on. Enabling MCP is the deliberate act
     * that the "before you enable this" advisory is written about.
     */
    public const DEFAULT_ENABLED = false;

    /**
     * Reads are the point of the feature; writes are not. An instance whose
     * switches were never written behaves as read-only, and the migration writes
     * exactly this.
     */
    public const DEFAULT_VERB_SWITCHES = [
        ApiVerb::READ => true,
        ApiVerb::CREATE => false,
        ApiVerb::UPDATE => false,
        ApiVerb::DELETE => false,
    ];

    /**
     * Empty means no role may connect: an instance whose config row was never
     * written fails closed.
     *
     * What ships is not this. The migration seeds both assignable roles, which
     * is safe because the master switch is off -- see seedAllowedRoles(). This
     * constant only covers the case where that row is missing entirely.
     */
    public const DEFAULT_ALLOWED_ROLE_IDS = [];

    public function isEnabled(): bool
    {
        $raw = $this->getConfigService()->getConfigDao()->getValue(self::KEY_ENABLED);
        if ($raw === null || $raw === '') {
            return self::DEFAULT_ENABLED;
        }
        return in_array(strtolower($raw), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @return array<string,bool> keyed by ApiVerb constant
     */
    public function getVerbSwitches(): array
    {
        $switches = [];
        foreach (ApiVerb::all() as $verb) {
            $switches[$verb] = $this->isVerbEnabled($verb);
        }
        return $switches;
    }

    public function isVerbEnabled(string $verb): bool
    {
        $raw = $this->getConfigService()->getConfigDao()->getValue(self::KEY_VERB_PREFIX . $verb);
        if ($raw === null || $raw === '') {
            return self::DEFAULT_VERB_SWITCHES[$verb] ?? false;
        }
        return in_array(strtolower($raw), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @param array<string,bool> $switches partial is fine; unnamed verbs are left alone
     */
    public function setVerbSwitches(array $switches): void
    {
        foreach (ApiVerb::all() as $verb) {
            if (!array_key_exists($verb, $switches)) {
                continue;
            }
            $this->setValue(self::KEY_VERB_PREFIX . $verb, $switches[$verb] ? '1' : '0');
        }
    }

    /**
     * True when no verb that changes data is switched on.
     *
     * Derived rather than stored. A separate read-only flag alongside these
     * switches would be a second control over one fact, and the two would
     * eventually disagree.
     */
    public function isReadOnly(): bool
    {
        return !$this->isVerbEnabled(ApiVerb::CREATE)
            && !$this->isVerbEnabled(ApiVerb::UPDATE)
            && !$this->isVerbEnabled(ApiVerb::DELETE);
    }

    /**
     * @return int[] user role ids permitted to use MCP
     */
    public function getAllowedRoleIds(): array
    {
        return array_values(array_map('intval', $this->decodeList(self::KEY_ALLOWED_ROLE_IDS)));
    }

    /**
     * Whether this person may reach the AI surface at all.
     *
     * Role alone decides it. There were per-user overrides here, allowing or
     * denying one individual against their role; they were dropped because no
     * screen could add or remove one, so the only way to create an override was
     * SQL -- and an override nobody can see is a decision nobody can audit.
     *
     * The incident lever that leaves is coarser: revoking a role affects
     * everyone who shares it. Worth revisiting if the screen ever grows a user
     * picker, and worth knowing in the meantime.
     */
    public function mayConnect(?int $userId, ?int $userRoleId): bool
    {
        if (!$this->isEnabled() || $userId === null || $userRoleId === null) {
            return false;
        }

        return in_array($userRoleId, $this->getAllowedRoleIds(), true);
    }

    /**
     * Whether a token issued to this OAuth client may be used on the AI
     * surface.
     *
     * Any registered client may, with one exception. The mobile app is seeded
     * by the product and OAuthClientAPI refuses to update or delete it, so its
     * redirect URI can never be pointed at an assistant -- there is no
     * configuration in which a token issued to it belongs here, which is why
     * this is a rule in code rather than something a tenant can set.
     *
     * There was an approved-client list here. It was dropped: an administrator
     * registering an OAuth client for an assistant has already made the
     * decision, and asking them to repeat it in a second place that no screen
     * exposes only created a way to lock everybody out with a typo.
     */
    public function isClientApproved(?string $clientId): bool
    {
        return $clientId !== OAuthService::PUBLIC_MOBILE_CLIENT_ID;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->setValue(self::KEY_ENABLED, $enabled ? '1' : '0');
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
