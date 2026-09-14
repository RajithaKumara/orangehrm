# OrangeHRM MCP Plugin — development handoff

_Last updated 2026-09-14. This file is the entry point for anyone (human or coding
agent) continuing the MCP server plugin. It replaces the lost planning session:
everything needed is either in this file or in the on-disk documents it points to.
Update the "Where things stand" section whenever a story lands._

_Decision review 2026-09-14 with the architect: D1, D2 and D5 settled; D3 reopened
and re-scoped; a new decision D7 covers the OAuth client registration model. See
section 4.12 and the table in section 8._

## 0. How to use this document

1. Read sections 1 to 5 once per session. They are short on purpose.
2. Pick the next unchecked item in the development plan (section 5).
3. Read that story's acceptance criteria in
   `_bmad-output/planning-artifacts/epics.md` (search for the story title).
4. Apply the corrections in section 4 wherever they touch the story. **The
   corrections win over the older planning documents.**
5. Implement with the recipes in section 7 and the checklist in section 6.
6. Tick the item here, add a line to `_bmad-output/implementation-artifacts/sprint-status.yaml`,
   commit.

Codebase rules live in `_bmad-output/project-context.md` (57 rules: PHP 7.4 floor,
DI through traits, declared validation, GPL header, php-cs-fix, test conventions).
Follow them without being asked.

## 1. Where things stand

**Branch:** `mcp`. **Base:** OrangeHRM **5.9** (`upstream/main` = `upstream/5.9` =
tag `v5.9` = `56e23b3b0`), merged 2026-09-14 in `0350562ec`. MCP schema targets the
**5.10** release. `build/build.xml` reads `5.10`.

Upstream 5.9 brought two things worth knowing about: a new
`orangehrmWorkspaceNotificationsPlugin` (its `V5_9_0` migration is why ours moved to
`V5_10_0`, see 4.7), and an agent-instruction system — `AGENTS.md` plus `.agents/`
with 28 skills, bridged into `.claude/skills/` by `.agents/SETUP.md`. That bridge has
**not** been run here because `.claude/skills/` holds the BMAD set; upstream's
`.gitignore` now ignores `.claude/`, so the two stay separate.

**Done — Epic 1, Plugin Foundation & Operability** (8 stories, all marked `done` in
`sprint-status.yaml` as of 2026-09-14; suite green on PHP 7.4 and 8.5):

| Area | Files | Notes |
|---|---|---|
| Plugin bootstrap + DI | `config/McpPluginConfiguration.php` | registers `Services::MCP_CONFIG_SERVICE`, `Services::MCP_OPERATOR_LOGGER`, the status-change subscriber, and the `mcp:doctor` command |
| Route + placeholder controller | `config/routes.yaml`, `Controller/McpController.php` | `POST /mcp`; returns JSON-RPC `-32601` for everything, `-32000` when module disabled. Implements `PublicControllerInterface` (correct, see 4.1) |
| Schema | `installer/Migration/V5_10_0/Migration.php` | creates `ohrm_mcp_audit_log` (indexed, FK to `ohrm_user`) and inserts the `mcp` row in `ohrm_module` with status 1. Renamed from `V5_9_0` on 2026-09-14, see 4.7 |
| Settings | `Service/McpConfigurationService.php` + trait | `mcp.read_only_mode` (default **true**), per-session / per-user rate limits (60 / 120 per min), daily cap (off), concurrent sessions (5), audit retention (0 = keep), redaction policy (`digest_only`). **Nothing enforces these yet.** |
| Module gate | `Traits/McpEnabledTrait.php`, `Subscriber/McpModuleStatusChangeSubscriber.php` | subscriber is a deliberate no-op seam for cache busting |
| Operator log | `Service/McpOperatorLogger.php` + trait | structured `mcp.tool_called` events; tests assert no token-shaped strings ever reach the log |
| Self-test | `Command/McpDoctorCommand.php` | `php bin/console mcp:doctor [--host=]` checks module, OAuth dependency (both encryption keys + `ohrm_oauth2_client` present), audit write path (rolled back), TLS; OpenAPI check is a stub pending Story 2.5. Verified 4 PASS / 1 SKIP on a real 5.10 instance |
| Tests | `test/` (5 files, ~700 lines) | testsuite `Mcp` is registered in `phpunit.xml`; requires `instance:create-test-db` first |

**Not started:** any MCP protocol behaviour. `initialize`, `tools/list`,
`tools/call` do not exist. Epics 2 to 7 are all backlog.

**Reference material:** a separate proof of concept (sibling checkout `ohrm-57`,
branch `mcp`) implemented the protocol surface end to end with one tool and OAuth
discovery metadata. Its code is copied verbatim with annotations into
[`docs/POC-REFERENCE.md`](docs/POC-REFERENCE.md). Use it as a starting point for
Epic 2; do not paste it in unchanged (it has six known defects, listed there).

## 2. Background documents (all on disk, no session needed)

| Document | Use it for |
|---|---|
| `_bmad-output/planning-artifacts/prd.md` | goals, user journeys, functional requirements FR1..FR52, NFRs |
| `_bmad-output/planning-artifacts/architecture.md` | the locked design. Key sections: "Authentication & Security", "API & Communication Patterns", "Tool Surface Architecture", "Permission Resolution via Native OrangeHRM Chain", "Resource Subsystem", "Prompt Subsystem" |
| `_bmad-output/planning-artifacts/epics.md` | 7 epics, 66 stories, each with Given/When/Then acceptance criteria. **The story text is the spec.** |
| `_bmad-output/implementation-artifacts/spec-1-*.md` | how Epic 1 stories were specified and reviewed (format reference only) |
| `_bmad-output/implementation-artifacts/deferred-work.md` | six findings parked during Epic 1 (D1..D6). D1 and D2 are addressed or superseded below |
| `_bmad-output/implementation-artifacts/sprint-status.yaml` | story status ledger; keep it current |
| `_bmad-output/project-context.md` | codebase rules |

