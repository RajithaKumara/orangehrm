---
name: mcp-server
description: Reference for OrangeHRM's Model Context Protocol server — the `orangehrmMcpPlugin` that exposes OrangeHRM to AI assistants over JSON-RPC 2.0 at `/api/mcp`, its two tool layers (hand-written curated tools under `Tool/Curated/` and the generic OpenAPI-driven router under `Tool/Rest/` that turns every `/api/v2` route into a tool with no per-endpoint code), the `RestDispatcher` that is the only sanctioned path to data, the `RestrictedApis` list of areas an assistant may never reach, the four verb switches and the per-API-class `ohrm_mcp_api_permission` grid an administrator configures under Admin > Configuration > MCP Access, the synchronous audit row written for every call, and the OAuth 2 discovery documents that let a client authenticate. Use whenever the user is adding or changing an MCP tool, asking why a tool is missing from `tools/list` or refused at `tools/call`, enabling write (create/update/delete) tools or deciding how sensitive an endpoint's data is, wiring a new REST endpoint that should or should not be reachable by an assistant, debugging an MCP client that cannot connect or authenticate, working on the audit log, or asking what an AI assistant can see and do in OrangeHRM. Companion to `rest-endpoints` (every tool dispatches into that layer), `authorization` (the permission chain MCP reuses rather than replacing), `auth-providers` (the OAuth 2 server MCP authenticates against), `rest-openapi` (the document the generic router consumes).
---

# MCP server

The MCP plugin lets an AI assistant read OrangeHRM as the person using it. It is a protocol surface, not a new API: every tool call is translated into an ordinary REST call and runs through the same permission chain, validation and controllers the web UI uses.

The single most important property: **MCP exposes no more than the signed-in user's existing API authority.** Everything below exists to keep that true.

```
AI client
  → POST /api/mcp                  JSON-RPC 2.0 over Streamable HTTP
  → McpController                  module gate, auth gate, policy gate, TLS gate
  → Dispatcher                     routes to a Method handler by name
  → ToolsCallHandler               resolves the tool, checks policy, audits
  → RestDispatcher                 RBAC + disabled-module check
  → GenericRestController          the ordinary REST pipeline, validation included
```

## The two tool layers

Tools come from two sources that must not know about each other.

**Curated tools** (`Tool/Curated/`) are hand-written classes implementing `ToolInterface`. Each wraps one endpoint with a description written for a model choosing between hundreds of options, not for a human reading reference docs. Use these for the handful of things an assistant is actually asked to do.

**Generic tools** (`Tool/Rest/`) are derived from the framework's own route collection. `RouteMap` walks every `/api/v2` route that declares an `_api` class; `GenericToolFactory` joins each with its OpenAPI operation for a description and parameter schema. Adding a REST endpoint makes it reachable over MCP with no change to this plugin.

Curated names win on collision, so a curated tool can supersede the generic one for the same endpoint without either layer referencing the other.

The route collection, not the OpenAPI document, is the source of truth for *what exists*. The document does not carry the `_api` class, and without that there is no permission check at all. The document is enrichment only: a missing or stale one degrades descriptions and must never change which tools exist.

## Reaching data: `RestDispatcher` only

Two protections that guard the REST surface live in kernel event subscribers, so an in-process call skips them. `RestDispatcher::dispatch()` replicates both, then dispatches through `GenericRestController` so request validation still runs:

- the `ohrm_api_permission` data-group check that `ApiAuthorizationSubscriber` performs
- the disabled-module check that `ModuleNotAvailableSubscriber` performs

**Never instantiate an API class directly from a tool.** Doing so loses both the permission check and validation. The `authorization` skill covers the permission model this reuses.

The dispatch carries the tool's own verb: `getVerb()` decides the HTTP method sent, whether parameters go in the query string (reads) or the body (writes), and which grant is checked — `canRead`, `canCreate`, `canUpdate` or `canDelete`. A tool that reports the wrong verb is dispatched wrongly three ways at once.

A tool must also replay its route's `defaults` block. `GenericRestController` chooses between fetching one record and fetching a list by testing for the key attribute, so a route supplying `id: 0` behaves differently from one that does not — this is a common cause of an endpoint answering "not implemented".

## Areas an assistant may never reach

`Tool/RestrictedApis.php` lists API classes excluded from MCP entirely, and classes that are readable but never writable. This is a product decision rather than a permission check: an administrator holds every grant these endpoints need, so nothing else in the system would stop them.

The list is keyed on the **API class**, not on a route or tool name. A new endpoint added to an already-restricted class is excluded automatically. Read that file for the current list and the reasoning per area; treat adding a new entry as a decision worth discussing, not a formality.

Enforced in two places: `RouteMap` skips restricted routes so they are never built into tools, and `RestDispatcher` re-checks at dispatch, which also catches a curated tool pointed at a restricted class.

## What an administrator controls

`Service/McpAccessPolicy.php` holds the organisation's policy, configured under Admin > Configuration > MCP Access and stored as `mcp.access.*` config values. It answers a different question from RBAC: not "may this user read this endpoint" but "should this person be reaching OrangeHRM through an AI assistant at all".

Every default fails closed. Policy can only ever narrow — the per-call permission check still runs afterwards regardless of what is configured.

