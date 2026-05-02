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

namespace OrangeHRM\Mcp\Command;

use DateTime;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Framework\Console\Command;
use OrangeHRM\Mcp\Traits\Service\ModuleServiceTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class McpDoctorCommand extends Command
{
    use ModuleServiceTrait;
    use ConfigServiceTrait;
    use EntityManagerHelperTrait;

    public const STATUS_PASS = 'pass';
    public const STATUS_FAIL = 'fail';
    public const STATUS_SKIP = 'skip';

    public const MCP_MODULE_NAME = 'mcp';
    public const OAUTH_MODULE_NAME = 'oauth';
    public const PROBE_SESSION_ID = '__doctor_probe__';

    public function getCommandName(): string
    {
        return 'mcp:doctor';
    }

    protected function configure(): void
    {
        $this->addOption(
            'host',
            null,
            InputOption::VALUE_REQUIRED,
            'Deployment host or URL to evaluate for the TLS check (default: localhost).',
            'localhost'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $host = (string) $input->getOption('host');

        $results = [
            $this->checkModuleEnabled(),
            $this->checkOauthDependency(),
            $this->checkAuditWritePath(),
            $this->checkOpenApiLoaded(),
            $this->checkTls($host),
        ];

        $io = $this->getIO();
        $io->title('mcp:doctor');

        $passed = $failed = $skipped = 0;
        foreach ($results as $r) {
            $tag = '[' . strtoupper($r['status']) . ']';
            $line = sprintf('%-7s %s', $tag, $r['name']);
            if ($r['status'] !== self::STATUS_PASS && $r['message'] !== '') {
                $line .= ' -- ' . $r['message'];
            }
            $io->writeln($line);
            if ($r['status'] === self::STATUS_PASS) {
                $passed++;
            } elseif ($r['status'] === self::STATUS_FAIL) {
                $failed++;
            } else {
                $skipped++;
            }
        }

        $io->newLine();
        $io->writeln(sprintf('%d passed, %d failed, %d skipped', $passed, $failed, $skipped));

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function checkModuleEnabled(): array
    {
        $enabled = $this->getModuleService()->getModuleDao()->getEnabledModuleNameList();
        if (in_array(self::MCP_MODULE_NAME, $enabled, true)) {
            return $this->result('Module enabled', self::STATUS_PASS, '');
        }
        return $this->result(
            'Module enabled',
            self::STATUS_FAIL,
            'Enable the MCP module via Admin → Modules.'
        );
    }

    private function checkOauthDependency(): array
    {
        $enabled = $this->getModuleService()->getModuleDao()->getEnabledModuleNameList();
        $oauthEnabled = in_array(self::OAUTH_MODULE_NAME, $enabled, true);
        $key = $this->getConfigService()->getConfigDao()->getValue(ConfigService::KEY_OAUTH_ENCRYPTION_KEY);
        $keyPresent = $key !== null && $key !== '';
        if ($oauthEnabled && $keyPresent) {
            return $this->result('OAuth dependency', self::STATUS_PASS, '');
        }
        return $this->result(
            'OAuth dependency',
            self::STATUS_FAIL,
            'Ensure orangehrmCoreOAuthPlugin is enabled and oauth.encryption_key is set.'
        );
    }

    private function checkAuditWritePath(): array
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
            return $this->result('Audit log write path', self::STATUS_PASS, '');
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
                'Audit log write path',
                self::STATUS_FAIL,
                'Audit log write path failed -- verify ohrm_mcp_audit_log exists and the DB user has INSERT. ('
                . $sanitized . ')'
            );
        }
    }

    private function checkOpenApiLoaded(): array
    {
        return $this->result(
            'OpenAPI loaded',
            self::STATUS_SKIP,
            'Pending Story 2.5 (OpenAPI loader).'
        );
    }

    private function checkTls(string $hostInput): array
    {
        $parsedHost = parse_url($hostInput, PHP_URL_HOST);
        $host = $parsedHost !== null && $parsedHost !== false ? $parsedHost : $hostInput;
        $isLocal = in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true);
        $isHttps = strncasecmp($hostInput, 'https://', 8) === 0;
        if ($isLocal || $isHttps) {
            return $this->result('TLS / localhost', self::STATUS_PASS, '');
        }
        return $this->result(
            'TLS / localhost',
            self::STATUS_FAIL,
            'TLS is required for non-localhost deployments. See setup guide (Story 7.1).'
        );
    }

    /**
     * @param 'pass'|'fail'|'skip' $status
     */
    private function result(string $name, string $status, string $message): array
    {
        return ['name' => $name, 'status' => $status, 'message' => $message];
    }
}