The `_bmad/` folder holds only the tool skeleton; the BMAD workflows themselves are
not installed. Work from `epics.md` directly.

## 3. Locked decisions (keep these)

From `architecture.md`, plus the ones inherited from the POC. Do not reopen
without the architect.

1. **In-product plugin**, `src/plugins/orangehrmMcpPlugin/`, namespace `OrangeHRM\Mcp\`, wired like every other plugin. No separate front controller, no daemon, no Node sidecar.
2. **JSON-RPC 2.0 over Streamable HTTP.** One HTTP request per JSON-RPC message. No SSE, no held PHP-FPM workers, nothing a default LAMP/LEMP box cannot run. Hand-rolled envelope handling; no MCP library.
3. **Authentication is the existing OAuth 2 server** (`orangehrmCoreOAuthPlugin`, `league/oauth2-server` 8.4): authorization-code + refresh grants, PKCE enforced for public clients. Bearer token on every request. No service account, no new token format, no anonymous surface (even `initialize` needs a token).
4. **Authorization is the existing chain**, never a parallel one: route `_api` class → `ohrm_api_permission` data group → `ohrm_user_role_data_group` CRUD grants → `UserRoleManager` (including derived roles Supervisor, HiringManager, Interviewer, Approver). `tools/list` is projected through a per-session `PermissionProfile`; `tools/call` re-checks at dispatch (defense in depth).
5. **Tools call the REST layer in-process**, never over HTTP loopback and never by instantiating API classes directly (see 4.2 for why the second half matters).
6. **Generic OpenAPI-driven router** exposes every `/api/v2` endpoint as a tool with deterministic names; a YAML overlay fixes descriptions; PHP classes exist only for orchestration tools (identity primitive, propose/confirm pairs, multi-call compositions, `mcp_discover_tools`).
7. **Safety controls are part of v1:** read-only mode (default on) blocks every write tool; propose/confirm for destructive and bulk writes; per-session and per-user rate limits; synchronous audit write on every call (call fails if the audit row cannot be written); parameters stored as SHA-256 digest plus redacted summary, never raw.
8. **Three MCP primitives in v1:** tools, resources (`orangehrm://` URIs for master data and per-user data), prompts (HR workflow templates). `resources/subscribe` deferred.
9. **Discovery must work out of the box** (from POC): RFC 9728 protected-resource metadata, RFC 8414 authorization-server metadata, `WWW-Authenticate` with `resource_metadata` on 401. Issuer at the host origin, endpoints at the app origin, `X-Forwarded-Proto` / `X-Forwarded-Host` honored, so subpath installs behind a reverse proxy work.
10. **Documentation is a deliverable.** Setup guide, "before you enable this" advisory, two client recipes, `mcp:doctor`.

## 4. Corrections and additions to the plan (found 2026-09-13)

Each item names the stories it changes.

### 4.1 Bearer auth already happens before the controller — Story 2.2 shrinks
`orangehrmCoreOAuthPlugin/Subscriber/OAuthSubscriber` runs on every request
(`KernelEvents::REQUEST`, priority 98000). If the session is not authenticated and
an `Authorization` header is present, it validates the bearer token with
`BearerTokenValidator` and marks the auth user as authenticated. An invalid token
already produces a 401 JSON error. So by the time `McpController::handle` runs,
`$this->getAuthUser()->isAuthenticated()` is the whole check. Story 2.2 becomes:
(a) if not authenticated, return the 401 contract from 4.4; (b) TLS check;
(c) never log the token (tripwire tests already exist). Do not re-validate the
token in the plugin.

### 4.2 In-process dispatch must replicate two kernel checks — Story 2.9, 4.5..4.9
Two protections live in kernel event subscribers, so an in-process call skips them:

- `ApiAuthorizationSubscriber` (CONTROLLER event, only for `AbstractRestController`) is the data-group RBAC check: `getUserRoleManager()->getApiPermissions($apiClass)->canRead()/canCreate()/canUpdate()/canDelete()` by HTTP verb.
- `ModuleNotAvailableSubscriber` (REQUEST event) forbids `/api/v2/<module>` when the module is disabled.

Request validation, on the other hand, runs inside
`AbstractRestController::handle()`, so calling `GenericRestController::handle()`
keeps it, while calling `new SomeAPI($request)->getOne()` loses it. The POC did the
latter. Rule: **every tool dispatch goes through the recipe in 7.1**, which does
the RBAC check, the disabled-module check, then `GenericRestController::handle()`.
Add a test that greps the plugin for `new \w+API(` outside `test/`.

### 4.3 Route path `/mcp` collides with the module gate — Story 1.4 change
`ModuleNotAvailableSubscriber` forwards any path starting with `/<module name>`
to the HTML "module disabled" page when that module is disabled. With the module
named `mcp` and the route at `/mcp`, disabling the module hits that page before
`McpController` ever runs, so the JSON `-32000 module_disabled` envelope the
controller builds (and its test asserts) is unreachable in production.
**DECIDED 2026-09-14 (D1): move the route to `/api/mcp`.** The POC's path; also
keeps the MCP endpoint under the prefix most reverse-proxy configs already route
to PHP. Update `routes.yaml`, `McpControllerTest`, the discovery metadata
`resource` value, and the Story 1.4 spec change log.

### 4.4 Discovery and the 401 contract are missing from the plan — new Stories 2.13, 2.14
Neither `prd.md` nor `architecture.md` mentions `.well-known`, RFC 9728/8414,
`WWW-Authenticate`, or subpath installs. Add:

