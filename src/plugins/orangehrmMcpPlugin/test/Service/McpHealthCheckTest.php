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

use OrangeHRM\Mcp\Service\McpHealthCheck;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group Service
 */
class McpHealthCheckTest extends TestCase
{
    private function result(string $status): array
    {
        return ['name' => 'check', 'status' => $status, 'message' => ''];
    }

    public function testReadyWhenEverythingPasses(): void
    {
        $this->assertTrue(McpHealthCheck::isReady([
            $this->result(McpHealthCheck::STATUS_PASS),
            $this->result(McpHealthCheck::STATUS_PASS),
        ]));
    }

    /**
     * A skip is not a failure. The OpenAPI document is enrichment -- an
     * instance without one still serves every tool, with thinner descriptions
     * -- so telling an administrator they are not ready would be wrong.
     */
    public function testSkipsDoNotBlockReadiness(): void
    {
        $this->assertTrue(McpHealthCheck::isReady([
            $this->result(McpHealthCheck::STATUS_PASS),
            $this->result(McpHealthCheck::STATUS_SKIP),
        ]));
    }

    public function testOneFailureIsEnoughToBlock(): void
    {
        $this->assertFalse(McpHealthCheck::isReady([
            $this->result(McpHealthCheck::STATUS_PASS),
            $this->result(McpHealthCheck::STATUS_FAIL),
            $this->result(McpHealthCheck::STATUS_PASS),
        ]));
    }

    /**
     * Nothing to check means nothing is known to be broken, which is the same
     * answer the screen gives before its first load.
     */
    public function testEmptyResultsAreReady(): void
    {
        $this->assertTrue(McpHealthCheck::isReady([]));
    }
}
