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
 * Joins the framework's route collection with the OpenAPI document to produce
 * one tool per REST operation.
 *
 * Built lazily and held for the request. The join is pure and ordered, so the
 * same OrangeHRM build always yields the same tool names -- which matters
 * because a model's prompt may reference them.
 *
 * Curated tools win on name collision. That is what lets a curated intent
 * supersede the generic tool for the same endpoint without the generic router
 * knowing anything about the curated layer (NFR-M1).
 */
class GenericToolFactory
{
    private RouteMap $routeMap;

    private OpenApiLoader $openApi;

    /**
     * @var GenericTool[]|null keyed by tool name
     */
    private ?array $tools = null;

    public function __construct(RouteMap $routeMap, OpenApiLoader $openApi)
    {
        $this->routeMap = $routeMap;
        $this->openApi = $openApi;
    }

    /**
     * @param string[] $excludeNames names already claimed by curated tools
     * @return GenericTool[] keyed by tool name
     */
    public function readOnlyTools(array $excludeNames = []): array
    {
        if ($this->tools === null) {
            $tools = [];
            foreach ($this->routeMap->readOnly() as $name => $route) {
                $tools[$name] = new GenericTool(
                    $route,
                    $this->openApi->getOperation($route->getMethod(), $route->getPath())
                );
            }
            $this->tools = $tools;
        }

        if ($excludeNames === []) {
            return $this->tools;
        }

        $excluded = array_flip($excludeNames);
        return array_filter(
            $this->tools,
            static function (string $name) use ($excluded): bool {
                return !isset($excluded[$name]);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Counts for `mcp:doctor` and the admin screen, so an operator can see how
     * much of the REST surface the document actually describes.
     *
     * @return array{routes:int,readOnly:int,described:int,specAvailable:bool,checksum:?string}
     */
    public function stats(): array
    {
        $readOnly = $this->routeMap->readOnly();
        $described = 0;
        foreach ($readOnly as $route) {
            if ($this->openApi->getOperation($route->getMethod(), $route->getPath()) !== null) {
                $described++;
            }
        }

        return [
            'routes' => $this->routeMap->count(),
            'readOnly' => count($readOnly),
            'described' => $described,
            'specAvailable' => $this->openApi->isAvailable(),
            'checksum' => $this->openApi->getChecksum(),
        ];
    }
}