- **Story 2.13 — OAuth discovery metadata.** Port `MetadataController` from `docs/POC-REFERENCE.md`. Routes: `GET /.well-known/oauth-protected-resource`, `GET /.well-known/oauth-protected-resource/api/mcp` (RFC 9728 path-suffixed form), `GET /.well-known/oauth-authorization-server`. Controller implements `PublicControllerInterface`. `resource` = app origin + `/api/mcp`; `issuer` and `authorization_servers[]` = host origin; endpoints = app origin + `/oauth2/authorize|token`. Honor `X-Forwarded-*`. Document the front-proxy rewrite needed when OrangeHRM is mounted on a subpath (host root must serve the two well-known paths).
- **Story 2.14 — Unauthenticated response contract.** `401`, `WWW-Authenticate: Bearer realm="OrangeHRM MCP", resource_metadata="<app origin>/.well-known/oauth-protected-resource"`, JSON-RPC error body. Streamable HTTP details: notifications and responses-only bodies return `202 Accepted` with empty body (not 204); `GET /api/mcp` returns `405` (no server-initiated stream in v1); `DELETE /api/mcp` terminates the session (once 2.3 exists), else `405`; JSON-RPC batch arrays are rejected with `-32600`.

### 4.5 Dynamic client registration is missing — new Story 2.15 (decision D3)
Claude Desktop, claude.ai connectors and Claude Code default to RFC 7591 dynamic
client registration. Without it every self-hoster must create an OAuth client in
Admin and paste a client id into each client that even allows it.
`league/oauth2-server` has no DCR. Add `POST /oauth2/register` in this plugin:
creates a **public** (non-confidential) `OAuthClient` row from `client_name` and
`redirect_uris` (https or `http://localhost`/`127.0.0.1` loopback only), returns
`client_id` and the echoed metadata, no secret. Gate with a new config key
`mcp.dynamic_client_registration` and advertise `registration_endpoint` in the
RFC 8414 document only when enabled. Until it lands, the setup guide documents
manual registration via Admin → Configuration → Register OAuth Client.

### 4.6 Protocol version and headers — Stories 2.1, 2.3, 2.4
Pin `2025-06-18` (the revision that introduced protected-resource metadata and
Streamable HTTP as the default). In `initialize`, if the client's requested version
is one of `2025-06-18`, `2025-03-26`, `2024-11-05`, echo it; otherwise return
`2025-06-18` and let the client decide. After `initialize`, read the
`MCP-Protocol-Version` header on every request. Session binding uses the
`Mcp-Session-Id` **response header** on `initialize` and the same **request
header** afterwards; that is the spec mechanism and replaces the plan's "session ID
in `serverInfo`". Server name `OrangeHRM-MCP`.

### 4.7 Migration collision with upstream 5.9 — DONE 2026-09-14
Upstream OrangeHRM 5.9 ships its own `installer/Migration/V5_9_0/` (workspace
notifications) and maps `'5.9'` to it in `installer/Util/AppSetupUtility.php`.
Merging 5.9 into this branch would collide on both.

**Resolved by renaming ours out of the way _before_ the merge**, so the merge sees
no conflicting folder at all. MCP schema now targets a 5.10 release:

- `installer/Migration/V5_9_0/` → `installer/Migration/V5_10_0/` (via `git mv`)
- namespace → `OrangeHRM\Installer\Migration\V5_10_0`
- `getVersion()` → `'5.10.0'`
- `MIGRATIONS_MAP` entry → `'5.10' => \OrangeHRM\Installer\Migration\V5_10_0\Migration::class`
- **`src/lib/config/Config.php` → `PRODUCT_VERSION = '5.10'`**

**There are three version locations, not two.** `PRODUCT_VERSION` is the one that
decides whether the migration ever runs, and missing it fails silently:
`runMigrations('3.3.3', Config::PRODUCT_VERSION)` is the target for both
`installer/cli_install.php` and `installer/Command/UpgradeCommand`, and
`getVersionsInRange()` stops at the map key equal to that value. Left at `'5.9'`,
the range ended one entry early and `V5_10_0` was skipped with no error at all —
`instance:reinstall` produced a database stamped `5.9.0` carrying upstream's
workspace-notification tables but no `ohrm_mcp_audit_log` and no `mcp` row in
`ohrm_module`. `PRODUCT_VERSION` must always equal the last `MIGRATIONS_MAP` key.
It also feeds the About screen, system check, registration payload and the Vue
product-version prop.

Two things to know about that map. It is traversed by `getVersionsInRange()` in
**array insertion order, never sorted**, so the string-ordering trap where `'5.10'`
sorts before `'5.9'` does not apply. The existing `'4.10'` entry sitting after
`'4.9'` is the precedent. But it does mean **`'5.10'` must remain the last entry**:
when upstream's `'5.9'` arrives in the merge it has to land *above* ours, not below.
That is the one thing to get right when resolving the merge.

`build/build.xml` is deliberately **not** bumped yet. It currently reads `5.8.1`.
Upstream 5.9 will set it to `5.9.0`, so setting `5.10.0` now would manufacture a
conflict for no reason. Bump it to `5.10.0` after the merge lands.

### 4.8 Module row inserted as enabled — decision D2
The migration inserts `ohrm_module.mcp` with `status = 1`.

**DECIDED 2026-09-14 (D2): keep `status = 1`. No change to the migration.**
Architect's rationale: the endpoint is unreachable without an authenticated
bearer token, so an enabled module exposes no data on its own. Reaching it also
requires an OAuth client that an admin must create deliberately (4.12), which is
the real gate. Read-only mode stays on by default as the second layer.

