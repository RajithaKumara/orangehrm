# MCP plugin — MVP release path (2026-09-28)

_This is the work list from here to a releasable MVP. It supersedes section 11 of
[`REVIEW-2026-09-22.md`](REVIEW-2026-09-22.md) as the order of work; that
document stays as the record of each finding and keeps the checklist. Item ids
here are `M-nn`; each names the `R-nn` it closes or narrows._

## 1. What the MVP is

**Ships:** read-only MCP over the existing OAuth server, one hand-registered
public client per AI product, the admin screen with verb switches and the
per-class grid, discovery documents, `mcp:doctor`, the audit table, prompts.
Target client: Claude Code and Claude Desktop, verified end to end.

**Does not ship:** writes (all three write verbs stay off; the switches exist but
are documented as "not supported in this release"), resources, rate limiting in
the application, an audit viewer, per-token revocation, dynamic client
registration, a UI for approved clients or per-user overrides.

**Bar for including a fix here:** it closes a way for data to leave the
instance beyond what the user could already see, breaks a real client, or
leaves an administrator with a wrong belief about what the system does. Code
quality, consistency and completeness items wait, however cheap they look,
because every one costs review time and the list is the point.

**Rule for the fixes themselves:** plugin code first, shared plugins only where
there is no plugin-side answer, no schema changes, no new tables, no new UI.

Current state (verified 2026-09-28): `--testsuite Mcp` green, 161 tests, PHP 7.4;
OAuth plugin tests green; `php-cs-fixer --dry-run` clean; last commit `962c8453d`.

---

## 2. Must do before MVP

Seven items. All but one are inside `orangehrmMcpPlugin`.

### M-01 — Clamp `limit`, cap tool result size (closes R-03) — plugin, S

**Why.** `Endpoint.php:162` treats `limit=0` as unlimited. One tool call with
`{"limit": 0}` dumps a table. This is the only open item that widens what a user
can extract compared with the web UI, where the page size is fixed.

**Smallest fix.** In `Tool/RestDispatcher::dispatch`, before `query->add($params)`:

```php
// GET only; body params are not paginated.
if ($method === HttpRequest::METHOD_GET && array_key_exists('limit', $params)) {
    $limit = (int) $params['limit'];
    if ($limit <= 0 || $limit > self::MAX_LIMIT) {           // MAX_LIMIT = 100
        throw new EnvelopeException(ErrorCode::INVALID_PARAMS,
            sprintf('`limit` must be between 1 and %d', self::MAX_LIMIT),
            ['category' => ErrorCode::CATEGORY_VALIDATION, 'tool' => $tool->getName(), 'parameter' => 'limit']);
    }
}
```

In `Method/ToolsCallHandler::toToolResult`, after `json_encode`: if
`strlen($text) > self::MAX_RESULT_BYTES` (200 000), return
`isError: true` with a text such as `Result too large (N bytes). Narrow the query
or page with offset.` Do not truncate JSON; a half document is worse than a
refusal. Add one sentence to `GenericTool::getDescription` for GET collection
tools: "Paginate with `limit` (max 100) and `offset`."

**Tests.** `RestDispatcherTest`: `limit: 0` and `limit: 500` rejected, `limit: 50`
passes through. `ToolsCallHandlerTest` (new file, also needed by M-03): oversized
payload → error result.

### M-02 — Stop `clientInfo.name` overwriting the resolved client id (closes the rest of R-19) — plugin, S

**Why.** `McpController::handle` sets the token's client id, then the
`initialize` payload's self-reported name replaces it. The audit column is
supposed to name the OAuth client, and this is the one row (initialize) where it
does not.

**Smallest fix.** Delete the `clientInfo` block in `McpController::handle`. If the
reported name is wanted later, it needs its own column; do not add one now.

**Tests.** `McpControllerTest`: initialize with `clientInfo.name` → session
context client id is still the resolved one.

### M-03 — Audit every `tools/call` (closes R-18) — plugin, S

