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

namespace OrangeHRM\Mcp\Session;

/**
 * Per-request MCP session identity.
 *
 * Streamable HTTP binds a session with the `Mcp-Session-Id` header: the server
 * issues one on initialize and the client echoes it on later calls. This
 * prototype only carries the value so audit rows can be correlated. Cache-backed
 * session state with revocation propagation is Story 2.3.
 */
class SessionContext
{
    public const HEADER = 'Mcp-Session-Id';

    private string $sessionId = '';

    private ?string $clientId = null;

    public function getSessionId(): string
    {
        if ($this->sessionId === '') {
            $this->sessionId = self::generateSessionId();
        }
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
    {
        // Ignore anything that is not a plausible id so a hostile header value
        // cannot reach the audit table.
        if (is_string($sessionId) && preg_match('/^[A-Za-z0-9_-]{8,64}$/', $sessionId) === 1) {
            $this->sessionId = $sessionId;
        }
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): void
    {
        if (is_string($clientId) && $clientId !== '') {
            $this->clientId = substr($clientId, 0, 200);
        }
    }

    public static function generateSessionId(): string
    {
        return bin2hex(random_bytes(16));
    }
}