`Tool/ToolPolicyGate.php` is the one place that decides whether configuration permits a tool, and it asks three questions in order:

1. **Is the area excluded in code?** `RestrictedApis` — no tenant can re-admit it.
2. **Is the verb switched on?** Four switches, `read` / `create` / `update` / `delete`, stored as `mcp.access.verb.*`. Only Read ships on. This is the circuit breaker: Delete off refuses every delete tool whatever any row says, and switching it back on restores exactly what was enabled before.
3. **Is the API class enabled for that verb?** One row per API class in `ohrm_mcp_api_permission` — `can_read`, `can_create`, `can_update`, `can_delete`.

The grid is keyed on the API class rather than the tool name, so a route rename cannot orphan a decision and a new endpoint in an existing family inherits the one already made. **Absence is refusal**: a class with no row is unreachable. `ApiPermissionDefaults::ALL` is the shipped list and a test asserts it matches the live route collection exactly.

Curated tools carry no special status here — they are governed by the row for the API class behind them, exactly like a generic tool.

Rows also carry a product-authored `sensitivity` (`normal`, `elevated`, `high`) from `Tool/ApiSensitivity.php`, seeded by the migration and **not editable in the UI**. It is a separate axis from verb risk: verb risk is about what a call does, sensitivity is about what it touches and applies even to a read. The screen shows it as a badge; `tools/list` publishes it under `_meta.orangehrm.sensitivity`.

## Audit

Every `tools/call` writes one row to `ohrm_mcp_audit_log` **before the result is returned**, failures included. If the audit write fails, the call fails: a result must never reach a client without a corresponding row.

Arguments are stored only as a SHA-256 digest over canonicalised arguments. Raw values never reach the table.

## Recipe: adding a curated tool

1. Add a class under `Tool/Curated/` implementing `ToolInterface`.
2. Write `getDescription()` for a model, not a human. Say when to use it and what it returns. This is the single biggest factor in whether the tool ever gets chosen.
3. `getApiClass()` must be the `_api` class from the endpoint's route, or the permission check keys off the wrong thing.
4. `getRouteAttributes()` must replay the route's `defaults` (minus the framework's own keys).
5. Return the real HTTP method from `getVerb()`, and `false` from `isWriteOperation()` unless it genuinely writes. Between them they drive the published annotation, the verb switch that governs the tool, the grant checked at dispatch and the method sent.
6. Register it in the plugin configuration's tool registry.
7. Check that the API class behind it has `can_read` in `ohrm_mcp_api_permission`, or the tool will not appear — a tool is only as enabled as its API class.

## Recipe: exposing a new REST endpoint to assistants

Usually nothing to do. A new `/api/v2` route with an `_api` class becomes a generic tool automatically once the generic router is switched on.

Decide two things:

- Should it be reachable at all? If it touches credentials, authentication config, or irreversible destruction, add its API class to `RestrictedApis`.
- Does it deserve a curated tool? Only if an assistant will be asked for it by name and the generated description would not be enough.

If the endpoint introduces a **new** API class, add it to `ApiPermissionDefaults::ALL` and seed a row in a migration, or it is silently unreachable — the drift test will tell you. Classify its sensitivity in `ApiSensitivity` at the same time.

Regenerate the OpenAPI document so the generic tool gets a description and parameter schema — see `rest-openapi`.

## Checklist for MCP changes

- [ ] Data reached only through `RestDispatcher`; no API class instantiated directly.
- [ ] `getApiClass()` matches the route's `_api`.
- [ ] Route `defaults` replayed, so one-record endpoints resolve.
- [ ] Write intent and verb declared honestly on the tool.
- [ ] New API classes added to `ApiPermissionDefaults`, classified in `ApiSensitivity`, and seeded by a migration.
- [ ] Sensitive areas added to `RestrictedApis` with a reason.
- [ ] Verified as a non-administrator, not only as an administrator.
- [ ] Audit row present for the new path.

## Things that bite

- **The tool is missing from the listing.** Work down the three gates in order: restricted in code, verb switched off, or the API class not enabled in `ohrm_mcp_api_permission`. `php bin/console mcp:doctor` prints the enabled counts per verb. Only then check the permission chain as the *calling* user, not as an administrator.
- **A whole area is missing and the grid looks right.** Check the class has a row at all. Absence is refusal, and an upgrade that added endpoints without seeding rows leaves them invisible.
- **"Unknown tool" rather than a permission error.** The tool was never built. Either it is restricted, or its route has no `_api` class.
- **The endpoint answers "not implemented".** The route's `defaults` were not replayed, so the dispatcher asked for a list from an endpoint that only serves one record.
- **Testing only as an administrator.** An administrator sees roughly twice the catalog an ESS user does. Permission bugs are invisible from an admin account.
- **Trusting tool annotations as a control.** They are hints for the client. The server enforces the same facts itself and must never rely on a client honouring them.
- **Assuming the OpenAPI document governs the surface.** It does not. It only enriches descriptions; the route collection decides what exists.
- **A client that cannot authenticate.** The discovery chain has three hops and the last one resolves at the *host root*, not the application path. One hostname therefore serves one instance's MCP endpoint. Clients also validate certificates, so a self-signed certificate fails before any OrangeHRM error appears.
