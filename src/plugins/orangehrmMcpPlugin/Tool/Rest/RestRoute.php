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

/**
 * One REST operation the generic router can expose: a method plus path, the API
 * class that serves it, and the route defaults needed to dispatch it.
 *
 * Built from the framework's own route collection rather than from the OpenAPI
 * document, because the document does not carry the `_api` class and that class
 * is what the `ohrm_api_permission` check keys off. The document enriches these
 * with descriptions and parameter schemas where it has them.
 */
class RestRoute
{
    private string $method;

    private string $path;

    private string $apiClass;

    private array $defaults;

    /**
     * @var string[] placeholder names in the path, e.g. ['empNumber']
     */
    private array $pathParams;

    public function __construct(string $method, string $path, string $apiClass, array $defaults)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->apiClass = $apiClass;
        $this->defaults = $defaults;

        $matches = [];
        preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', $path, $matches);
        $this->pathParams = $matches[1] ?? [];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getApiClass(): string
    {
        return $this->apiClass;
    }

    /**
     * Route `defaults` minus the framework's own underscore-prefixed keys.
     * These must be replayed onto a dispatched request: GenericRestController
     * decides between getOne() and getAll() by testing for the `_key` attribute,
     * so a route supplying `id: 0` behaves differently from one that does not.
     */
    public function getRouteAttributes(): array
    {
        $attributes = [];
        foreach ($this->defaults as $key => $value) {
            if ($key === '_api' || $key === '_controller') {
                continue;
            }
            $attributes[$key] = $value;
        }
        return $attributes;
    }

    /**
     * @return string[]
     */
    public function getPathParams(): array
    {
        return $this->pathParams;
    }

    /**
     * Module name, taken from the first segment after `/api/v2/`. Used for the
     * disabled-module check that ModuleNotAvailableSubscriber performs on real
     * HTTP requests.
     */
    public function getModule(): string
    {
        if (preg_match('#^/api/v2/([^/]+)#', $this->path, $m) === 1) {
            return $m[1];
        }
        return 'core';
    }

    public function isWrite(): bool
    {
        return $this->method !== 'GET';
    }

    public function isDestructive(): bool
    {
        return $this->method === 'DELETE';
    }

    /**
     * Deterministic tool name, documented in docs/mcp/tool-naming.md.
     *
     * `GET /api/v2/pim/employees/{empNumber}` becomes
     * `rest_get_pim_employees_by_empNumber`.
     *
     * Stability matters: a model's prompt may reference these names, so the rule
     * must be pure and never depend on registration order or spec contents.
     */
    public function getToolName(): string
    {
        $trimmed = preg_replace('#^/api/v2/#', '', $this->path);
        $segments = [];
        foreach (explode('/', trim((string) $trimmed, '/')) as $segment) {
            if ($segment === '') {
                continue;
            }
            if (preg_match('/^\{(.+)\}$/', $segment, $m) === 1) {
                $segments[] = 'by_' . $m[1];
                continue;
            }
            // Path segments are kebab-case; tool names are snake_case.
            $segments[] = str_replace('-', '_', $segment);
        }

        return 'rest_' . strtolower($this->method) . '_' . implode('_', $segments);
    }
}
