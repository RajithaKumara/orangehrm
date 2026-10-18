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

namespace OrangeHRM\Mcp\Service;

use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Entity\OAuthClient;
use OrangeHRM\Mcp\Traits\Service\ModuleServiceTrait;
use OrangeHRM\OAuth\Service\OAuthService;
use Throwable;

/**
 * Is this instance actually able to serve MCP?
 *
 * The same checks answer that question in two places, and they have to be the
 * same checks: `mcp:doctor` for whoever installed the thing, and the MCP
 * Configuration screen for the HR administrator who will never open a
 * terminal. Two implementations of "is OAuth wired up" would disagree within a
 * release, and the one the administrator reads would be the one nobody
 * maintains.
 *
 * Each check returns `['name' => ..., 'status' => pass|fail|skip, 'message' =>
 * ...]`. The message is written to be read by a person deciding what to do
 * next, so it names the thing to fix rather than the thing that failed.
 *
 * Nothing here writes: the audit probe inserts inside a transaction and rolls
 * it back, because the alternative is telling an administrator the audit path
 * works without ever having exercised it.
 */
class McpHealthCheck
{
    use ModuleServiceTrait;
    use EntityManagerHelperTrait;

    public const STATUS_PASS = 'pass';
    public const STATUS_FAIL = 'fail';
    public const STATUS_SKIP = 'skip';

    public const OAUTH_CLIENT_TABLE = 'ohrm_oauth2_client';

    /**
     * Every check, in the order a reader should meet them: can the feature run
     * at all, then can a client authenticate, then is the surface configured.
     *
     * @param string $host what to judge the TLS requirement against -- the
     *                     command passes its --host option, the screen passes
     *                     the origin the administrator is actually using
     * @return array<int,array{name:string,status:string,message:string}>
     */
    /**
     * @param string $host how this instance is reached, as an origin or a URL
     * @param string|null $basePath whatever sits between the host and the
     *                              application, which only a real request
     *                              knows; null skips the discovery check
     */
    public function all(string $host, ?string $basePath = null): array
    {
        return [
            $this->checkModuleEnabled(),
            $this->checkAssistantClientRegistered(),
            $this->checkDiscoveryAtHostRoot($basePath),
            $this->checkTls($host),
        ];
    }

    /**
     * True when nothing is failing. A skip is not a failure: the OpenAPI
     * document is enrichment, and an instance without one still serves tools.
     *
     * @param array<int,array<string,string>> $results
     */
    public static function isReady(array $results): bool
    {
        foreach ($results as $result) {
            if ($result['status'] === self::STATUS_FAIL) {
                return false;
            }
        }
        return true;
    }

    public function checkModuleEnabled(): array
    {
        $enabled = $this->getModuleService()->getModuleDao()->getEnabledModuleNameList();
        if (in_array(McpConfigurationService::MODULE_NAME, $enabled, true)) {
            return $this->result('MCP module enabled', self::STATUS_PASS, '');
        }
        return $this->result(
            'MCP module enabled',
            self::STATUS_FAIL,
            'Enable the MCP module via Admin → Modules.'
        );
    }

    /**
     * Whether a client will be able to find this server at all.
     *
     * An MCP client drops the path when it builds the discovery URL: told the
     * server is at `https://host/a/b/api/mcp`, it fetches
     * `https://host/.well-known/oauth-authorization-server`. So an instance
     * served from a subpath advertises endpoints nobody can reach -- the
     * authorization request dies before a token is issued, and the only symptom
     * an administrator sees is a client that will not connect.
     *
     * An empty base URL is exactly the condition: the application answers at
     * the host root, which also means the webserver sends unmatched paths to
     * the front controller. Anything else -- a subpath, or `/index.php` because
     * no rewrite is configured -- fails, and both fail for the same reason.
     *
     * The remedy is a hostname of its own with the document root set to `web/`.
     * That is not a workaround for development: two instances cannot share a
     * hostname, because they cannot both own its well-known paths.
     *
     * @param string|null $basePath
     * @return array<string,string>
     */
    public function checkDiscoveryAtHostRoot(?string $basePath): array
    {
        $label = 'Discovery served from the host root';

        if ($basePath === null) {
            return $this->result(
                $label,
                self::STATUS_SKIP,
                'Only a request knows where this instance is mounted. '
                . 'Pass the MCP server URL with --host to check it.'
            );
        }

        if (rtrim($basePath, '/') === '') {
            return $this->result($label, self::STATUS_PASS, '');
        }

        return $this->result(
            $label,
            self::STATUS_FAIL,
            'This instance answers under ' . rtrim($basePath, '/') . ', but a client looks for '
            . '/.well-known/oauth-authorization-server at the host root and will not find it. '
            . 'Give this instance its own hostname with the document root set to the web folder.'
        );
    }

    /**
     * Whether any OAuth client exists that an AI assistant could actually use.
     *
     * The mobile app does not count, and this is the check that says so out
     * loud. Its registration is seeded by the product and `OAuthClientAPI`
     * refuses to update or delete it, so its redirect URI cannot be pointed at
     * an assistant; `McpConfigPolicy` refuses a token issued to it whatever the
     * approved list says. An instance whose only client is the mobile app
     * therefore has nothing that can connect, and an administrator would
     * otherwise find that out from a client that simply fails to authorise.
     *
     * A failure rather than a note: an instance in this state cannot serve
     * anyone, which is the same thing the module-disabled check reports.
     */
    public function checkAssistantClientRegistered(): array
    {
        $label = 'Assistant OAuth client registered';

        try {
            // Loaded rather than counted in SQL: an instance has a handful of
            // these, and the mobile exclusion reads better here than as a
            // second WHERE clause.
            $enabled = $this->getRepository(OAuthClient::class)->findBy(['enabled' => true]);
            $count = count(array_filter($enabled, static function (OAuthClient $client): bool {
                return $client->getClientId() !== OAuthService::PUBLIC_MOBILE_CLIENT_ID;
            }));
        } catch (Throwable $e) {
            return $this->result($label, self::STATUS_FAIL, 'Could not read ' . self::OAUTH_CLIENT_TABLE . '.');
        }

        if ($count > 0) {
            return $this->result(
                $label,
                self::STATUS_PASS,
                $count === 1 ? '1 client may connect' : $count . ' clients may connect'
            );
        }

        return $this->result(
            $label,
            self::STATUS_FAIL,
            'Register one under Admin > Configuration > Register OAuth Client, one per AI product. '
            . 'The mobile app cannot be used: its redirect URI is fixed and MCP refuses its tokens.'
        );
    }

    public function checkTls(string $hostInput): array
    {
        $parsedHost = parse_url($hostInput, PHP_URL_HOST);
        $host = $parsedHost !== null && $parsedHost !== false ? $parsedHost : $hostInput;
        $isLocal = in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true);
        $isHttps = strncasecmp($hostInput, 'https://', 8) === 0;
        if ($isLocal || $isHttps) {
            return $this->result('Secure connection', self::STATUS_PASS, '');
        }
        return $this->result(
            'Secure connection',
            self::STATUS_FAIL,
            'TLS is required for non-localhost deployments. See setup guide (Story 7.1).'
        );
    }

    /**
     * @param 'pass'|'fail'|'skip' $status
     */
    /**
     * @param 'pass'|'fail'|'skip' $status
     */
    private function result(string $name, string $status, string $message): array
    {
        return ['name' => $name, 'status' => $status, 'message' => $message];
    }
}
