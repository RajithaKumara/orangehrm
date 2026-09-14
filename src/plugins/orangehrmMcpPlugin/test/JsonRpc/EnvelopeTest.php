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

use OrangeHRM\Mcp\JsonRpc\Envelope;
use OrangeHRM\Mcp\JsonRpc\EnvelopeException;
use OrangeHRM\Mcp\JsonRpc\ErrorCode;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group JsonRpc
 */
class EnvelopeTest extends TestCase
{
    public function testParsesAWellFormedRequest(): void
    {
        $envelope = Envelope::fromPayload([
            'jsonrpc' => '2.0',
            'id' => 7,
            'method' => 'tools/call',
            'params' => ['name' => 'x'],
        ]);

        $this->assertSame(7, $envelope->getId());
        $this->assertSame('tools/call', $envelope->getMethod());
        $this->assertSame(['name' => 'x'], $envelope->getParams());
        $this->assertFalse($envelope->isNotification());
    }

    /**
     * Absence of `id` is what makes a message a notification; a present-but-null
     * id is a request whose response carries a null id.
     */
    public function testAbsentIdMeansNotification(): void
    {
        $notification = Envelope::fromPayload([
            'jsonrpc' => '2.0',
            'method' => 'notifications/initialized',
        ]);
        $this->assertTrue($notification->isNotification());

        $request = Envelope::fromPayload([
            'jsonrpc' => '2.0',
            'id' => null,
            'method' => 'ping',
        ]);
        $this->assertFalse($request->isNotification());
    }

    public function testParamsDefaultToAnEmptyArray(): void
    {
        $envelope = Envelope::fromPayload(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping']);
        $this->assertSame([], $envelope->getParams());
    }

    /**
     * MCP removed JSON-RPC batching. Rejecting it explicitly is better than
     * silently processing only the first element of the array.
     */
    public function testBatchRequestsAreRejected(): void
    {
        $this->expectException(EnvelopeException::class);
        $this->expectExceptionCode(ErrorCode::INVALID_REQUEST);
        Envelope::fromPayload([['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping']]);
    }

    /**
     * @dataProvider invalidPayloadProvider
     * @param mixed $payload
     */
    public function testInvalidPayloadsAreRejected($payload, int $expectedCode): void
    {
        $this->expectException(EnvelopeException::class);
        $this->expectExceptionCode($expectedCode);
        Envelope::fromPayload($payload);
    }

    public function invalidPayloadProvider(): array
    {
        return [
            'not an object' => ['a string', ErrorCode::INVALID_REQUEST],
            'wrong version' => [['jsonrpc' => '1.0', 'id' => 1, 'method' => 'ping'], ErrorCode::INVALID_REQUEST],
            'missing version' => [['id' => 1, 'method' => 'ping'], ErrorCode::INVALID_REQUEST],
            'missing method' => [['jsonrpc' => '2.0', 'id' => 1], ErrorCode::INVALID_REQUEST],
            'non-string method' => [['jsonrpc' => '2.0', 'id' => 1, 'method' => 5], ErrorCode::INVALID_REQUEST],
            'empty method' => [['jsonrpc' => '2.0', 'id' => 1, 'method' => ''], ErrorCode::INVALID_REQUEST],
            'scalar params' => [
                ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping', 'params' => 'x'],
                ErrorCode::INVALID_PARAMS,
            ],
            'object id' => [
                ['jsonrpc' => '2.0', 'id' => ['a'], 'method' => 'ping'],
                ErrorCode::INVALID_REQUEST,
            ],
        ];
    }

    public function testEncodesResultAndErrorEnvelopes(): void
    {
        $result = json_decode(Envelope::encodeResult(3, ['ok' => true]), true);
        $this->assertSame(['jsonrpc' => '2.0', 'id' => 3, 'result' => ['ok' => true]], $result);

        $error = json_decode(Envelope::encodeError(null, -32601, 'Method not found'), true);
        $this->assertSame('2.0', $error['jsonrpc']);
        $this->assertNull($error['id']);
        $this->assertSame(-32601, $error['error']['code']);
        // `data` is omitted entirely when empty rather than serialised as null.
        $this->assertArrayNotHasKey('data', $error['error']);

        $withData = json_decode(Envelope::encodeError(1, -32602, 'bad', ['category' => 'validation']), true);
        $this->assertSame('validation', $withData['error']['data']['category']);
    }
}
