# Connecting an MCP client to OrangeHRM

Prototype status, 2026-09-14. Read-only: four curated tools, no writes. See
[`../README.md`](../README.md) for what is and is not implemented.

## Before you enable this

Connecting an AI assistant gives it **the full API authority of whoever logs in**.
There are no granular OAuth scopes in OrangeHRM Starter today, so a connected
assistant can read anything that user can read through the web UI or REST API.

What limits exposure:

- Every tool call runs as the authenticated user and passes the same
  `ohrm_api_permission` check the REST API enforces. An ESS user sees ESS data.
- Only read tools exist in this prototype. No write path is implemented.
- Every call writes a row to `ohrm_mcp_audit_log` with user, session, tool,
  endpoint, status, latency and a SHA-256 digest of the arguments. Raw argument
  values are never stored.
- `mcp.read_only_mode` defaults to true.

What does not limit it:

- No per-tool consent, and no way to grant an assistant less authority than the
  user has.
- **Token audience is not checked.** Any valid OrangeHRM OAuth token is accepted
  on the MCP endpoint, including one issued to a different application such as
  the mobile app. The MCP specification requires servers to reject tokens not
  issued for them; this server does not yet. A token still carries only its own
  user's authority, so this is not privilege escalation, but a token granted to
  another app can be replayed here. Tracked in `../README.md` section 4.13.

## 1. Requirements

- OrangeHRM 5.10 (this branch). Confirm with `php bin/console mcp:doctor`.
- The `mcp` module enabled. It ships enabled.
- **TLS.** The server refuses MCP requests over plain HTTP unless the host is
  localhost. Behind a reverse proxy, forward `X-Forwarded-Proto`.

## 2. Register an OAuth client

One client per AI product. Admin, then Configuration, then Register OAuth Client:

| Field | Value |
|---|---|
| Name | the product, e.g. `Claude Code` |
| Redirect URI | the product's callback (see below) |
| Confidential | **off** |

Leaving Confidential off makes it a public client, which forces PKCE and issues
no secret. Save, then copy the generated `client_id`.

### Which redirect URI

This is the part that is not yet settled. Vendors do not publish these as setup
constants because the MCP ecosystem assumes dynamic client registration, which
OrangeHRM does not implement. Capture the real value instead:

1. Register a throwaway client with any placeholder redirect URI.
2. Start the connection from the AI product.
3. When the browser lands on the OrangeHRM authorize or error page, read
   `redirect_uri` out of the address bar.
4. Put that exact value in the real client.

Matching rules, verified in league/oauth2-server 8.4.2:

| URI shape | Matching |
|---|---|
| `http://127.0.0.1:PORT/path`, `http://[::1]:PORT/path` | port ignored, **everything else exact** |
| anything else, **including `http://localhost:PORT/path`** | exact string match |

Only the numeric loopback hosts are treated as loopback. A client using the
literal spelling `localhost` with a rotating port cannot be registered by hand.

## 3. Connect

```bash
claude mcp add --transport http orangehrm \
  https://<your-host>/<subpath>/web/index.php/api/mcp
```

On this dev box the URL is:

```
https://php74/orangehrm_57/web/index.php/api/mcp
```

The client fetches the discovery documents, opens a browser at the OrangeHRM
login, you sign in **as yourself**, approve the consent screen, and the client
stores the token. Access tokens last 30 minutes and refresh automatically;
refresh tokens last a month.

Claude's web connectors cannot reach a local host. They run on Anthropic's
servers and need a publicly resolvable HTTPS URL with a real certificate.

## 4. Verify

```
Ask the assistant: "who am I in OrangeHRM?"
```

It should call `mcp_get_current_employee` and answer with your own employee
record. Then confirm the audit trail:

```sql
SELECT tool_name, response_status, latency_ms, created_at
FROM ohrm_mcp_audit_log ORDER BY id DESC LIMIT 5;
```

## Available tools

| Tool | Returns |
|---|---|
| `mcp_get_current_employee` | the authenticated user's employee record |
| `mcp_list_employees` | employees visible to you, optional name filter |
| `mcp_list_job_titles` | job titles with ids |
| `mcp_list_leave_types` | leave types with ids |

`tools/list` only shows tools your role can actually invoke, so an ESS user sees
fewer than an admin.

## Troubleshooting

**401 on every call.** Expected before authorizing. Confirm the pointer is
present:

```bash
curl -sk -i -X POST https://<host>/<subpath>/web/index.php/api/mcp \
  -H 'Content-Type: application/json' -d '{"jsonrpc":"2.0","id":1,"method":"ping"}' \
  | grep -i www-authenticate
```

**Error -32099, insecure transport.** Not TLS and not localhost. Terminate TLS at
the proxy and forward `X-Forwarded-Proto: https`.

**The client authenticates against the wrong server, or the flow dies after the
consent screen.** Check what actually owns the host root. A client follows three
hops, and the third goes to the *host root*, not your app path:

```bash
# 1. the pointer your server hands out
curl -sk -i -X POST https://<host>/<subpath>/web/index.php/api/mcp \
  -H 'Content-Type: application/json' -d '{}' | grep -i www-authenticate

# 2. that document names the authorization server
curl -sk https://<host>/<subpath>/web/index.php/.well-known/oauth-protected-resource

# 3. the client fetches THIS, at the host root
curl -sk https://<host>/.well-known/oauth-authorization-server
```

If step 3 returns another application's document, or a 404, discovery cannot
complete no matter how healthy the MCP endpoint is. RFC 8414 puts
authorization-server metadata at the host root and clients strip the path when
building that URL, so **one hostname serves one OrangeHRM instance's MCP
endpoint**. Two instances cannot share a hostname. On a subpath install, route
host-root `/.well-known/oauth-*` to the application.

**Discovery 404.** Both documents must resolve:

```bash
curl -sk https://<host>/<subpath>/web/index.php/.well-known/oauth-protected-resource
curl -sk https://<host>/<subpath>/web/index.php/.well-known/oauth-authorization-server
```

Clients strip the path when building the well-known URL, so on a subpath install
the host root must also serve these. Add a proxy rewrite from `/.well-known/*` to
the app path.

**Error -32000, module disabled.** Enable `mcp` under Admin, Configuration,
Modules.

**invalid_request from the token endpoint, mentioning the code verifier.** PKCE
verifiers must be 43 to 128 characters from `[A-Za-z0-9-._~]`.

**Empty tool results.** Usually correct. A fresh install has no job titles or
leave types. Check with `SELECT COUNT(*) FROM ohrm_job_title;`.