Consequence for Story 7.1: the "before you enable this" advisory must be written
for a module that is already on, so it reads as "before you register an OAuth
client", not "before you enable the module". The registration step is the moment
of informed consent, not the module toggle.

Still worth verifying: that the Admin → Configuration → Module Configuration
screen lists the new row, so an admin can turn it off. If that screen renders a
fixed list, expose the toggle through the MCP settings instead.

### 4.9 Test namespace not in autoload-dev — Phase 0
`src/composer.json` `autoload-dev.psr-4` lacks
`"OrangeHRM\\Tests\\Mcp\\": "./plugins/orangehrmMcpPlugin/test"`. Tests still run
because `phpunit.xml` loads the directory, but any shared test helper class under
`test/` would fail to autoload. Add the entry (deferred-work D2).

### 4.10 The OpenAPI artifact is not shipped — Stories 2.5, 7.x (decision D4)
`php devTools/core/console.php generate-open-api-doc` writes `build/orangehrm-v2.json`.
It is a dev-tool output, not part of the release zip or Docker image, and
`devTools/` needs its own `composer install`. Decide: ship the generated file in
release artifacts (preferred), or generate it at install time. `mcp:doctor` must
fail clearly when it is absent. Fallback worth keeping in mind: the
`ToolPermissionMap` is already built from every plugin's `routes.yaml` plus the
`_api` class; the same source could seed tool names and verbs if the OpenAPI file
is unavailable, with schemas degraded to "any object".

### 4.11 Config keys exist but nothing reads them — Epic 3 ordering
`mcp.read_only_mode` defaults to true yet no code enforces it. Story 3.2 (read-only
toggle) should be pulled forward to the end of Phase A so the first shippable
build is read-only by construction.

### 4.14 Self-hosted deployment blockers — measured 2026-09-14

Three things stand between a working plugin and a self-hoster being able to
connect. None is a code defect; all three must be solved before a help article
can promise the feature works.

**1. `.well-known` must be served from the host root.**
MCP clients strip the path when building the RFC 9728 / RFC 8414 URLs. Measured
on this subpath install:

| URL | Result |
|---|---|
| `https://host/orangehrm_57/web/index.php/.well-known/oauth-protected-resource` | 200 |
| `https://host/.well-known/oauth-protected-resource` | **404** |

The route exists, but only under the app path. Anything mounted at a subpath —
which is the common self-hosted shape — needs a reverse-proxy rewrite from
`/.well-known/oauth-*` to the app path. That is nginx or Apache configuration,
not something an HR admin can do.

**Worse: these paths collide per host.** On this very box
`https://host/.well-known/oauth-authorization-server` already returns 200, served
by a *different* OrangeHRM instance under `/MCP/orangehrm/`, advertising a stale
ngrok issuer. Two instances on one hostname cannot both own the host-root
well-known paths. Multi-instance hosts need one hostname per instance.

**2. TLS must be real, not self-signed.**
The server enforces TLS off localhost, which is correct. But MCP clients validate
certificates, so a self-signed cert fails at the client with no useful message.
Every curl in our testing needed `-k`. Self-hosters need a real certificate.

**3. Web connectors need public reachability.**
Desktop and CLI clients run on the user's machine and can reach a private host.
Claude's web connectors run on Anthropic's infrastructure and cannot. A
self-hoster on a private network can support the former and not the latter, and
the documentation must say which is which rather than implying both work.

**Consequence for documentation.** A single "configure MCP" article aimed at
non-technical HR admins is not achievable. The work splits in two: an IT
prerequisites guide (TLS, well-known rewrite, hostname, reachability) and an HR
admin guide (register the client, choose who may connect, verify). The HR admin
guide can only be written against a config screen that does not exist yet — see
the Growth note below.

### 4.13 Token audience is not validated — known gap, found 2026-09-14

**Verified:** a bearer token minted for the `orangehrm_mobile_app` OAuth client is
accepted by `/api/mcp` and returns data. Any valid OrangeHRM token works on the
MCP endpoint regardless of which client it was issued to.

The MCP specification treats this as a hard requirement: a server must verify the
access token was issued *for it*, and must not accept a token minted for another
audience. The spec calls this out specifically to prevent the confused-deputy
problem, where a token obtained by one application is replayed against another.

Why it happens here: `OAuthSubscriber` validates that a token is valid and
resolves its user, which is all `isAuthenticated()` reports. Nothing in the chain
records or checks which client the token was issued to, and
`league/oauth2-server` has no audience concept in this configuration.

Severity in context: every such token still belongs to a real OrangeHRM user and
carries exactly that user's authority, so this is not privilege escalation. The
risk is that a token a user granted to a *different* application can be replayed
against the AI surface without their knowledge, which is precisely the consent
boundary MCP's audience rule exists to protect.

Fix direction, for the story that closes it: persist the issuing `client_id`
alongside the access token, expose it through the auth layer, and have
`McpController` reject tokens whose client is not registered for MCP use. That
likely means a small schema addition in `orangehrmCoreOAuthPlugin` plus a config
key listing MCP-approved client ids. Needs architect sign-off because it touches
the shared OAuth plugin.

Until then the setup guide must say plainly that any OrangeHRM OAuth token works
on the MCP endpoint.

### 4.15 Discovery chain is broken on the shared dev box — measured 2026-09-14

**No MCP client can complete OAuth discovery against this instance today.** Not
a code defect; a host-level routing collision. Traced hop by hop:

| Hop | Result |
|---|---|
| 401 on `/api/mcp` names `resource_metadata` | full app path, resolves correctly |
| that document's `authorization_servers[0]` | `https://php74` (host origin) |
| client fetches `https://php74/.well-known/oauth-authorization-server` | **a stale static file for a different OrangeHRM instance**, advertising a dead ngrok issuer under `/MCP/orangehrm/` |