**Why.** Refused calls (policy gate, unknown tool) are exactly what an
administrator reviewing an incident wants to see, and the docblock already
promises "every invocation writes an audit row". Read-only MVP makes the
after-mutation ordering (R-20) moot, so only completeness is needed.

**Smallest fix.** In `ToolsCallHandler::handle`, wrap everything after argument
validation in one `try … finally`-shaped flow: resolve `$tool` (may be null),
compute `$status` (`unknown_tool`, `denied_policy`, `denied`, `exception`, or the
HTTP status), then a single `auditWriter->write(...)` call before any `throw`.
For an unknown tool pass `tool_name = $name`, `dispatched_endpoint = ''`,
`verb = ''`. Leave the discovery meta-tool unaudited; it reads the catalog, not
data.

**Tests.** `ToolsCallHandlerTest`: one assertion per status that `write()` was
called with it; and that an `AuditWriter` throwing `AUDIT_WRITE_FAILED` overrides
a successful dispatch (NFR-R3, never tested).

### M-04 — `WWW-Authenticate` on every 401 from `/api/mcp` (closes R-08) — plugin, S

**Why.** An expired token is refused by `OAuthSubscriber::handleException` with a
bare 401. The spec requires the `resource_metadata` pointer on every 401; without
it a client whose refresh has also expired cannot restart discovery and shows a
generic failure. This is the one protocol item that breaks a real client
session.

**Smallest fix, no core change.** A `KernelEvents::RESPONSE` subscriber in the MCP
plugin (`Subscriber/McpUnauthorizedHeaderSubscriber`), registered in
`McpPluginConfiguration::initialize`:

```php
public function onResponse(ResponseEvent $event): void
{
    $request = $event->getRequest();
    $response = $event->getResponse();
    if ($response->getStatusCode() !== 401 || $response->headers->has('WWW-Authenticate')) {
        return;
    }
    if ($request->attributes->get('_route') !== 'mcp_jsonrpc') {
        return;
    }
    $response->headers->set('WWW-Authenticate', McpController::wwwAuthenticateHeader($request));
}
```

Extract the header string from `McpController::unauthenticated` into that static
helper so both paths produce the same value.

**Tests.** Subscriber unit test: 401 on the MCP route gains the header; 401 on
another route and 200 on the MCP route are untouched.

### M-05 — Stop publicly caching the discovery documents (narrows R-10) — plugin, S

**Why.** `OAuthMetadataController::json` sets `Cache-Control: public,
max-age=3600` on documents built from client-supplied `X-Forwarded-*` headers.
Any shared cache in front of the origin turns a single forged request into an
hour of every client being sent to an attacker's authorization endpoint.
Removing `public` confines a forged document to the requester who forged it.

**Smallest fix.** One line: `Cache-Control: no-store`. Trusted-proxy
configuration and a `mcp.public_url` key are post-MVP; document in SETUP.md that
the TLS gate honours `X-Forwarded-Proto` from any source and is therefore a
deployment guard, not a control.

**Tests.** Existing metadata test asserts the new header value.

### M-06 — Validate `approvedClientIds` on save (narrows R-31) — plugin, S

**Why.** Enforcement switches on the moment the list is non-empty. A typo
through the API locks every client out with "This application is not approved",
and there is no UI to see or fix the list (R-32 stays deferred). Validating the
ids against `ohrm_oauth2_client.client_id` at save time is the cheapest way to
make the SQL/API recipe in SETUP.md safe. Leave the rest of R-31 (role and user
id checks, boolean coercion) for later; the Vue client sends valid values.

**Smallest fix.** In `McpConfigAPI::update`, when `approvedClientIds` is present:
load enabled clients via `OAuthService::getOAuthClientDao()`, reject with 422
listing the unknown ids. Ten lines.

**Tests.** `McpConfigAPITest` (new): unknown id → 422; known id saved.

