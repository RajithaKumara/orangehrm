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

namespace OrangeHRM\Mcp\Tool\Rest;

use OrangeHRM\Config\Config;
use Throwable;

/**
 * Reads the OpenAPI document produced by
 * `php devTools/core/console.php generate-open-api-doc` and indexes it by
 * method and path, so the generic router can enrich a route with a summary and
 * parameter schema.
 *
 * Enrichment only. Nothing here decides whether a tool exists or who may call
 * it -- RouteMap owns the former, the permission chain the latter. A missing or
 * stale document degrades tool descriptions; it must never widen or narrow the
 * exposed surface, which is why every accessor fails soft.
 *
 * The document is a build artifact, not part of the release, so absence is a
 * normal state rather than an error. `mcp:doctor` reports it.
 */
class OpenApiLoader
{
    public const DEFAULT_RELATIVE_PATH = 'build/orangehrm-v2.json';

    /**
     * @var array<string,array>|null operation objects keyed by "METHOD /path"
     */
    private ?array $operations = null;

    private ?string $checksum = null;

    private bool $loaded = false;

    public function isAvailable(): bool
    {
        return $this->getPath() !== null;
    }

    public function getPath(): ?string
    {
        $base = Config::get(Config::BASE_DIR);
        if (!is_string($base) || $base === '') {
            return null;
        }
        $path = rtrim($base, '/') . '/' . self::DEFAULT_RELATIVE_PATH;
        return is_readable($path) ? $path : null;
    }

    /**
     * Content hash of the document, so `mcp:doctor` can report which revision is
     * in play and a cache can be invalidated when the document is regenerated.
     */
    public function getChecksum(): ?string
    {
        $this->load();
        return $this->checksum;
    }

    /**
     * The operation object for one route, or null when the document does not
     * describe it.
     */
    public function getOperation(string $method, string $path): ?array
    {
        $this->load();
        $key = strtoupper($method) . ' ' . $path;
        return $this->operations[$key] ?? null;
    }

    public function countOperations(): int
    {
        $this->load();
        return $this->operations === null ? 0 : count($this->operations);
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }
        $this->loaded = true;

        $path = $this->getPath();
        if ($path === null) {
            $this->operations = [];
            return;
        }

        try {
            $raw = (string) file_get_contents($path);
            $this->checksum = hash('sha256', $raw);
            $decoded = json_decode($raw, true);
        } catch (Throwable $e) {
            $this->operations = [];
            return;
        }

        if (!is_array($decoded) || !isset($decoded['paths']) || !is_array($decoded['paths'])) {
            $this->operations = [];
            return;
        }

        $operations = [];
        foreach ($decoded['paths'] as $specPath => $methods) {
            if (!is_array($methods)) {
                continue;
            }
            foreach ($methods as $method => $operation) {
                if (!is_array($operation)) {
                    continue;
                }
                $upper = strtoupper((string) $method);
                if (!in_array($upper, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], true)) {
                    continue;
                }
                $operations[$upper . ' ' . $specPath] = $operation;
            }
        }

        $this->operations = $operations;
    }
}
