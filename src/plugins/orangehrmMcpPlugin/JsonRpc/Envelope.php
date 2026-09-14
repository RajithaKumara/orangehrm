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

/**
 * Immutable view of one parsed JSON-RPC 2.0 request, plus the encoders for
 * result and error envelopes. Knows nothing about MCP semantics.
 */
final class Envelope
{
    public const VERSION = '2.0';

    /**
     * @var string|int|null
     */
    private $id;

    private string $method;

    private array $params;

    private bool $isNotification;

    /**
     * @param string|int|null $id
     */
    private function __construct($id, string $method, array $params, bool $isNotification)
    {
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
        $this->isNotification = $isNotification;
    }

    /**
     * @param mixed $payload decoded JSON body
     * @return Envelope
     * @throws EnvelopeException when the payload is not a valid JSON-RPC request
     */
    public static function fromPayload($payload): Envelope
    {
        if (!is_array($payload)) {
            throw new EnvelopeException(ErrorCode::INVALID_REQUEST, 'Invalid Request: expected a JSON object');
        }

        // A JSON-RPC batch arrives as a list. MCP dropped batching, so reject it
        // explicitly rather than silently handling only the first element.
        if (array_key_exists(0, $payload)) {
            throw new EnvelopeException(ErrorCode::INVALID_REQUEST, 'Batch requests are not supported');
        }

        $version = $payload['jsonrpc'] ?? null;
        if ($version !== self::VERSION) {
            throw new EnvelopeException(ErrorCode::INVALID_REQUEST, 'Invalid Request: jsonrpc must be "2.0"');
        }

        $method = $payload['method'] ?? null;
        if (!is_string($method) || $method === '') {
            throw new EnvelopeException(ErrorCode::INVALID_REQUEST, 'Invalid Request: method must be a string');
        }

        $params = $payload['params'] ?? [];
        if (!is_array($params)) {
            throw new EnvelopeException(ErrorCode::INVALID_PARAMS, 'Invalid params: expected an object or array');
        }

        // Absent id means notification. A null id is technically present, but the
        // spec says notifications carry no id at all, so treat only absence as one.
        $isNotification = !array_key_exists('id', $payload);
        $id = $isNotification ? null : $payload['id'];
        if ($id !== null && !is_string($id) && !is_int($id)) {
            throw new EnvelopeException(ErrorCode::INVALID_REQUEST, 'Invalid Request: id must be a string or number');
        }

        return new self($id, $method, $params, $isNotification);
    }

    /**
     * @return string|int|null
     */
    public function getId()
    {
        return $this->id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function isNotification(): bool
    {
        return $this->isNotification;
    }

    /**
     * @param string|int|null $id
     * @param mixed $result
     */
    public static function encodeResult($id, $result): string
    {
        return json_encode([
            'jsonrpc' => self::VERSION,
            'id' => $id,
            'result' => $result,
        ], JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * @param string|int|null $id
     */
    public static function encodeError($id, int $code, string $message, array $data = []): string
    {
        $error = [
            'code' => $code,
            'message' => $message,
        ];
        if ($data !== []) {
            $error['data'] = $data;
        }

        return json_encode([
            'jsonrpc' => self::VERSION,
            'id' => $id,
            'error' => $error,
        ], JSON_UNESCAPED_SLASHES);
    }
}
