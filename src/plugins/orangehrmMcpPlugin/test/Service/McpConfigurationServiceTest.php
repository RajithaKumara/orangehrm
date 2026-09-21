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
use OrangeHRM\Mcp\Service\McpConfigurationService;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Service
 */
class McpConfigurationServiceTest extends KernelTestCase
{
    private McpConfigurationService $service;

    protected function setUp(): void
    {
        $this->service = new McpConfigurationService();
        $this->createKernel();
    }

    private function stubDao(array $values): void
    {
        $dao = $this->getMockBuilder(ConfigDao::class)->getMock();
        $dao->method('getValue')->willReturnCallback(
            fn (string $key) => $values[$key] ?? null
        );
        $this->service->setConfigDao($dao);
    }

    public function testDefaultsAppliedWhenDaoReturnsNull(): void
    {
        $this->stubDao([]);
        $this->assertSame(0, $this->service->getAuditRetentionDays());
        $this->assertSame('digest_only', $this->service->getAuditRedactionPolicy());
    }

    public function testIntOverride(): void
    {
        $this->stubDao([McpConfigurationService::KEY_AUDIT_RETENTION_DAYS => '10']);
        $this->assertSame(10, $this->service->getAuditRetentionDays());
    }

    public function testEmptyStringIntCoercesToZero(): void
    {
        $this->stubDao([McpConfigurationService::KEY_AUDIT_RETENTION_DAYS => '']);
        $this->assertSame(0, $this->service->getAuditRetentionDays());
    }

    public function testStringEnumOverride(): void
    {
        $this->stubDao([
            McpConfigurationService::KEY_AUDIT_REDACTION_POLICY => 'digest_plus_field_names',
        ]);
        $this->assertSame(
            'digest_plus_field_names',
            $this->service->getAuditRedactionPolicy()
        );
    }
}
