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

namespace OrangeHRM\Mcp\Method;

use OrangeHRM\Mcp\JsonRpc\Envelope;

/**
 * MCP `initialize`. Negotiates protocol version and advertises the capability
 * set this server actually implements.
 */
class InitializeHandler implements MethodHandlerInterface
{
    /**
     * Pinned spec revision: the one that introduced protected-resource metadata
     * and made Streamable HTTP the default transport.
     */
    public const PROTOCOL_VERSION = '2025-06-18';

    /**
     * Older revisions we still answer on if a client asks for them.
     */
    public const SUPPORTED_PROTOCOL_VERSIONS = ['2025-06-18', '2025-03-26', '2024-11-05'];

    public const SERVER_NAME = 'OrangeHRM-MCP';
    public const SERVER_VERSION = '0.1.0';

    public function getMethodName(): string
    {
        return 'initialize';
    }

    public function handle(Envelope $envelope)
    {
        $params = $envelope->getParams();
        $requested = $params['protocolVersion'] ?? null;

        // Echo the client's version when we support it, otherwise answer with
        // ours and let the client decide whether it can continue.
        $negotiated = is_string($requested) && in_array($requested, self::SUPPORTED_PROTOCOL_VERSIONS, true)
            ? $requested
            : self::PROTOCOL_VERSION;

        return [
            'protocolVersion' => $negotiated,
            'capabilities' => [
                'tools' => ['listChanged' => false],
                // Prompts live in ohrm_mcp_prompt, so the set can change under
                // a running server when someone edits the table. Advertised
                // without listChanged because there is nothing watching the
                // table to send the notification.
                'prompts' => ['listChanged' => false],
                // resources/ arrives with Epic 5 and must not be advertised
                // before it works.
            ],
            'serverInfo' => [
                'name' => self::SERVER_NAME,
                'version' => self::SERVER_VERSION,
            ],
            'instructions' => 'OrangeHRM HR data. Every tool runs as the authenticated '
                . 'OrangeHRM user and returns only what that user is permitted to see. '
                . 'Call rest_get_pim_myself first to resolve who the user is.',
        ];
    }
}
