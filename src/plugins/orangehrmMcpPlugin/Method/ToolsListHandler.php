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
use OrangeHRM\Mcp\Service\McpAccessPolicy;
use OrangeHRM\Mcp\Tool\RestDispatcher;
use OrangeHRM\Mcp\Tool\ToolInterface;
use OrangeHRM\Mcp\Tool\ToolPolicyGate;
use OrangeHRM\Mcp\Tool\ToolRegistry;

/**
 * MCP `tools/list`, projected through the caller's effective permissions so the
 * catalog never advertises a tool the user cannot invoke.
 *
 * This filtering is a usability measure, not the security boundary -- tools/call
 * re-checks every grant at dispatch (defense in depth, NFR-S1).
 *
 * Paginated because the generic router can contribute hundreds of tools and a
 * single response would be both enormous and useless to a model.
 */
class ToolsListHandler implements MethodHandlerInterface
{
    /**
     * Tools per page. Chosen to keep a page comfortably under the 100 KB
     * response budget even when every tool carries a full parameter schema.
     */
    public const PAGE_SIZE = 50;

    private ToolRegistry $registry;

    private RestDispatcher $dispatcher;

    private McpAccessPolicy $policy;

    private ?DiscoverToolsHandler $discovery;

    public function __construct(
        ToolRegistry $registry,
        RestDispatcher $dispatcher,
        McpAccessPolicy $policy,
        ?DiscoverToolsHandler $discovery = null
    ) {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
        $this->policy = $policy;
        $this->discovery = $discovery;
    }

    public function getMethodName(): string
    {
        return 'tools/list';
    }

    public function handle(Envelope $envelope)
    {
        $gate = new ToolPolicyGate($this->policy, $this->registry);

        $visible = [];
        foreach ($this->registry->all() as $tool) {
            if (!$gate->isAllowed($tool)) {
                continue;
            }
            if (!$this->dispatcher->isVisibleTo($tool)) {
                continue;
            }
            $visible[] = $this->describe($tool);
        }

        $offset = $this->decodeCursor($envelope->getParams()['cursor'] ?? null);
        $page = array_slice($visible, $offset, self::PAGE_SIZE);

        // The discovery meta-tool leads the first page and is never paginated
        // away, because it is how a model reaches anything on later pages. It
        // is a protocol affordance rather than a REST endpoint, so it is not in
        // the registry and needs no permission check of its own -- every result
        // it returns is filtered by the same gate as this list.
        if ($this->discovery !== null && $offset === 0) {
            array_unshift($page, [
                'name' => $this->discovery->getName(),
                'description' => $this->discovery->getDescription(),
                'inputSchema' => $this->discovery->getInputSchema(),
                'annotations' => [
                    'readOnlyHint' => true,
                    'destructiveHint' => false,
                    'idempotentHint' => true,
                    'openWorldHint' => false,
                ],
            ]);
        }

        $result = ['tools' => $page];

        $next = $offset + self::PAGE_SIZE;
        if ($next < count($visible)) {
            $result['nextCursor'] = $this->encodeCursor($next);
        }

        return $result;
    }

    private function describe(ToolInterface $tool): array
    {
        return [
            'name' => $tool->getName(),
            'description' => $tool->getDescription(),
            'inputSchema' => $tool->getInputSchema(),
            // MCP tool annotations. Hints for the client, never a security
            // boundary -- the server enforces the same facts itself in
            // tools/call. A client uses them to decide what to surface for
            // human confirmation.
            'annotations' => [
                'readOnlyHint' => !$tool->isWriteOperation(),
                'destructiveHint' => $tool->isDestructive(),
                'idempotentHint' => !$tool->isWriteOperation(),
                'openWorldHint' => false,
            ],
        ];
    }

    /**
     * Cursors are opaque to the client by contract. Offset encoding is enough
     * here because the catalog is stable for the life of a request.
     */
    private function encodeCursor(int $offset): string
    {
        return base64_encode('offset:' . $offset);
    }

    /**
     * @param mixed $cursor
     */
    private function decodeCursor($cursor): int
    {
        if (!is_string($cursor) || $cursor === '') {
            return 0;
        }
        $decoded = base64_decode($cursor, true);
        if ($decoded === false || strpos($decoded, 'offset:') !== 0) {
            // A malformed cursor restarts from the beginning rather than
            // failing the call; the client gets a usable answer either way.
            return 0;
        }
        return max(0, (int) substr($decoded, strlen('offset:')));
    }
}
