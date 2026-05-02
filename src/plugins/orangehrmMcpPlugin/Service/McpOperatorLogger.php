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

use OrangeHRM\Core\Traits\LoggerTrait;

class McpOperatorLogger
{
    use LoggerTrait;

    public const EVENT_TOOL_CALLED = 'mcp.tool_called';

    public function logCall(
        string $sessionId,
        string $toolName,
        string $dispatchedEndpoint,
        string $responseStatus,
        int $latencyMs,
        ?string $errorCategory = null
    ): void {
        $context = [
            'event' => self::EVENT_TOOL_CALLED,
            'session_id' => $sessionId,
            'tool_name' => $toolName,
            'dispatched_endpoint' => $dispatchedEndpoint,
            'response_status' => $responseStatus,
            'latency_ms' => $latencyMs,
            'error_category' => $errorCategory,
        ];

        if ($errorCategory !== null) {
            $this->getLogger()->error(self::EVENT_TOOL_CALLED, $context);
            return;
        }
        $this->getLogger()->info(self::EVENT_TOOL_CALLED, $context);
    }
}