The client is then sent to token and authorize endpoints that do not exist, and
the flow dies before a token is issued.

**Why the host origin at all.** RFC 8414 places authorization-server metadata at
the *host root*, and MCP clients strip the path when building that URL — which is
exactly why the POC put the issuer at the host origin (locked decision 9). That
is correct behaviour. It only fails here because something else already owns
`https://php74`'s well-known namespace.

The offending file is `/home/administrator/Documents/OHRM/.well-known/oauth-authorization-server`,
which is outside this project: `/var/www` is bind-mounted from the parent
directory shared by every instance on the box.

**Three ways out, none applied — architect chose to document only (2026-09-14):**

1. *nginx route.* A `location ^~ /.well-known/oauth-` block fastcgi-passing to
   this instance's `index.php`. Correct and dynamic: the controller already
   honours `X-Forwarded-*`, so a tunnelled origin is advertised automatically.
   Costs: the nginx config is baked into the container rather than bind-mounted,
   so it is lost on rebuild, and it takes host-root discovery away from the other
   instances.
2. *Replace the static file.* One line, but hardcoded, so it goes stale the next
   time a tunnel URL changes — which is precisely how the current stale file came
   to exist.
3. *Dedicated hostname.* A vhost whose document root is this instance, so its
   host root is genuinely ours. Cleanest, touches nothing shared, most setup.

**Implication for any real deployment.** One hostname serves one OrangeHRM
instance's MCP endpoint. Two instances cannot share a hostname, because they
cannot both own the host-root well-known paths. This belongs in the IT
prerequisites guide (4.14) as a hard requirement, not a recommendation.

### 4.12 OAuth client registration model — decision D7 (new, 2026-09-14)

**DECIDED: one OAuth client row per AI product, registered by hand by the IT
admin. No change to `orangehrmCoreOAuthPlugin`. Supported products and their
redirect URIs are published in a help article, not shipped in code.**

This closes a design question that was not in the PRD or architecture. Recording
the reasoning because the alternatives look attractive and were rejected on
evidence.

**How the flow actually works** (traced through the code, not assumed). The OAuth
client identifies the *application*, never the person:

1. IT admin registers one public client per product at Admin → Configuration →
   Register OAuth Client. Confidential switch **off**, which makes PKCE mandatory.
   No secret is generated. The admin publishes the MCP URL and the `client_id`.
2. Each employee adds the server in their own AI client. On the first call the
   server answers 401 with discovery pointers (Stories 2.13, 2.14).
3. The AI client opens `/oauth2/authorize`. `AuthorizationController::preRender`
   sees no session and forwards to the login page, so **the employee signs in with
   their own OrangeHRM credentials**. The admin is never involved.
4. The consent screen names the client and asks allow/deny.
5. `OAuth2/AuthorizationController` binds the auth code to the logged-in user via
   `UserEntity::createFromEntity($this->getUserRoleManager()->getUser())`.
6. Token exchange with the PKCE verifier. Every MCP call then runs as that
   employee, under that employee's existing RBAC.

One registration therefore serves every employee. There is no per-user setup and
no admin involvement per user. An HR admin connects identically to an ESS user;
only their role grants differ.

Current TTLs, from `installer/Migration/V5_4_0/Migration.php`: auth code 5 min,
access token 30 min, refresh token 1 month.

**Why one row per product, not one shared row with several redirect URIs.**
A shared row means one `client_id` and one `enabled` flag for every vendor, which
costs three things that matter for an HR system: an admin cannot revoke one
product without cutting off all of them; the audit log's client id can no longer
distinguish vendors, leaving only the self-reported and spoofable `clientInfo`
from the MCP handshake (weakens FR31 and Epic 6); and the consent screen can only
show one generic name instead of the vendor actually asking.

**Why no multi-redirect-URI support.** One row per product already works with the
existing single-column schema, so multi-URI is not what unblocks multi-product
support. It only helps when a single product has several valid callbacks (IPv4
plus IPv6 loopback, a path change across versions, a domain migration). The
library already supports it, `ClientEntityInterface::getRedirectUri()` is
documented as `string|string[]` and `AuthCodeGrant` branches on arrays, so this
stays cheap to add later if a real case appears. Not v1.

Note if it is ever added: with two or more URIs registered, the library *requires*
the authorize request to send `redirect_uri` explicitly. With exactly one it may
be omitted. Adding a second URI to an existing client can therefore break an
integration that relied on omitting it.

**Redirect URI matching, verified in `RedirectUriValidator` (league 8.4.2).**
This drives what the help article must say:

| Redirect URI shape | Matching |
|---|---|
| `http://127.0.0.1:PORT/path` or `http://[::1]:PORT/path` | port dropped, **everything else exact** |
| anything else, **including `http://localhost:PORT/path`** | exact string match |

`isLoopbackUri()` accepts only the numeric hosts. The literal spelling `localhost`
is *not* treated as loopback and gets strict matching including the port. So a
desktop client using the numeric form can be hand-registered once as
`http://127.0.0.1/<path>` and will match any ephemeral port, but the path must be
character-exact. A desktop client using the `localhost` spelling with a rotating
port cannot practically be hand-registered at all.

**Why the redirect URIs go in a help article and not in code.** They are
vendor-controlled and change without notice, while OrangeHRM ships on a slow
cadence, so a value baked into a migration strands every self-hoster until the
next release. A help article is correctable independently. Requirements for that
article (Story 7.1):

- One row per product: product name, redirect URI, **and the date it was verified**.
- Only products confirmed to accept a pasted `client_id` may be listed at all.
- A footer describing how to capture the current value: register a throwaway
  client with a placeholder URI, start the connection from the product, and read
  `redirect_uri` out of the browser address bar on the authorize or error page.
