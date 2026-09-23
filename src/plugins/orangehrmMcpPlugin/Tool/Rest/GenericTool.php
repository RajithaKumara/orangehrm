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

use OrangeHRM\Mcp\Tool\ToolInterface;

/**
 * A tool derived from one REST operation, with no hand-written code per
 * endpoint. Adding an endpoint to OrangeHRM makes it reachable here with no
 * change to this plugin -- the point of the whole generic layer.
 *
 * The trade-off against a curated tool is description quality. This one can only
 * say what the route and the OpenAPI summary say, which is written for a human
 * reading reference documentation rather than for a model choosing between two
 * hundred options. That gap is what a curated intent would close.
 */
class GenericTool implements ToolInterface
{
    private RestRoute $route;

    private ?array $operation;

    public function __construct(RestRoute $route, ?array $operation = null)
    {
        $this->route = $route;
        $this->operation = $operation;
    }

    public function getName(): string
    {
        return $this->route->getToolName();
    }

    public function getDescription(): string
    {
        $summary = null;
        if (is_array($this->operation)) {
            $summary = $this->operation['summary'] ?? ($this->operation['description'] ?? null);
        }

        $verb = $this->route->getMethod();
        $path = $this->route->getPath();

        if (is_string($summary) && $summary !== '') {
            return sprintf('%s. Calls %s %s in the OrangeHRM REST API.', rtrim($summary, '.'), $verb, $path);
        }

        // No summary in the document, or no document at all. Say what is known
        // rather than inventing a description a model might trust.
        return sprintf(
            'Calls %s %s in the OrangeHRM REST API (%s module). No description is '
            . 'published for this endpoint.',
            $verb,
            $path,
            $this->route->getModule()
        );
    }

    /**
     * Built from the operation's query parameters plus any path placeholders.
     * Path placeholders are always required: without them the URL cannot be
     * formed.
     */
    public function getInputSchema(): array
    {
        $properties = [];
        $required = [];

        foreach ($this->route->getPathParams() as $name) {
            $properties[$name] = [
                'type' => 'string',
                'description' => sprintf('Path parameter `%s`.', $name),
            ];
            $required[] = $name;
        }

        if (is_array($this->operation) && isset($this->operation['parameters'])) {
            foreach ((array) $this->operation['parameters'] as $parameter) {
                if (!is_array($parameter)) {
                    continue;
                }
                $name = $parameter['name'] ?? null;
                if (!is_string($name) || $name === '' || isset($properties[$name])) {
                    continue;
                }
                if (($parameter['in'] ?? 'query') !== 'query') {
                    continue;
                }

                $schema = is_array($parameter['schema'] ?? null) ? $parameter['schema'] : ['type' => 'string'];
                if (isset($parameter['description']) && is_string($parameter['description'])) {
                    $schema['description'] = $parameter['description'];
                }
                $properties[$name] = $schema;

                if (!empty($parameter['required'])) {
                    $required[] = $name;
                }
            }
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties === [] ? (object) [] : $properties,
            // Left open deliberately: the document is incomplete for some
            // endpoints, and refusing an argument it simply failed to describe
            // would make those endpoints unusable.
            'additionalProperties' => true,
        ];
        if ($required !== []) {
            $schema['required'] = array_values(array_unique($required));
        }

        return $schema;
    }

    public function getApiClass(): string
    {
        return $this->route->getApiClass();
    }

    public function getPath(): string
    {
        return $this->route->getPath();
    }

    public function getModule(): string
    {
        return $this->route->getModule();
    }

    public function getVerb(): string
    {
        return $this->route->getMethod();
    }

    public function isWriteOperation(): bool
    {
        return $this->route->isWrite();
    }

    public function isDestructive(): bool
    {
        return $this->route->isDestructive();
    }

    public function getRouteAttributes(): array
    {
        return $this->route->getRouteAttributes();
    }

    /**
     * Path placeholders are consumed into the URL by the dispatcher; everything
     * else is passed through as a query parameter.
     */
    public function buildParams(array $arguments): array
    {
        $pathParams = $this->route->getPathParams();
        $query = [];
        foreach ($arguments as $key => $value) {
            if (in_array($key, $pathParams, true)) {
                continue;
            }
            $query[$key] = $value;
        }
        return $query;
    }

    /**
     * Tags the discovery meta-tool searches, so a query like "leave balance"
     * can reach an endpoint whose summary never uses those words.
     */
    public function getSearchTags(): array
    {
        $tags = [$this->route->getModule(), strtolower($this->route->getMethod())];
        if (is_array($this->operation) && isset($this->operation['tags'])) {
            foreach ((array) $this->operation['tags'] as $tag) {
                if (is_string($tag)) {
                    $tags[] = strtolower($tag);
                }
            }
        }
        return array_values(array_unique($tags));
    }

    public function getRoute(): RestRoute
    {
        return $this->route;
    }
}
