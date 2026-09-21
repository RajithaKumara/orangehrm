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

namespace OrangeHRM\Tests\Mcp\JsonRpc;

use OrangeHRM\Mcp\JsonRpc\Dispatcher;
use OrangeHRM\Mcp\JsonRpc\Envelope;
use OrangeHRM\Mcp\JsonRpc\EnvelopeException;
use OrangeHRM\Mcp\JsonRpc\ErrorCode;
use OrangeHRM\Mcp\Method\InitializeHandler;
use OrangeHRM\Mcp\Method\MethodHandlerInterface;
use OrangeHRM\Mcp\Method\PingHandler;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group JsonRpc
 */
class DispatcherTest extends TestCase
{
    private function envelope(string $method, array $params = []): Envelope
    {
        return Envelope::fromPayload([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => $params,
        ]);
    }

    public function testRoutesToTheHandlerRegisteredForTheMethod(): void
    {
        $dispatcher = new Dispatcher([new PingHandler()]);

        $this->assertTrue($dispatcher->has('ping'));
        $this->assertFalse($dispatcher->has('tools/list'));
        $this->assertEquals((object) [], $dispatcher->dispatch($this->envelope('ping')));
    }

    public function testUnknownMethodRaisesMethodNotFound(): void
    {
        $dispatcher = new Dispatcher([new PingHandler()]);

        $this->expectException(EnvelopeException::class);
        $this->expectExceptionCode(ErrorCode::METHOD_NOT_FOUND);
        $dispatcher->dispatch($this->envelope('tools/list'));
    }

    public function testLaterRegistrationOfTheSameMethodWins(): void
    {
        $replacement = new class () implements MethodHandlerInterface {
            public function getMethodName(): string
            {
                return 'ping';
            }

            public function handle(Envelope $envelope)
            {
                return ['replaced' => true];
            }
        };

        $dispatcher = new Dispatcher([new PingHandler()]);
        $dispatcher->register($replacement);

        $this->assertSame(['replaced' => true], $dispatcher->dispatch($this->envelope('ping')));
    }

    /**
     * The server must answer with a version it supports. Echoing an unsupported
     * client version would claim compliance we do not have.
     */
    public function testInitializeNegotiatesProtocolVersion(): void
    {
        $dispatcher = new Dispatcher([new InitializeHandler()]);

        $supported = $dispatcher->dispatch($this->envelope('initialize', ['protocolVersion' => '2024-11-05']));
        $this->assertSame('2024-11-05', $supported['protocolVersion']);

        $unsupported = $dispatcher->dispatch($this->envelope('initialize', ['protocolVersion' => '1999-01-01']));
        $this->assertSame(InitializeHandler::PROTOCOL_VERSION, $unsupported['protocolVersion']);

        $absent = $dispatcher->dispatch($this->envelope('initialize'));
        $this->assertSame(InitializeHandler::PROTOCOL_VERSION, $absent['protocolVersion']);
    }

    /**
     * Advertising a capability the server does not implement makes clients call
     * methods that do not exist, and a -32601 reads as a broken server rather
     * than a missing feature. `tools` and `prompts` are real; `resources` is
     * not, and must stay unadvertised until it is.
     */
    public function testInitializeAdvertisesOnlyImplementedCapabilities(): void
    {
        $dispatcher = new Dispatcher([new InitializeHandler()]);
        $result = $dispatcher->dispatch($this->envelope('initialize'));

        $this->assertArrayHasKey('tools', $result['capabilities']);
        $this->assertArrayHasKey('prompts', $result['capabilities']);
        $this->assertArrayNotHasKey('resources', $result['capabilities']);
        $this->assertSame('OrangeHRM-MCP', $result['serverInfo']['name']);
    }
}