### M-07 — Consent nonce (closes the remaining half of R-01) — OAuth plugin, S, **needs architect sign-off**

**Why.** After `ConsentGuardTrait` the strong variant is closed. What remains: a
signed-in user who follows a crafted `/oauth2/authorize/consent?…&authorized=true`
link grants consent silently. The code lands only at the client's registered
redirect URI, which bounds it, but the fix is small enough that shipping without
it is hard to defend in a security review of an HR product.

**Smallest fix, keeping GET.** No POST switch, no screen row.
1. `Controller/AuthorizationController::preRender`: generate
   `bin2hex(random_bytes(16))`, store it in the session under
   `oauth.consent_nonce`, and add it to the `params` prop the Vue page already
   receives (`$psrRequest->getQueryParams()` + `['consent_nonce' => $nonce]`).
2. `OAuthAuthorize.vue`: the form already renders `params` as hidden inputs
   next to `authorized`; confirm the new key is included (one line if not).
3. `Controller/OAuth2/AuthorizationController::handle`: before
   `validateAuthorizationRequest`, compare the `consent_nonce` query param with
   the session value using `hash_equals`, remove the session value, and throw
   `OAuthServerException::accessDenied('Consent form expired, start again')` on
   mismatch. league reads its own parameters from the query string, so nothing
   else moves.

About twenty lines across three files. The screen row (R-01 step 3) changes
nothing observable once bearer and nonce are enforced; skip it.

**Tests.** `OAuth2/AuthorizationControllerTest` already exists: add missing nonce
→ denied, wrong nonce → denied, correct nonce → proceeds, nonce single-use.

---

## 3. Should do before MVP if the week allows

Small, plugin-only, and each removes a wrong belief an administrator or a
design partner would otherwise form. Do them in this order and stop when the
release date arrives.

| Id | Closes | Change | Size |
|---|---|---|---|
| M-08 | R-24 | In `McpController::handle` `catch (Throwable)` and in `ToolsCallHandler`, log `get_class`, message and the first 2 KB of the trace at `error` with the correlation id. Never the token; the tripwire test already guards that. | S |
| M-09 | R-23 | In `McpConfigAPI::update`, after saving, one `McpOperatorLogger` line: actor user id, which keys changed, how many grid rows `saveFlags()` changed. No audit row, no diff table. | S |
| M-10 | R-30 | Wrap `McpConfigAPI::update` in `beginTransaction / commitTransaction / rollBackTransaction`. | S |
| M-11 | R-33 | `McpHealthCheck::checkModuleEnabled` hint: replace "Enable the MCP module via Admin → Modules" with the truth: the row is set by the installer and re-enabled with `UPDATE ohrm_module SET status = 1 WHERE name = 'mcp'`. Leave the two-switch design alone. | S |
| M-12 | R-14 | Ship tool descriptions with zero code: run `php devTools/core/console.php generate-open-api-doc` in the release pipeline before phing packages; `build/build.xml:60-67` already copies `build/` except `dist`, so `build/orangehrm-v2.json` lands in the package and `OpenApiLoader` finds it. Add the step to the release runbook and make `mcp:doctor`'s "Tool descriptions available" a FAIL rather than a note when the file is missing. | S |
| M-13 | R-16 | `InitializeHandler`: drop the tool name from `instructions`, or build the sentence only if `rest_get_pim_myself` passes the gate. | S |
| M-14 | R-21 (wording), R-06, R-27, R-10 | `docs/SETUP.md`: (a) the "audience" paragraph at lines 37-45 is stale, the mobile app is now always refused; (b) say that a purged employee's user id remains in `ohrm_mcp_audit_log` by design; (c) say that an MCP token is also a REST token; (d) replace "values are never recoverable" with "not stored, but low-entropy arguments such as an employee number are correlatable"; (e) the TLS gate is advisory behind a proxy. | S |

---

## 4. Deferred past MVP, and why

Recorded so nobody reopens them during the release week. Each stays in the
review checklist.

