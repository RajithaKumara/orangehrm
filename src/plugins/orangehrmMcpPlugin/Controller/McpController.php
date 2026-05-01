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

namespace OrangeHRM\Mcp\Controller;

use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use OrangeHRM\Mcp\Traits\McpEnabledTrait;

class McpController extends AbstractController implements PublicControllerInterface
{
    use McpEnabledTrait;

    /**
     * MCP HTTP entry point. When the mcp module is disabled, returns
     * a JSON-RPC -32000 module_disabled envelope before any further
     * processing. When enabled, returns the Story 1.4 placeholder
     * -32601 envelope until Story 2.1 wires the dispatcher.
     */
    public function handle(Request $request): Response
    {
        if (!$this->isMcpModuleEnabled()) {
            return $this->buildJsonRpcResponse([
                'jsonrpc' => '2.0',
                'id' => null,
                'error' => [
                    'code' => -32000,
                    'message' => 'MCP module is disabled',
                    'data' => ['reason' => 'module_disabled'],
                ],
            ]);
        }

        return $this->buildJsonRpcResponse([
            'jsonrpc' => '2.0',
            'id' => null,
            'error' => [
                'code' => -32601,
                'message' => 'Method not found',
                'data' => [
                    'info' => 'MCP method handlers are not yet implemented; see https://modelcontextprotocol.io',
                ],
            ],
        ]);
    }

    private function buildJsonRpcResponse(array $envelope): Response
    {
        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($envelope, JSON_UNESCAPED_SLASHES));
        return $response;
    }
}
