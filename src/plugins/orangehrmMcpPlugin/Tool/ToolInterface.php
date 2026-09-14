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

/**
 * A curated, read-only MCP tool backed by exactly one REST endpoint.
 *
 * v1 prototype scope: every tool is a GET. The generic OpenAPI-driven router
 * (Story 2.6) and write discipline (Epic 3) are not part of this interface yet
 * -- when they land, `getVerb()` stops being implicitly GET and the write path
 * goes through the WriteDisciplineInterceptor.
 */
interface ToolInterface
{
    /**
     * Stable tool name exposed to the AI client, e.g. `mcp_get_current_employee`.
     */
    public function getName(): string;

    /**
     * LLM-facing description. Written for tool selection, not for humans.
     */
    public function getDescription(): string;

    /**
     * JSON Schema for the tool's arguments.
     */
    public function getInputSchema(): array;

    /**
     * FQCN of the API class backing this tool. Used for the `ohrm_api_permission`
     * RBAC check, exactly as ApiAuthorizationSubscriber does for HTTP requests.
     */
    public function getApiClass(): string;

    /**
     * REST path this tool dispatches to, e.g. `/api/v2/pim/myself`.
     */
    public function getPath(): string;

    /**
     * Module the backing endpoint belongs to, checked against the disabled-module
     * list the way ModuleNotAvailableSubscriber does for HTTP requests.
     */
    public function getModule(): string;

    /**
     * Request attributes the real route would have supplied via its `defaults`
     * block, minus `_api`.
     *
     * This matters more than it looks. GenericRestController decides between
     * getOne() and getAll() by asking whether the `_key` attribute (default
     * `id`) is present, so a ResourceEndpoint with no id in its path -- such as
     * MyInfoAPI, whose route declares `id: 0` -- is only reachable when that
     * default is replicated here. Without it the dispatch falls through to
     * getAll() and fails as "not a CollectionEndpoint".
     *
     * @return array<string,mixed>
     */
    public function getRouteAttributes(): array;

    /**
     * Map MCP tool arguments onto REST query parameters.
     *
     * @param array $arguments validated tool arguments
     * @return array query parameters for the dispatched request
     */
    public function buildQueryParams(array $arguments): array;
}