| Id | Item | Why it can wait |
|---|---|---|
| R-01 steps 2/3 | POST consent, screen row | Nonce (M-07) closes the exposure; the rest is hygiene. |
| R-42 | Revoke on password change, revoke-all, per-token revoke | Incident path exists and is documented (SETUP.md "cut off an assistant"): disable the client (immediate since R-05) or disable the user. Tokens live 30 min. |
| R-20 | Audit before mutation | No writes in MVP. Do it with Epic 3. |
| R-15 | Request-body schemas | Writes only. First story of Epic 3. |
| R-22 | Retention job, audit viewer | One row per call, reads only; growth is slow. SQL recipe in SETUP.md. |
| R-25 | Application rate limiting | Document proxy-level limiting for internet-exposed instances; PBKDF2 cost halved by R-26. |
| R-26 rest | Key loaded as password | Core OAuth change for a CPU cost that is now one derivation per request. |
| R-27 | Scopes / MCP-only clients | Design work in the OAuth plugin; documented in M-14. |
| R-32 | UI for overrides and approved clients | Both optional in MVP; SQL recipe documented; M-06 makes it safe. |
| R-11, R-12, R-13, R-17 | Session id signing, error code naming, protocol header, `tools/list` cost | No client breaks on any of them. |
| R-28, R-29 | Redirect URI validation, consent screen detail | Admin-only footgun; cosmetic. |
| R-34, R-35, R-36, R-37, R-38, R-39, R-40, R-41 | i18n, raw SQL, migration idempotency on dev DBs, prompt provenance, charset, indexes, module derivation, session table | Quality and completeness. Migration idempotency only affects databases that ran a pre-release build. |
| R-04 structural | Untrusted-data frame | Candidate reads ship off and `externalContent` is published; framing waits for the result shape. |

---

## 5. Release checklist

Run on a real instance, not only the suite. Both Epic 1 defects and the
consent finding were invisible to tests that encoded the same assumptions.

1. Fresh install to 5.10 **and** upgrade a 5.9 database: `ohrm_mcp_*` tables
   present, 153 permission rows, `mcp.config.enabled = 0`, all three write
   switches `0`, `CandidateAPI.can_read = 0`.
2. `php bin/console mcp:doctor`: 7 PASS with the OpenAPI document present
   (M-12), and confirm it FAILs when the only client is the mobile app.
3. Claude Code end to end from a clean machine: 401 → discovery → login →
   consent → `initialize` → `tools/list` → `rest_get_pim_myself`. Then with the
   token expired: the client refreshes; with the refresh token revoked in SQL:
   the client re-runs discovery (M-04).
4. As an ESS user: `rest_get_pim_employees` returns only what the web UI shows
   them; `limit: 0` is refused (M-01).
5. Disable the OAuth client in Admin: the next call fails within the same
   minute. Re-enable: it works again without re-authorising.
6. Send a mobile-app token to `/api/mcp`: refused. Send a cookie session: 401
   with `WWW-Authenticate`.
7. Follow a crafted consent link while signed in: refused (M-07).
8. `--testsuite Mcp` and the OAuth plugin tests on **PHP 7.4**, then
   `php-cs-fix`, then `git status --porcelain` empty.
9. SETUP.md reads true against what was just observed (M-14). Help article
   with the Claude redirect URIs published, with the verification date.

## 6. Order and size

| Step | Items | Size |
|---|---|---|
| 1 | M-02, M-05, M-11, M-13 | half a day together |
| 2 | M-01, M-03 (share the new `ToolsCallHandlerTest`) | one day |
| 3 | M-04, M-06 | half a day |
| 4 | M-07 (after sign-off) | half a day |
| 5 | M-08, M-09, M-10, M-12, M-14 | one day |
| 6 | Release checklist, section 5 | one day |

Roughly four to five developer days to a releasable read-only MVP, none of it
new tables, new screens or new dependencies.
