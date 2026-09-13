# OrangeHRM MCP Plugin (POC)

Exposes OrangeHRM to [Model Context Protocol](https://modelcontextprotocol.io) clients
(Claude Desktop, Claude Code, other MCP-aware LLM apps) so they can call OrangeHRM
functionality as tools on behalf of the signed-in user.

Status: **proof of concept**. Handshake, tool listing, and one sample tool
(`pim_myself`) work end-to-end against Claude, using the existing web-session as the
auth surface. OAuth 2.1 metadata is advertised but the authorize/token endpoints are
not yet implemented — see [What's not done yet](#whats-not-done-yet).

## Design intent

Guidance that shapes future work on this plugin. Where the code doesn't yet
follow it, treat the intent as the direction to move in — **not** as a licence to
walk back the intent to match today's code.

1. **Reuse OrangeHRM's existing OAuth2 authorization server. Do not build a new
   one inside this plugin.** The `/oauth2/authorize` and `/oauth2/token`
   endpoints advertised by `MetadataController` must resolve to OHRM's OAuth2
   implementation. The MCP plugin's job is discovery, protocol translation, and
   tool dispatch — not identity.
2. **Bearer-token auth on `/api/mcp` must go through the existing
   `OAuthSubscriber`** (the same middleware that authorizes `/api/v2/*`). This
   is how per-user data-group permissions apply for free — a tool call runs as
   the token's user with the exact same authorization surface as a direct REST
   call, no parallel permission logic inside this plugin. The current
   session-auth check on the MCP endpoint is a POC shortcut and must be
   replaced.
3. **Tools call OHRM APIs in-process, not over internal HTTP.** A tool
   instantiates the target API class (e.g. `MyInfoAPI`), forwards arguments,
   and returns `$result->normalize()`. This preserves auth, DTOs, validation,
   and normalization while avoiding a loopback roundtrip. See
   [`PimMyselfTool`](Tool/PimMyselfTool.php) — every new tool should look like it.
4. **Wrap existing V2 API endpoints; don't hand-roll business logic in tools.**
   If the capability doesn't exist as a V2 endpoint, add the endpoint first
   (through the normal REST + service + DAO layers) and then expose it as a
   tool. Keeps a single implementation and a single authorization path.
5. **MCP client auto-discovery must work out of the box.** RFC 9728
   (protected-resource) and RFC 8414 (authorization-server) metadata is served
   so Claude Desktop / Claude Code / other clients can complete
   Authorization-Code + PKCE without a human copy-pasting client IDs or
   endpoints. Anything that breaks discovery is a defect.
6. **Support subpath deployments and reverse proxies.** OHRM is often mounted at
   e.g. `/orangehrm/` and lives behind Nginx / an LB in production. Metadata
   must advertise URLs that reach the app (baseUrl-aware) while still exposing
   an issuer at the host root, and must honor `X-Forwarded-Proto` /
   `X-Forwarded-Host`. See [OAuth 2.1 discovery — gotcha](#oauth-21-discovery--gotcha).
7. **Tool registration must be pluggable across plugins, not centralized here.**
   Long-term direction: a DI-collector-style registry so Pim, Leave, Time,
   Admin, etc. contribute their own tools from their own plugin
   configuration classes, with no edits to `orangehrmMcpPlugin`. The
   in-constructor registration in `ToolRegistry` today is a placeholder.
8. **This plugin is auto-discovered via `ConfigHelper`, same as every other
   OHRM plugin.** When it grows services, wire them through a
   `McpPluginConfiguration` (per the `services` skill) rather than
   instantiating from controllers.
9. **Documentation is a deliverable, not a follow-up.** The README, the routes
   file, and code comments explain *why* each decision was made (the OAuth
   subpath gotcha, in-process API calls, permission model) so a fresh
   contributor — human or agent — can extend the plugin without re-deriving
   the constraints.

## Endpoints

Registered by [`config/routes.yaml`](config/routes.yaml). Base URL is whatever
serves the OHRM app (e.g. `http://localhost:8080`, or a mounted subpath like
`https://example.com/orangehrm/`).

| Path | Method | Purpose |
| --- | --- | --- |
| `/api/mcp` | POST | JSON-RPC 2.0 endpoint. All MCP methods land here. |
| `/.well-known/oauth-protected-resource` | GET | RFC 9728 protected-resource metadata (points clients at the auth server). |
| `/.well-known/oauth-authorization-server` | GET | RFC 8414 authorization-server metadata (advertises OAuth endpoints, PKCE, grants). |

The MCP endpoint requires a signed-in OHRM session (any auth path the app already
supports). Unauthenticated requests return `401` with a
`WWW-Authenticate: Bearer realm=…, resource_metadata=…` header pointing at the
protected-resource doc — that's how MCP clients discover where to authenticate.

## MCP protocol support

Protocol version: `2024-11-05` (declared in
[`McpController::PROTOCOL_VERSION`](Controller/McpController.php)).

Implemented JSON-RPC methods:

- `initialize` — advertises `serverInfo` and a `tools` capability.
- `notifications/initialized`, `notifications/cancelled` — accepted, no response body (204).
- `ping` — returns `{}`.
- `tools/list` — lists every tool registered with [`ToolRegistry`](Server/ToolRegistry.php).
- `tools/call` — dispatches to the named tool; response is wrapped as MCP text
  content (`json_encode` of the tool's return value).

Errors use JSON-RPC codes: `-32700` parse, `-32600` invalid request,
`-32601` method not found, `-32602` invalid params, `-32603` internal.
Tool-level failures return `isError: true` inside the `tools/call` result rather
than a JSON-RPC error, per MCP spec.

**Not implemented:** JSON-RPC batch requests, streaming, resources, prompts,
sampling, roots, logging.

## Tool architecture

Three small pieces:

- [`ToolInterface`](Server/ToolInterface.php) — every tool implements
  `getName()`, `getDescription()`, `getInputSchema()` (JSON Schema), and
  `execute(array $arguments): array`.
- [`ToolRegistry`](Server/ToolRegistry.php) — simple map; tools are registered
  in its constructor for now. **This will move to plugin-side DI** so every
  plugin can contribute its own tools without editing this file.
- Tool implementations live in [`Tool/`](Tool/). The current one,
  [`PimMyselfTool`](Tool/PimMyselfTool.php), delegates to `MyInfoAPI` — the
  pattern most tools should follow (wrap an existing V2 API endpoint) so we
  reuse its authorization, DTOs, and normalization.

### Adding a tool

1. Create a class in `Tool/` (or in another plugin) implementing `ToolInterface`.
2. Return a JSON Schema from `getInputSchema()` — MCP clients use this for
   argument validation and (crucially) for the LLM to know how to call the tool.
3. In `execute()`, prefer instantiating an existing V2 API class and forwarding
   arguments to it, then returning `$result->normalize()`. That keeps auth,
   validation, and serialization consistent with REST.
4. Register the tool with `ToolRegistry`. (Interim: edit the registry constructor.
   Long term: DI collector.)

## OAuth 2.1 discovery — gotcha

Claude and several other MCP clients construct the
`/.well-known/oauth-*` URL by taking the **host root** of the `resource` URL
and appending the well-known path — they strip any subpath. If OHRM is mounted
at, say, `https://example.com/orangehrm/`, the client still looks for
`https://example.com/.well-known/oauth-authorization-server`, not
`https://example.com/orangehrm/.well-known/…`.

To make discovery work in both configurations, [`MetadataController`](Controller/MetadataController.php)
distinguishes two origins:

- **`hostOrigin`** — scheme + host only. Used as `issuer` and inside
  `authorization_servers[]`, because that value has to equal the well-known
  URL the client actually looks up.
- **`appOrigin`** — scheme + host + app baseUrl. Used for endpoint URLs
  (`resource`, `authorization_endpoint`, `token_endpoint`) that must reach the
  real OHRM mount.

The controller also honors `X-Forwarded-Proto` / `X-Forwarded-Host` so the
advertised URLs stay correct behind reverse proxies (typical in Docker/K8s).

**Deployment note:** on a subpath install, the host root must be able to serve
the two `.well-known/oauth-*` routes. Either mount OHRM at `/`, or add a
front-proxy rewrite from `/.well-known/oauth-*` to `<baseUrl>/.well-known/oauth-*`.

## Authentication model

Right now the MCP endpoint piggybacks on the existing OHRM web session
(`AuthUserTrait::getAuthUser()->isAuthenticated()`). Fine for testing from a
browser-authenticated context; **not** what MCP clients actually want — they
expect an OAuth 2.1 Authorization Code + PKCE flow so they can obtain a bearer
token and hit `/api/mcp` headlessly.

The metadata already advertises this flow. The endpoints themselves
(`/oauth2/authorize`, `/oauth2/token`) are the next thing to build.

## File layout

```
src/plugins/orangehrmMcpPlugin/
├── README.md                          # this file
├── config/
│   └── routes.yaml                    # MCP + well-known routes
├── Controller/
│   ├── McpController.php              # JSON-RPC dispatcher
│   └── MetadataController.php         # OAuth 2.1 discovery docs
├── Server/
│   ├── ToolInterface.php              # contract every tool implements
│   └── ToolRegistry.php               # in-memory registry (interim)
└── Tool/
    └── PimMyselfTool.php              # sample tool wrapping MyInfoAPI
```

PSR-4 namespace `OrangeHRM\Mcp\` → `./plugins/orangehrmMcpPlugin` is registered
in [`src/composer.json`](../../composer.json).

## What's not done yet

- `/oauth2/authorize` and `/oauth2/token` endpoints (metadata advertises them; no
  implementation).
- Dynamic client registration (`registration_endpoint`) — Claude Desktop expects
  it; without it, clients need pre-provisioned client IDs.
- Real bearer-token auth on `/api/mcp` (currently accepts session auth only).
- A DI-based tool collector so other plugins can register tools without editing
  `ToolRegistry`.
- More tools. `pim_myself` is a template; the goal is broad coverage of the V2
  REST surface (leave requests, time entries, PIM lookups, etc.).
- Data-group / permission checks at the tool layer (currently inherits whatever
  the wrapped API enforces).
- Tests. Nothing under `test/` yet.
- No plugin configuration class — this plugin is currently discovered only via
  its PSR-4 entry + route file. If it grows services it will need a
  `MpcPluginConfiguration` (see the `services` skill).
