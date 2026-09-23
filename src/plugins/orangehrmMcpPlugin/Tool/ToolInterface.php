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
 * One MCP tool, backed by exactly one REST operation.
 *
 * Generic tools are derived from the route collection; a curated tool would
 * implement this by hand. Everything downstream -- the policy gate, the
 * dispatcher, the annotations published in tools/list -- works through this
 * interface and knows nothing about which of the two it is holding.
 */
interface ToolInterface
{
    /**
     * Stable tool name exposed to the AI client, e.g. `rest_get_pim_myself`.
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
     * HTTP method this tool dispatches with: GET, POST, PUT, PATCH or DELETE.
     *
     * Drives three separate decisions, which is why it is a method rather than
     * something inferred from isWriteOperation(): which of the four top-level
     * verb switches governs the tool, which `ohrm_api_permission` grant the
     * dispatcher must check (canRead, canCreate, canUpdate, canDelete), and
     * which HTTP method the in-process request actually carries. Inferring a
     * method from "is it a write" would dispatch every write as a PUT.
     */
    public function getVerb(): string;

    /**
     * Module the backing endpoint belongs to, checked against the disabled-module
     * list the way ModuleNotAvailableSubscriber does for HTTP requests.
     */
    public function getModule(): string;

    /**
     * Whether invoking this tool changes data.
     *
     * Drives two things: the `readOnlyHint` annotation published in tools/list,
     * which is how the MCP specification lets a client tell a safe tool from a
     * mutating one; and the server-side read-only mode, which refuses write
     * tools outright regardless of what the caller's RBAC would allow.
     *
     * Every tool in the prototype returns false. The first write tool must both
     * return true here and pass through the write-discipline interceptor.
     */
    public function isWriteOperation(): bool;

    /**
     * Whether invoking this tool can destroy or irreversibly change data.
     *
     * Published as `destructiveHint`. Only meaningful when isWriteOperation()
     * is true.
     */
    public function isDestructive(): bool;

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
     * The tool arguments that are not path placeholders, shaped for the REST
     * endpoint.
     *
     * Where they end up depends on the verb, and that is the dispatcher's
     * decision, not the tool's: a GET carries them in the query string, a
     * POST/PUT/DELETE carries them in the body. Tools that want defaults --
     * a page size, a sort order -- apply them here.
     *
     * @param array $arguments validated tool arguments
     * @return array parameters for the dispatched request
     */
    public function buildParams(array $arguments): array;
}
