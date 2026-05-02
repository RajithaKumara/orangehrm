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

use OrangeHRM\Framework\Logger\Logger;
use OrangeHRM\Framework\Services;
use OrangeHRM\Mcp\Service\McpOperatorLogger;
use OrangeHRM\Tests\Util\KernelTestCase;

/**
 * @group Mcp
 * @group Service
 */
class McpOperatorLoggerTest extends KernelTestCase
{
    private array $infoCalls = [];
    private array $errorCalls = [];

    private function buildLogger(): McpOperatorLogger
    {
        $logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $logger->method('info')->willReturnCallback(
            function (string $msg, array $ctx = []): void {
                $this->infoCalls[] = ['msg' => $msg, 'ctx' => $ctx];
            }
        );
        $logger->method('error')->willReturnCallback(
            function (string $msg, array $ctx = []): void {
                $this->errorCalls[] = ['msg' => $msg, 'ctx' => $ctx];
            }
        );
        $this->createKernelWithMockServices([Services::LOGGER => $logger]);
        return new McpOperatorLogger();
    }

    public function testSuccessRoutesToInfoWithSixFieldContext(): void
    {
        $service = $this->buildLogger();
        $service->logCall('s1', 'employee.list', '/api/v2/pim/employees', '200', 42);

        $this->assertCount(1, $this->infoCalls);
        $this->assertCount(0, $this->errorCalls);
        $this->assertSame(McpOperatorLogger::EVENT_TOOL_CALLED, $this->infoCalls[0]['msg']);
        $ctx = $this->infoCalls[0]['ctx'];
        $this->assertSame(McpOperatorLogger::EVENT_TOOL_CALLED, $ctx['event']);
        $this->assertSame('s1', $ctx['session_id']);
        $this->assertSame('employee.list', $ctx['tool_name']);
        $this->assertSame('/api/v2/pim/employees', $ctx['dispatched_endpoint']);
        $this->assertSame('200', $ctx['response_status']);
        $this->assertSame(42, $ctx['latency_ms']);
        $this->assertNull($ctx['error_category']);
    }

    public function testErrorCategoryRoutesToErrorWithCategorySet(): void
    {
        $service = $this->buildLogger();
        $service->logCall('s2', 'employee.create', '/api/v2/pim/employees', '400', 91, 'rbac_denied');

        $this->assertCount(0, $this->infoCalls);
        $this->assertCount(1, $this->errorCalls);
        $this->assertSame('rbac_denied', $this->errorCalls[0]['ctx']['error_category']);
    }

    public function testLatencyForwardedAsInt(): void
    {
        $service = $this->buildLogger();
        $service->logCall('s3', 't', '/e', '200', 0);
        $this->assertSame(0, $this->infoCalls[0]['ctx']['latency_ms']);

        $this->infoCalls = [];
        $service->logCall('s4', 't', '/e', '200', 60_000);
        $this->assertSame(60000, $this->infoCalls[0]['ctx']['latency_ms']);
    }
}
