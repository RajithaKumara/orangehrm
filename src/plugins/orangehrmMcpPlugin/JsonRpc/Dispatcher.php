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

namespace OrangeHRM\Mcp\JsonRpc;

use OrangeHRM\Mcp\Method\MethodHandlerInterface;

/**
 * Routes a parsed envelope to the handler registered for its method.
 *
 * Handlers come from McpPluginConfiguration, so adding an MCP method never
 * touches this class or the controller.
 */
class Dispatcher
{
    /**
     * @var MethodHandlerInterface[] keyed by method name
     */
    private array $handlers = [];

    /**
     * @param MethodHandlerInterface[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $handler) {
            $this->register($handler);
        }
    }

    public function register(MethodHandlerInterface $handler): void
    {
        $this->handlers[$handler->getMethodName()] = $handler;
    }

    public function has(string $method): bool
    {
        return isset($this->handlers[$method]);
    }

    /**
     * @return mixed the `result` member for the response envelope
     * @throws EnvelopeException when no handler is registered for the method
     */
    public function dispatch(Envelope $envelope)
    {
        $method = $envelope->getMethod();
        if (!isset($this->handlers[$method])) {
            throw new EnvelopeException(
                ErrorCode::METHOD_NOT_FOUND,
                sprintf('Method `%s` is not supported by this server', $method)
            );
        }

        return $this->handlers[$method]->handle($envelope);
    }
}