- A named owner and a re-verification cadence, since these values drift.

**What is still unknown, and the test that resolves it.** Vendors do not publish
these callbacks as setup constants because the ecosystem assumes RFC 7591 dynamic
registration. So it is not yet known whether the desktop clients expose a
`client_id` field at all. Run the capture procedure above against Claude first,
during Phase A once 2.13 and 2.14 exist. That one trip answers three things:
the exact redirect URI, whether the numeric or `localhost` loopback spelling is
used, and whether manual registration is even possible for that product.

**This re-scopes D3.** Dynamic client registration was previously recommended for
v1, then deferred on the argument that manual registration is a cheap two-minute
task. That argument is weaker than it looked: if vendors do not document these
values, manual registration is a scavenger hunt per product. D3 is therefore not
"deferred to Growth" but **open, pending the Phase A test**. Build the manual path
first, because it is needed regardless. If the test shows a target product refuses
a pasted client id, Story 2.15 comes back into v1 and most of the redirect-URI
work becomes irrelevant for that product.

## 5. Development plan

Ordered. Story numbers refer to `epics.md`; 2.13 to 2.15 are the additions above.
Tick items here as they land.

### Phase 0 — housekeeping (half a day)
- [x] ~~Rename our migration out of the way~~ — **DONE 2026-09-14**: `V5_9_0` → `V5_10_0`, namespace, `getVersion()` and `MIGRATIONS_MAP` all updated (4.7). Done before the merge so the folder no longer collides.
- [x] ~~Fetch upstream and merge 5.9~~ — **DONE** (`0350562ec`). Three keep-both conflicts: `MIGRATIONS_MAP`, `phpunit.xml`, `src/composer.json`. `Services.php` auto-merged. `CLAUDE.md` also collided (upstream 5.9 introduces `@AGENTS.md` + `.agents/`); reconciled to import upstream's and keep the MCP section below it.
- [x] ~~Bump `build/build.xml`~~ — **DONE** (`be9c5f9a7`), set to `5.10` using upstream's two-segment spelling.
- [x] ~~Add `OrangeHRM\Tests\Mcp\` to `autoload-dev`~~ — **DONE** (`b3bb321f0`), deferred item D2 cleared.
- [x] ~~Flip module row default~~ — **dropped, D2 decided to keep `status = 1`** (4.8). No migration change.
- [x] ~~Run the Epic 1 tests~~ — **DONE**. This surfaced a real defect, fixed in `60d2cf037`: `McpEnabledTrait` declared a constant, which is PHP 8.2+, while the project floor is 7.4. The plugin fatalled on every supported PHP version; the suite had only ever been run on 8.3. Module name consolidated onto `McpConfigurationService::MODULE_NAME`. Suite is now 26 tests / 82 assertions green on **both 7.4 and 8.5**.
- [x] ~~Mark the eight stories `done`; add 2.13, 2.14, 2.15 as `backlog`~~ — **DONE** in `sprint-status.yaml`.
- [x] ~~Bump `Config::PRODUCT_VERSION`~~ — **DONE** (`fe271afb4`). Missed in the first pass; see 4.7. Without it the 5.10 migration silently never ran.
- [x] ~~Verify against a real instance~~ — **DONE**. `instance:reinstall` now yields `instance.version` 5.10.0, 173 tables, `ohrm_mcp_audit_log` with all 12 columns / 5 indexes / `fk_mcp_audit_user`, `ohrm_module` row `mcp` status 1, and upstream's 3 workspace-notification tables intact. `mcp:doctor` reports **4 passed, 0 failed, 1 skipped**. Fixing it to get there exposed a second Epic 1 defect (`116530408`).
- [ ] **Move route to `/api/mcp`** per 4.3 (D1 approved); fix `McpControllerTest`. _Only Phase 0 item left._

### Phase A — "Claude can talk to it" (target: first demo)
Exit criteria: Claude Code connects to `https://<host>/api/mcp` through the OAuth
flow with no hand-copied client id, lists one tool, calls
`mcp_get_current_employee`, and one row lands in `ohrm_mcp_audit_log`.

- [ ] **2.1** JSON-RPC dispatcher. `Mcp/JsonRpc/{Envelope,ErrorCode,Dispatcher}.php`, one `Mcp/Method/<Name>Handler.php` per method implementing a small `MethodHandlerInterface`. Port the parse/validate/encode logic from the POC controller. Correlation id in every error `data`.
- [ ] **2.14** Unauthenticated contract + Streamable HTTP status codes (4.4). Lives in `McpController` before dispatch.
- [ ] **2.13** Discovery metadata controller + routes (4.4). `Controller/OAuthMetadataController.php`.
- [ ] **2.4** `initialize` and `ping` handlers with version negotiation and `Mcp-Session-Id` header (4.6). Capabilities: `tools` only until 5.x lands.
- [ ] **2.2 (reduced)** auth gate + TLS check + token-never-logged assertion (4.1).
- [ ] **Redirect-URI capture test (gates 2.15, blocks the help article).** Register a throwaway public client with a placeholder redirect URI, start a connection from Claude, and read `redirect_uri` out of the browser address bar on the authorize or error page. Record: the exact URI, whether the loopback host is the numeric form or the `localhost` spelling (4.12), and whether the product offers a `client_id` field at all. Repeat per product you intend to list. **Do this before writing any of 2.15 or the product table in the setup guide.**
- [ ] **2.15** Dynamic client registration (4.5) — **only if the capture test shows a target product refuses a pasted client id** (D3, see 4.12). `Controller/ClientRegistrationController.php`, config key, `registration_endpoint` advertisement. If every target product accepts a pasted client id, skip this and document manual registration instead.
- [ ] **4.5** `mcp_get_current_employee` orchestration tool wrapping `GET /api/v2/pim/myself` **through recipe 7.1**. `Mcp/Tool/OrchestrationToolInterface.php`, `Mcp/Tool/RestDispatcher.php`, `Mcp/Tool/CurrentEmployeeTool.php`. Registered in `McpPluginConfiguration`.
- [ ] **2.10 (minimal)** synchronous audit row on every `tools/call` (digest placeholder = sha256 of canonical JSON, which is already what Epic 6 wants). `Mcp/Audit/AuditWriter.php`, `Dao/McpAuditLogDao.php`, entity `entity/McpAuditLog.php`.
- [ ] **3.2** read-only mode enforcement: any tool whose verb is not GET is hidden from `tools/list` and refused in `tools/call` with `-32003` while `mcp.read_only_mode` is true (4.11).
- [ ] Setup guide draft (`docs/SETUP.md`): TLS, reverse-proxy rewrite for subpath installs, registering one OAuth client per AI product (4.12), the supported-product table with verified-on dates, first connection from Claude, `mcp:doctor`. Start the product table with the Claude web connector only, since that is the one path with real evidence behind it; add rows as the capture test confirms each additional product.

