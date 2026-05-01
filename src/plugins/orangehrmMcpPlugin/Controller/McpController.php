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

class McpController extends AbstractController implements PublicControllerInterface
{
    /**
     * Placeholder MCP HTTP entry point. Returns a JSON-RPC 2.0 "Method not found"
     * envelope until Story 2.1 lands the JSON-RPC dispatcher and method handlers.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $envelope = [
            'jsonrpc' => '2.0',
            'id' => null,
            'error' => [
                'code' => -32601,
                'message' => 'Method not found',
                'data' => [
                    'info' => 'MCP method handlers are not yet implemented; see https://modelcontextprotocol.io',
                ],
            ],
        ];

        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($envelope, JSON_UNESCAPED_SLASHES));

        return $response;
    }
}
