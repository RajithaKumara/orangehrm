# Connecting an MCP client to OrangeHRM

Prototype status, 2026-09-14. Ships read-only: 70 read tools switched on, every
other endpoint available but switched off. See [`../README.md`](../README.md) for
what is and is not implemented.

## Before you enable this

Connecting an AI assistant gives it **the full API authority of whoever logs in**.
There are no granular OAuth scopes in OrangeHRM Starter today, so a connected
assistant can read anything that user can read through the web UI or REST API.

What limits exposure:

- Every tool call runs as the authenticated user and passes the same
  `ohrm_api_permission` check the REST API enforces. An ESS user sees ESS data.
- Reads, creates, updates and deletes are four separate switches, and only Read
  ships on. A write tool is refused unless an administrator turns on both the verb
  and the API it belongs to.
- Credentials, authentication configuration, purge and password endpoints are
  excluded in code and cannot be enabled by anyone. Endpoints that exist only to
  serve the web UI -- validation checks, menus, themes -- ship without a row and
  so are unavailable, though they are not blocked: a row added to
  `ohrm_mcp_api_permission` brings one back if you need it.
- Every call writes a row to `ohrm_mcp_audit_log` with user, session, tool,
  endpoint, status, latency and a SHA-256 digest of the arguments. Raw argument
  values are never stored.
- Admin, then Configuration, then MCP Configuration is where all of this is set. It
  flags which APIs hold personal data (`Personal`) or compensation, immigration,
  evaluation and attachment data (`Highly sensitive`); none of the highly
  sensitive ones ships enabled.

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

Four are hand-written, for the questions worth phrasing well:

| Tool | Returns |
|---|---|
| `mcp_get_current_employee` | the authenticated user's employee record |
| `mcp_list_employees` | employees visible to you, optional name filter |
| `mcp_list_job_titles` | job titles with ids |
| `mcp_list_leave_types` | leave types with ids |

The rest are derived from the REST API, one tool per operation, named after the
method and path — `GET /api/v2/pim/employees/{empNumber}` becomes
`rest_get_pim_employees_by_empNumber`. 430 are in the shipped catalog; 65 of them
ship enabled, alongside the four above. Use
`mcp_discover_tools` with a phrase like "leave balance" to find one rather than
paging the whole catalog.

`tools/list` shows only tools that pass all four gates: the endpoint is not
excluded in code, the verb is switched on, the API is enabled on the MCP Configuration
screen, and your own role permits it. So an ESS user sees fewer than an admin,
and an admin sees fewer than the catalog holds.

## Renaming an API

The names on the MCP Configuration screen live in `ohrm_mcp_api_permission.label`,
seeded from the shipped wording and editable afterwards.

```sql
-- call it whatever your organisation calls it
UPDATE ohrm_mcp_api_permission
   SET label = 'Compensation Record'
 WHERE api_name = 'OrangeHRM\\Pim\\Api\\EmployeeSalaryComponentAPI';

-- back to the shipped wording
UPDATE ohrm_mcp_api_permission SET label = NULL WHERE api_name = '...';
```

A NULL label falls back to a name derived from the API class, which is close to
the shipped wording but not always identical -- the shipped list lives in
`installer/Migration/V5_10_0/api-catalog.yaml`. Upgrades only fill labels that
are still NULL, so an edit survives, at the cost of not receiving later
rewording from us.

The same row carries `sensitivity`, a level rather than a name: `0` ordinary,
`10` sensitive. The numbers are spaced so a tier can be added between them
later without renumbering. Unlike the label, an upgrade rewrites this one.

## Prompts

Prompts are stored in `ohrm_mcp_prompt`, not in code, so they can be changed on
a running instance without a release. Four ship enabled.

```sql
-- what a client is being offered
SELECT name, title, is_enabled FROM ohrm_mcp_prompt;

-- reword one
UPDATE ohrm_mcp_prompt
   SET template = 'Show my leave balance and flag anything expiring this period.'
 WHERE name = 'my_leave_balance';

-- stop offering one, without losing it
UPDATE ohrm_mcp_prompt SET is_enabled = 0 WHERE name = 'attendance_this_week';

-- add your own
INSERT INTO ohrm_mcp_prompt (name, title, description, arguments, template, is_enabled)
VALUES (
  'probation_due',
  'Probation reviews due',
  'List employees whose probation ends in the next month.',
  '[{"name":"month","description":"Month to check, e.g. 2026-10.","required":false}]',
  'List employees whose probation period ends in {{month}}, with their supervisor and job title.',
  1
);
```

`template` is plain text. `{{name}}` placeholders are replaced with the
arguments the client supplies; one with no matching argument is left visible in
the text, so a typo shows up rather than silently changing the request.
`arguments` is a JSON array of `{name, description, required}` — if it is
malformed the prompt still lists, just without arguments.

Clients cache the list, so reconnect after editing.

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

**`DEPTH_ZERO_SELF_SIGNED_CERT`.** The client rejected your certificate. Use a
real one. For a development box only, either trust the specific certificate:

```bash
openssl s_client -connect <host>:443 -servername <host> </dev/null 2>/dev/null \
  | openssl x509 -outform PEM > /path/to/host.pem
NODE_EXTRA_CA_CERTS=/path/to/host.pem claude
```

**`ERR_TLS_CERT_ALTNAME_INVALID`.** The certificate is trusted but was issued for
a different name than the one you are calling. Either call the host by a name the
certificate covers, adding an nginx `server_name` for it, or use a certificate
matching the hostname you use. Trusting the certificate does not waive the
hostname check.

## Connecting Claude Code

Register a public OAuth client whose redirect URI is `http://localhost:8080/callback`,
then:

```bash
claude mcp add --transport http --scope user \
  --client-id <client_id> --callback-port 8080 \
  orangehrm https://<host>/<subpath>/web/index.php/api/mcp
```

`--callback-port` matters: without it Claude Code picks a random port, and since
the OAuth server matches the `localhost` spelling exactly including the port, a
random one can never be pre-registered.

If the host root does not serve this instance's authorization-server metadata
(see the three-hop check above), point the client straight at the app-path
document by adding to the server's entry in `~/.claude.json`:

```json
"oauth": {
  "clientId": "<client_id>",
  "callbackPort": 8080,
  "authServerMetadataUrl":
    "https://<host>/<subpath>/web/index.php/.well-known/oauth-authorization-server"
}
```

Then start Claude Code and run `/mcp` to complete sign-in. A browser opens on the
OrangeHRM login; sign in as yourself and approve.
