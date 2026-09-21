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
    /**
     * Name of this plugin's row in `ohrm_module`. Declared here rather than in
     * McpEnabledTrait because constants in traits require PHP 8.2 and this
     * project's floor is PHP 7.4.
     */
    public const MODULE_NAME = 'mcp';

    public const KEY_AUDIT_RETENTION_DAYS = 'mcp.audit.retention_days';
    public const KEY_AUDIT_REDACTION_POLICY = 'mcp.audit.redaction_policy';

    public const DEFAULT_AUDIT_RETENTION_DAYS = 0;
    public const DEFAULT_AUDIT_REDACTION_POLICY = 'digest_only';

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

    private function coerceInt(?string $raw, int $default): int
    {
        if ($raw === null) {
            return $default;
        }
        return (int) $raw;
    }
}
