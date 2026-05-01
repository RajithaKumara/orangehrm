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

use OrangeHRM\Core\Service\ConfigService;

class McpConfigurationService extends ConfigService
{
    public const KEY_READ_ONLY_MODE = 'mcp.read_only_mode';
    public const KEY_RATE_LIMIT_PER_SESSION_PER_MINUTE = 'mcp.rate_limit.per_session_per_minute';
    public const KEY_RATE_LIMIT_PER_USER_PER_MINUTE = 'mcp.rate_limit.per_user_per_minute';
    public const KEY_DAILY_CALL_CAP_ENABLED = 'mcp.daily_call_cap.enabled';
    public const KEY_DAILY_CALL_CAP_VALUE = 'mcp.daily_call_cap.value';
    public const KEY_CONCURRENT_SESSIONS_PER_USER = 'mcp.concurrent_sessions_per_user';
    public const KEY_AUDIT_RETENTION_DAYS = 'mcp.audit.retention_days';
    public const KEY_AUDIT_REDACTION_POLICY = 'mcp.audit.redaction_policy';

    public const DEFAULT_READ_ONLY_MODE = true;
    public const DEFAULT_RATE_LIMIT_PER_SESSION_PER_MINUTE = 60;
    public const DEFAULT_RATE_LIMIT_PER_USER_PER_MINUTE = 120;
    public const DEFAULT_DAILY_CALL_CAP_ENABLED = false;
    public const DEFAULT_DAILY_CALL_CAP_VALUE = 0;
    public const DEFAULT_CONCURRENT_SESSIONS_PER_USER = 5;
    public const DEFAULT_AUDIT_RETENTION_DAYS = 0;
    public const DEFAULT_AUDIT_REDACTION_POLICY = 'digest_only';

    public function isReadOnlyMode(): bool
    {
        return $this->coerceBool(
            $this->_getConfigValue(self::KEY_READ_ONLY_MODE),
            self::DEFAULT_READ_ONLY_MODE
        );
    }

    public function getRateLimitPerSessionPerMinute(): int
    {
        return $this->coerceInt(
            $this->_getConfigValue(self::KEY_RATE_LIMIT_PER_SESSION_PER_MINUTE),
            self::DEFAULT_RATE_LIMIT_PER_SESSION_PER_MINUTE
        );
    }

    public function getRateLimitPerUserPerMinute(): int
    {
        return $this->coerceInt(
            $this->_getConfigValue(self::KEY_RATE_LIMIT_PER_USER_PER_MINUTE),
            self::DEFAULT_RATE_LIMIT_PER_USER_PER_MINUTE
        );
    }

    public function isDailyCallCapEnabled(): bool
    {
        return $this->coerceBool(
            $this->_getConfigValue(self::KEY_DAILY_CALL_CAP_ENABLED),
            self::DEFAULT_DAILY_CALL_CAP_ENABLED
        );
    }

    public function getDailyCallCapValue(): int
    {
        return $this->coerceInt(
            $this->_getConfigValue(self::KEY_DAILY_CALL_CAP_VALUE),
            self::DEFAULT_DAILY_CALL_CAP_VALUE
        );
    }

    public function getConcurrentSessionsPerUser(): int
    {
        return $this->coerceInt(
            $this->_getConfigValue(self::KEY_CONCURRENT_SESSIONS_PER_USER),
            self::DEFAULT_CONCURRENT_SESSIONS_PER_USER
        );
    }

    public function getAuditRetentionDays(): int
    {
        return $this->coerceInt(
            $this->_getConfigValue(self::KEY_AUDIT_RETENTION_DAYS),
            self::DEFAULT_AUDIT_RETENTION_DAYS
        );
    }

    public function getAuditRedactionPolicy(): string
    {
        $raw = $this->_getConfigValue(self::KEY_AUDIT_REDACTION_POLICY);
        return $raw === null ? self::DEFAULT_AUDIT_REDACTION_POLICY : $raw;
    }

    private function coerceBool(?string $raw, bool $default): bool
    {
        if ($raw === null) {
            return $default;
        }
        return in_array(strtolower($raw), ['1', 'true', 'yes'], true);
    }

    private function coerceInt(?string $raw, int $default): int
    {
        if ($raw === null) {
            return $default;
        }
        return (int) $raw;
    }
}
