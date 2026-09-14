#!/usr/bin/env bash
#
# Drive the OrangeHRM OAuth 2 authorization-code + PKCE flow headlessly and print
# a bearer token usable against /api/mcp. Mirrors exactly what an MCP client does,
# so it tells you whether a failed connection is the server or the client.
#
# Usage:
#   OHRM_CLIENT_ID=<id> OHRM_PASSWORD=<pw> ./oauth-flow-example.sh
#
# Drive the full OrangeHRM OAuth 2 authorization-code + PKCE flow headlessly and
# print the resulting bearer token. Mirrors exactly what an MCP client does.
set -euo pipefail

BASE="${BASE:-https://php74/orangehrm_57/web/index.php}"
SCRATCH="${SCRATCH:-/tmp/ohrm-mcp}"
mkdir -p "$SCRATCH"
# Set these for your instance before running.
OHRM_CLIENT_ID="${OHRM_CLIENT_ID:?set OHRM_CLIENT_ID to the client_id from Admin > Configuration > Register OAuth Client}"
# Prefixed because USERNAME is commonly already set in the environment, which
# would silently override the default and send the wrong login.
OHRM_USER="${OHRM_USER:-admin}"
OHRM_PASSWORD="${OHRM_PASSWORD:?set OHRM_PASSWORD to the OrangeHRM password for that user}"
REDIRECT="${REDIRECT:-http://127.0.0.1:9876/callback}"
JAR="$SCRATCH/cookies.txt"
rm -f "$JAR"

C() { curl -sk -b "$JAR" -c "$JAR" "$@"; }

# PKCE pair: verifier is base64url of 32 random bytes, challenge is S256 of it.
# RFC 7636: 43-128 chars from [A-Za-z0-9-._~]. Hex satisfies that by construction.
VERIFIER=$(openssl rand -hex 32)
CHALLENGE=$(printf '%s' "$VERIFIER" | openssl dgst -sha256 -binary | openssl base64 | tr '+/' '-_' | tr -d '=')

echo "== 1. login page (collect CSRF token) =="
LOGIN_HTML=$(C "$BASE/auth/login")
TOKEN=$(printf '%s' "$LOGIN_HTML" | grep -oP ':token="&quot;\K[^&]+' | head -1)
if [ -z "$TOKEN" ]; then
  TOKEN=$(printf '%s' "$LOGIN_HTML" | grep -oP 'token[^a-zA-Z0-9]{1,12}\K[A-Za-z0-9._-]{20,}' | head -1)
fi
echo "csrf token: ${TOKEN:0:24}..."

echo "== 2. authenticate as admin =="
C -X POST "$BASE/auth/validate" \
  --data-urlencode "_token=$TOKEN" \
  --data-urlencode "username=$OHRM_USER" \
  --data-urlencode "password=$OHRM_PASSWORD" \
  -o /dev/null -w '  status %{http_code} -> %{redirect_url}\n'

echo "== 3. authorize (PKCE S256) =="
AUTH_URL="$BASE/oauth2/authorize?response_type=code&client_id=$OHRM_CLIENT_ID&redirect_uri=$(printf '%s' "$REDIRECT" | sed 's|:|%3A|g; s|/|%2F|g')&code_challenge=$CHALLENGE&code_challenge_method=S256&state=xyz"
C "$AUTH_URL" -o /dev/null -w '  consent page status %{http_code}\n'

echo "== 4. approve consent =="
CONSENT="$BASE/oauth2/authorize/consent?response_type=code&client_id=$OHRM_CLIENT_ID&redirect_uri=$(printf '%s' "$REDIRECT" | sed 's|:|%3A|g; s|/|%2F|g')&code_challenge=$CHALLENGE&code_challenge_method=S256&state=xyz&authorized=true"
LOCATION=$(C "$CONSENT" -o /dev/null -D - -w '' | grep -i '^location:' | tr -d '\r' | sed 's/^[Ll]ocation: //')
echo "  redirect: ${LOCATION:0:80}..."
CODE=$(printf '%s' "$LOCATION" | grep -oP 'code=\K[^&]+' || true)
if [ -z "$CODE" ]; then echo "  !! no authorization code returned"; exit 1; fi
echo "  code: ${CODE:0:24}..."

echo "== 5. exchange code for token =="
TOKEN_JSON=$(curl -sk -X POST "$BASE/oauth2/token" \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode "grant_type=authorization_code" \
  --data-urlencode "client_id=$OHRM_CLIENT_ID" \
  --data-urlencode "redirect_uri=$REDIRECT" \
  --data-urlencode "code=$CODE" \
  --data-urlencode "code_verifier=$VERIFIER")
echo "$TOKEN_JSON"

ACCESS=$(printf '%s' "$TOKEN_JSON" | grep -oP '"access_token"\s*:\s*"\K[^"]+' || true)
if [ -z "$ACCESS" ]; then echo "!! no access token"; exit 1; fi
printf '%s' "$ACCESS" > "$SCRATCH/access_token.txt"
echo "== token saved (${#ACCESS} chars) =="
