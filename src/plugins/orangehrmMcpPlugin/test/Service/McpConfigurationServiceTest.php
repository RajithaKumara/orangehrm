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
        $this->assertSame(60, $this->service->getRateLimitPerSessionPerMinute());
        $this->assertSame(120, $this->service->getRateLimitPerUserPerMinute());
        $this->assertFalse($this->service->isDailyCallCapEnabled());
        $this->assertSame(0, $this->service->getDailyCallCapValue());
        $this->assertSame(5, $this->service->getConcurrentSessionsPerUser());
        $this->assertSame(0, $this->service->getAuditRetentionDays());
        $this->assertSame('digest_only', $this->service->getAuditRedactionPolicy());
    }

    public function testBoolFalsyOverride(): void
    {
        $this->stubDao([McpConfigurationService::KEY_DAILY_CALL_CAP_ENABLED => '0']);
        $this->assertFalse($this->service->isDailyCallCapEnabled());
    }

    public function testBoolTruthyVariantsAreCaseInsensitive(): void
    {
        foreach (['1', 'true', 'TRUE', 'yes', 'YES', 'True'] as $raw) {
            $this->stubDao([McpConfigurationService::KEY_DAILY_CALL_CAP_ENABLED => $raw]);
            $this->assertTrue(
                $this->service->isDailyCallCapEnabled(),
                "raw=$raw should coerce to true"
            );
        }
    }

    public function testBoolNonTruthyValuesAreFalse(): void
    {
        foreach (['0', 'false', 'no', 'maybe', ''] as $raw) {
            $this->stubDao([McpConfigurationService::KEY_DAILY_CALL_CAP_ENABLED => $raw]);
            $this->assertFalse(
                $this->service->isDailyCallCapEnabled(),
                "raw=$raw should coerce to false"
            );
        }
    }

    public function testIntOverride(): void
    {
        $this->stubDao([McpConfigurationService::KEY_CONCURRENT_SESSIONS_PER_USER => '10']);
        $this->assertSame(10, $this->service->getConcurrentSessionsPerUser());
    }

    public function testEmptyStringIntCoercesToZero(): void
    {
        $this->stubDao([McpConfigurationService::KEY_DAILY_CALL_CAP_VALUE => '']);
        $this->assertSame(0, $this->service->getDailyCallCapValue());
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
