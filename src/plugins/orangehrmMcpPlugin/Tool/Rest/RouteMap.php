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

use OrangeHRM\Framework\RouteManager;
use OrangeHRM\Mcp\Tool\RestrictedApis;

/**
 * Every `/api/v2` operation the framework knows about, read from its own route
 * collection.
 *
 * This is the source of truth for *what exists*, deliberately in preference to
 * the OpenAPI document. Two reasons: the document does not carry the `_api`
 * class, without which there is no permission check; and the document is
 * generated, so it lags. Measured on 5.10, the route collection holds 523
 * `/api/v2` operations while the document describes 490, and 478 join. The 45
 * the document misses are still real endpoints and are exposed with a degraded
 * schema rather than dropped.
 */
class RouteMap
{
    /**
     * @var RestRoute[]|null keyed by tool name
     */
    private ?array $routes = null;

    /**
     * @return RestRoute[] keyed by tool name
     */
    public function all(): array
    {
        if ($this->routes !== null) {
            return $this->routes;
        }

        $routes = [];
        foreach (RouteManager::getRoutes() as $route) {
            $path = $route->getPath();
            if (strpos($path, '/api/v2/') !== 0) {
                continue;
            }

            $defaults = $route->getDefaults();
            $apiClass = $defaults['_api'] ?? null;
            if (!is_string($apiClass) || $apiClass === '') {
                // No API class means no data group, so no permission check is
                // possible. Such a route must never become a tool.
                continue;
            }

            $methods = $route->getMethods();
            if ($methods === []) {
                // An unrestricted route would otherwise be exposed under every
                // verb. Treat it as GET only; a write tool must be explicit.
                $methods = ['GET'];
            }

            foreach ($methods as $method) {
                // Product-level exclusions (RestrictedApis) are applied here, so
                // a restricted endpoint is never built into a tool at all rather
                // than being built and then refused. Nothing downstream can
                // re-admit it by mistake.
                if (!RestrictedApis::isAllowed($apiClass, $method)) {
                    continue;
                }
                $restRoute = new RestRoute($method, $path, $apiClass, $defaults);
                $routes[$restRoute->getToolName()] = $restRoute;
            }
        }

        ksort($routes);
        return $this->routes = $routes;
    }

    /**
     * @return RestRoute[] keyed by tool name
     */
    public function readOnly(): array
    {
        return array_filter($this->all(), static function (RestRoute $route): bool {
            return !$route->isWrite();
        });
    }

    public function get(string $toolName): ?RestRoute
    {
        $all = $this->all();
        return $all[$toolName] ?? null;
    }

    public function count(): int
    {
        return count($this->all());
    }
}
