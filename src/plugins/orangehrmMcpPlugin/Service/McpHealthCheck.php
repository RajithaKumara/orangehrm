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

use DateTime;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Mcp\Tool\ApiCatalog;
use OrangeHRM\Mcp\Tool\ApiVerb;
use OrangeHRM\Mcp\Tool\Rest\GenericToolFactory;
use OrangeHRM\Mcp\Tool\Rest\OpenApiLoader;
use OrangeHRM\Mcp\Tool\Rest\RouteMap;
use OrangeHRM\Mcp\Traits\Service\McpApiPermissionServiceTrait;
use OrangeHRM\Mcp\Traits\Service\ModuleServiceTrait;
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
    use ConfigServiceTrait;
    use EntityManagerHelperTrait;
    use McpApiPermissionServiceTrait;

    public const STATUS_PASS = 'pass';
    public const STATUS_FAIL = 'fail';
    public const STATUS_SKIP = 'skip';

    public const OAUTH_CLIENT_TABLE = 'ohrm_oauth2_client';
    public const PROBE_SESSION_ID = '__doctor_probe__';

    /**
     * Every check, in the order a reader should meet them: can the feature run
     * at all, then can a client authenticate, then is the surface configured.
     *
     * @param string $host what to judge the TLS requirement against -- the
     *                     command passes its --host option, the screen passes
     *                     the origin the administrator is actually using
     * @return array<int,array{name:string,status:string,message:string}>
     */
    public function all(string $host): array
    {
        return [
            $this->checkModuleEnabled(),
            $this->checkOauthDependency(),
            $this->checkAuditWritePath(),
            $this->checkApiPermissions(),
            $this->checkOpenApiLoaded(),
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
     * orangehrmCoreOAuthPlugin is a core plugin, not a row in `ohrm_module` -- there is
     * no module named 'oauth' to look up. What MCP actually depends on is the OAuth
     * schema being installed and both encryption keys being set, so check those.
     */
    public function checkOauthDependency(): array
    {
        $missing = [];

        $key = $this->getConfigService()->getConfigDao()->getValue(ConfigService::KEY_OAUTH_ENCRYPTION_KEY);
        if ($key === null || $key === '') {
            $missing[] = ConfigService::KEY_OAUTH_ENCRYPTION_KEY;
        }

        $tokenKey = $this->getConfigService()->getConfigDao()
            ->getValue(ConfigService::KEY_OAUTH_TOKEN_ENCRYPTION_KEY);
        if ($tokenKey === null || $tokenKey === '') {
            $missing[] = ConfigService::KEY_OAUTH_TOKEN_ENCRYPTION_KEY;
        }

        $schemaPresent = false;
        try {
            $schemaPresent = $this->getEntityManager()
                ->getConnection()
                ->createSchemaManager()
                ->tablesExist([self::OAUTH_CLIENT_TABLE]);
        } catch (Throwable $e) {
            $schemaPresent = false;
        }

        if ($missing === [] && $schemaPresent) {
            return $this->result('OAuth sign-in configured', self::STATUS_PASS, '');
        }

        $hint = $schemaPresent
            ? 'Set the missing OAuth config value(s): ' . implode(', ', $missing) . '.'
            : 'OAuth schema missing -- expected table ' . self::OAUTH_CLIENT_TABLE
                . '. Run the installer/migrations.';

        return $this->result('OAuth sign-in configured', self::STATUS_FAIL, $hint);
    }

    public function checkAuditWritePath(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        try {
            $connection->beginTransaction();
            $connection->insert('ohrm_mcp_audit_log', [
                'user_id' => null,
                'mcp_session_id' => self::PROBE_SESSION_ID,
                'tool_name' => '__probe__',
                'parameter_digest' => str_repeat('0', 64),
                'response_status' => 'probe',
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            ]);
            $connection->rollBack();
            return $this->result('Audit log writable', self::STATUS_PASS, '');
        } catch (Throwable $e) {
            if ($connection->isTransactionActive()) {
                try {
                    $connection->rollBack();
                } catch (Throwable $rollbackError) {
                    // Best-effort cleanup; the original cause is what we report.
                }
            }
            $sanitized = preg_replace('/\s+/', ' ', $e->getMessage());
            return $this->result(
                'Audit log writable',
                self::STATUS_FAIL,
                'Audit log write path failed -- verify ohrm_mcp_audit_log exists and the DB user has INSERT. ('
                . $sanitized . ')'
            );
        }
    }

    /**
     * The grid is what decides which endpoints are offered, so an operator
     * asking "why does the assistant not see X" should be able to see the state
     * of it without opening the database.
     *
     * Reports what is configured rather than checking it against an expected
     * number: the shipped list is seed data in the migration folder, and
     * reading an installer file at runtime to second-guess the migration is
     * worse than the problem it detects. A catalog that drifts from the routes
     * is caught in CI by ApiCatalogSeedTest, which is where it can be fixed.
     */
    public function checkApiPermissions(): array
    {
        try {
            $map = $this->getMcpApiPermissionService()->getPermissionMap();
        } catch (Throwable $e) {
            $map = [];
        }

        if ($map === []) {
            return $this->result(
                'API permissions installed',
                self::STATUS_FAIL,
                'No rows in ohrm_mcp_api_permission -- nothing is reachable. Run the migrations.'
            );
        }

        $counts = (new ApiCatalog(new RouteMap()))->getCounts($map);

        $parts = [];
        $enabledTools = 0;
        foreach (ApiVerb::all() as $verb) {
            $parts[] = sprintf('%s %d/%d', $verb, $counts['tools'][$verb]['enabled'], $counts['tools'][$verb]['available']);
            $enabledTools += $counts['tools'][$verb]['enabled'];
        }

        $message = sprintf(
            '%d API classes configured; tools: %s (%d enabled)',
            count($map),
            implode(', ', $parts),
            $enabledTools
        );

        return $this->result('API permissions installed', self::STATUS_PASS, $message);
    }

    /**
     * The document is enrichment, never a dependency: an endpoint it fails to
     * describe is still exposed, with a thinner schema. So a gap is worth
     * reporting and is not a failure.
     */
    public function checkOpenApiLoaded(): array
    {
        $stats = (new GenericToolFactory(new RouteMap(), new OpenApiLoader()))->stats();

        if (!$stats['specAvailable']) {
            return $this->result(
                'Tool descriptions available',
                self::STATUS_SKIP,
                'No OpenAPI document found -- tools work but carry no descriptions. '
                . 'Generate it with `php devTools/core/console.php api:generate-doc`.'
            );
        }

        return $this->result(
            'Tool descriptions available',
            self::STATUS_PASS,
            sprintf('%d of %d readable operations described', $stats['described'], $stats['readOnly'])
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