### Phase B — generic read path (Epic 2 remainder)
- [ ] **2.5** OpenAPI loader with checksum cache (decision D4 first).
- [ ] **2.6** generic router + deterministic naming + overlay file `config/tool-overlay.yaml`.
- [ ] **2.7** `PermissionProfile` resolver over `UserRoleManager` and `ohrm_api_permission`.
- [ ] **2.8** `tools/list` with visibility filtering, scope narrowing, cursor pagination (< 100 KB per page).
- [ ] **2.9** generic `tools/call` through recipe 7.1 for every routed endpoint; map REST status codes to MCP error categories.
- [ ] **2.11** structured error envelope; **2.3** session manager in Symfony cache with revocation propagation; **2.12** cooperative cancellation.
- [ ] `mcp:doctor`: OpenAPI checksum check, discovery URL check, DCR status.

### Phase C — write discipline (Epic 3)
3.1 interceptor, 3.3 dry-run, 3.4 to 3.6 propose/confirm, 3.7 to 3.9 limits and caps (the config keys already exist), 3.10, 3.11.

### Phase D — as planned
Epic 4 curated intents (≥ 30), Epic 5 resources and prompts, Epic 6 audit query /
GDPR, Epic 7 integrator experience and release gates.

## 6. Definition of done for every story

- Acceptance criteria from `epics.md` demonstrably met (unit or kernel test per criterion where feasible).
- GPL 17-line header on every new PHP file. PHP 7.4-compatible syntax. `php devTools/core/console.php php-cs-fix` clean.
- No `new <X>API(` outside tests; every tool dispatch goes through `RestDispatcher` (recipe 7.1).
- No bearer token, raw parameters, or PII in operator logs or audit rows.
- Services registered in `McpPluginConfiguration`; constants in `OrangeHRM\Framework\Services`; consumers use a `Traits/Service/*Trait`.
- Tests under `test/`, `@group Mcp`, docblock annotations; kernel tests extend `OrangeHRM\Tests\Util\KernelTestCase`.
- `sprint-status.yaml` updated; this README's checklist ticked; README "Where things stand" table updated if a new file class appears.
- Commit: `OHRM5X-<ticket>: <imperative summary>`.

## 7. Recipes

### 7.1 Dispatching a tool call through the REST pipeline (the only allowed way)

```php
use OrangeHRM\Core\Controller\Rest\V2\GenericRestController;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;          // getUserRoleManager()
use OrangeHRM\Framework\Http\Request as HttpRequest;

// $route comes from the boot-time map built from every plugin's routes.yaml:
//   method (GET|POST|PUT|DELETE), path template (/api/v2/pim/employees/{empNumber}),
//   apiClass (defaults._api), key (defaults._key, nullable)

// 1. RBAC — ApiAuthorizationSubscriber does not run for in-process calls.
$getter = ['GET' => 'canRead', 'POST' => 'canCreate', 'PUT' => 'canUpdate', 'DELETE' => 'canDelete'][$route->method];
if (!$this->getUserRoleManager()->getApiPermissions($route->apiClass)->$getter()) {
    throw new McpAuthorizationException();              // -> JSON-RPC -32001, audit outcome=forbidden
}

// 2. Disabled module — ModuleNotAvailableSubscriber does not run either.
//    Module name is the path segment after /api/v2/. Compare against
//    $this->getModuleService()->getModuleDao()->getDisabledModuleList().

// 3. Build the request exactly as the kernel would have.
$httpRequest = HttpRequest::create($route->expandPath($pathParams), $route->method);
$httpRequest->headers->set('Content-Type', 'application/json');
$httpRequest->query->add($queryParams);
$httpRequest->request->add($bodyParams);                // RequestBodySubscriber does this on real requests
$httpRequest->attributes->set('_api', $route->apiClass);
if ($route->key !== null) {
    $httpRequest->attributes->set('_key', $route->key);
}
foreach ($pathParams as $name => $value) {
    $httpRequest->attributes->set($name, $value);
}

// 4. Dispatch. Runs the endpoint's declared validation rules, the CRUD method,
//    and normalisation; converts REST exceptions to 400/403/404/422/500.
$httpResponse = (new GenericRestController())->handle($httpRequest);
$status  = $httpResponse->getStatusCode();
$payload = json_decode($httpResponse->getContent(), true);  // {"data":..,"meta":..,"rels":..} or {"error":{..}}
```

The authenticated user is whatever `OAuthSubscriber` put on the session for this
request, so the REST layer sees the same identity a direct API call would.

### 7.2 Unauthenticated response

