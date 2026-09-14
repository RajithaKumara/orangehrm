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

namespace OrangeHRM\Mcp\Tool;

use OrangeHRM\Mcp\Tool\Rest\GenericToolFactory;

/**
 * In-memory catalog of curated tools. Populated from McpPluginConfiguration so
 * adding a tool is a DI registration, not an edit to the protocol code.
 *
 * When the generic OpenAPI router (Story 2.6) lands it becomes a second source
 * merged here; the curated overlay must not reach into the router's internals.
 */
class ToolRegistry
{
    /**
     * @var ToolInterface[] curated tools, keyed by tool name
     */
    private array $tools = [];

    private ?GenericToolFactory $genericFactory = null;

    /**
     * @var ToolInterface[]|null merged view, built once per request
     */
    private ?array $merged = null;

    /**
     * @param ToolInterface[] $tools
     */
    public function __construct(array $tools = [])
    {
        foreach ($tools as $tool) {
            $this->add($tool);
        }
    }

    public function add(ToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
        $this->merged = null;
    }

    /**
     * Attaches the generic OpenAPI-driven surface. Passing null detaches it,
     * which is how the whole generic layer is switched off.
     */
    public function setGenericFactory(?GenericToolFactory $factory): void
    {
        $this->genericFactory = $factory;
        $this->merged = null;
    }

    /**
     * @return ToolInterface[] curated only, keyed by name
     */
    public function curated(): array
    {
        return $this->tools;
    }

    public function hasGenericSurface(): bool
    {
        return $this->genericFactory !== null;
    }

    /**
     * Curated tools first, then generic ones. Curated names win on collision,
     * so a curated intent can supersede the generic tool for the same endpoint
     * without either layer knowing about the other.
     *
     * @return ToolInterface[]
     */
    public function all(): array
    {
        return array_values($this->merged());
    }

    public function has(string $name): bool
    {
        $merged = $this->merged();
        return isset($merged[$name]);
    }

    public function get(string $name): ?ToolInterface
    {
        $merged = $this->merged();
        return $merged[$name] ?? null;
    }

    /**
     * @return ToolInterface[] keyed by name
     */
    private function merged(): array
    {
        if ($this->merged !== null) {
            return $this->merged;
        }
        if ($this->genericFactory === null) {
            return $this->merged = $this->tools;
        }

        return $this->merged = $this->tools
            + $this->genericFactory->readOnlyTools(array_keys($this->tools));
    }
}
