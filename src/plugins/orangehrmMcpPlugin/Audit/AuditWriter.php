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

namespace OrangeHRM\Mcp\Audit;

use DateTime;
use DateTimeZone;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Mcp\JsonRpc\EnvelopeException;
use OrangeHRM\Mcp\JsonRpc\ErrorCode;
use Throwable;

/**
 * Writes one ohrm_mcp_audit_log row per tools/call, synchronously, before the
 * result is returned.
 *
 * NFR-R3 makes audit completeness non-negotiable: if the row cannot be written
 * the originating call fails. A result must never reach the client without a
 * corresponding audit row.
 *
 * Raw parameter values are never stored -- only a SHA-256 digest over the
 * canonicalised arguments, so equal calls correlate without the values being
 * recoverable.
 */
class AuditWriter
{
    use EntityManagerHelperTrait;

    public const TABLE = 'ohrm_mcp_audit_log';

    /**
     * @throws EnvelopeException when the audit row cannot be persisted
     */
    public function write(
        ?int $userId,
        string $sessionId,
        string $toolName,
        ?string $endpoint,
        ?string $verb,
        array $arguments,
        string $responseStatus,
        ?int $latencyMs,
        ?string $clientId
    ): void {
        try {
            $this->getEntityManager()->getConnection()->insert(self::TABLE, [
                'user_id' => $userId,
                'mcp_session_id' => $sessionId,
                'tool_name' => $toolName,
                'dispatched_endpoint' => $endpoint,
                'verb' => $verb,
                'parameter_digest' => self::digest($arguments),
                'response_status' => $responseStatus,
                'latency_ms' => $latencyMs,
                'mcp_client_id' => $clientId,
                // Stored in UTC explicitly; the column is not timezone-aware and
                // mixed-timezone deployments would otherwise be ambiguous.
                'created_at' => (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            throw new EnvelopeException(
                ErrorCode::AUDIT_WRITE_FAILED,
                'Audit write failed; the call was not completed',
                ['category' => ErrorCode::CATEGORY_AUDIT_WRITE_FAILED, 'tool' => $toolName],
                $e
            );
        }
    }

    /**
     * SHA-256 over canonicalised arguments: keys sorted recursively so that
     * semantically identical calls produce identical digests.
     */
    public static function digest(array $arguments): string
    {
        $canonical = self::canonicalise($arguments);
        return hash('sha256', (string) json_encode($canonical, JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function canonicalise($value)
    {
        if (!is_array($value)) {
            return $value;
        }
        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = self::canonicalise($item);
        }
        ksort($out);
        return $out;
    }
}