```php
if (!$this->getAuthUser()->isAuthenticated()) {
    $response->setStatusCode(401);
    $response->headers->set('WWW-Authenticate', sprintf(
        'Bearer realm="OrangeHRM MCP", resource_metadata="%s/.well-known/oauth-protected-resource"',
        OAuthMetadataController::getOrigin($request)     // app origin, X-Forwarded-aware
    ));
    // body: JSON-RPC error, id null, code -32001, message "Unauthenticated"
}
```

### 7.3 Manual end-to-end check

```bash
HOST=https://your-host            # add /orangehrm if mounted on a subpath
curl -s  $HOST/.well-known/oauth-protected-resource | jq
curl -s  $HOST/.well-known/oauth-authorization-server | jq
curl -si -X POST $HOST/api/mcp -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"curl","version":"0"}}}'
# expect 401 + WWW-Authenticate

# Get a token: until 2.15 exists, Admin -> Configuration -> Register OAuth Client
# (public client, redirect http://localhost:1234/cb), then browser
# $HOST/oauth2/authorize?response_type=code&client_id=..&redirect_uri=..&code_challenge=..&code_challenge_method=S256&state=x
# and exchange the code at $HOST/oauth2/token with code_verifier.
TOKEN=...
H=(-H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -H 'Accept: application/json, text/event-stream')
curl -si -X POST $HOST/api/mcp "${H[@]}" -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"curl","version":"0"}}}'
# note the Mcp-Session-Id response header; send it back as a request header below
curl -s  -X POST $HOST/api/mcp "${H[@]}" -H "Mcp-Session-Id: $SID" -d '{"jsonrpc":"2.0","id":2,"method":"tools/list"}' | jq
curl -s  -X POST $HOST/api/mcp "${H[@]}" -H "Mcp-Session-Id: $SID" -d '{"jsonrpc":"2.0","id":3,"method":"tools/call","params":{"name":"mcp_get_current_employee","arguments":{}}}' | jq

# Once 2.15 exists, the real test is simply:
claude mcp add --transport http orangehrm $HOST/api/mcp
```

Everything above runs inside the dev PHP container; see the `orangehrm-os-dev-environment`
repo for `docker compose` usage. PHPUnit: `php devTools/core/console.php instance:create-test-db`
once, then `cd src && ../vendor/bin/phpunit --testsuite Mcp` (or the project's usual invocation).

## 8. Decisions needed from the architect

| # | Question | Outcome | Blocks |
|---|---|---|---|
| D1 | Route path `/mcp` or `/api/mcp` | **DECIDED 2026-09-14: `/api/mcp`** (4.3) | done |
| D2 | `ohrm_module.mcp` default status | **DECIDED 2026-09-14: keep enabled, `status = 1`.** Auth is the real gate; OAuth client registration is the deliberate step. Advisory in Story 7.1 rewrites around registration, not the module toggle (4.8) | done |
| D3 | Dynamic client registration in v1 | **OPEN, pending the Phase A capture test.** Was "yes", then "defer"; both were decided without evidence. Build the manual path first regardless; 2.15 returns to v1 only if a target product refuses a pasted client id (4.12) | Phase A |
| D4 | How the OpenAPI JSON reaches a self-hosted install | ship in release artifacts; `mcp:doctor` fails loudly if absent (4.10) | Phase B |
| D5 | JIRA ticket key for commits | **DECIDED 2026-09-14: `OHRM5X-MCP: <imperative summary>`** until a real ticket exists. Note the eight Epic 1 commits used `feat(mcp):` and are not being rewritten, so the branch history changes style at this point | done |
| D6 | Keep writing BMAD-style `spec-*.md` files per story | no; this README's plan + `epics.md` acceptance criteria are enough | — |
| D7 | OAuth client registration model | **DECIDED 2026-09-14: one hand-registered public client per AI product; no multi-redirect-URI support; no change to `orangehrmCoreOAuthPlugin`; supported products and redirect URIs published in a help article, not shipped in code** (4.12) | done |

## 9. Path glossary

| Path | What |
|---|---|
| `src/plugins/orangehrmMcpPlugin/` | this plugin (`OrangeHRM\Mcp\`) |
| `src/plugins/orangehrmMcpPlugin/Mcp/{Method,Tool,Session,Schema,Audit}/` | protocol code (currently `.gitkeep` only) |
| `src/plugins/orangehrmCoreOAuthPlugin/` | OAuth2 server: `Server/BearerTokenValidator`, `Subscriber/OAuthSubscriber`, `Controller/OAuth2/{AuthorizationController,TokenController}`, entity `OAuthClient` |
| `src/plugins/orangehrmCorePlugin/Subscriber/{ApiAuthorizationSubscriber,ModuleNotAvailableSubscriber,RequestBodySubscriber}.php` | the kernel checks recipe 7.1 replicates |
| `src/plugins/orangehrmCorePlugin/Controller/Rest/V2/{AbstractRestController,GenericRestController}.php` | REST pipeline (validation lives here) |
| `src/plugins/orangehrmCorePlugin/Authorization/` | `UserRoleManager`, `ResourcePermission`, data-group DAOs |
| `src/plugins/orangehrmAuthenticationPlugin/Subscriber/AuthenticationSubscriber.php` | why controllers must implement `PublicControllerInterface` |
| `installer/Migration/V5_10_0/Migration.php` | MCP schema, targets the 5.10 release (renamed 2026-09-14, 4.7) |
| `installer/Util/AppSetupUtility.php` | `MIGRATIONS_MAP` |
| `devTools/core/src/Command/GenerateOpenApiDocCommand.php` | writes `build/orangehrm-v2.json` |
| `docs/POC-REFERENCE.md` | the ohrm-57 proof of concept, annotated |
