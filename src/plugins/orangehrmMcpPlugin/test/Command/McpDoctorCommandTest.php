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

namespace OrangeHRM\Tests\Mcp\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use OrangeHRM\Core\Dao\ConfigDao;
use OrangeHRM\Core\Dao\ModuleDao;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Core\Service\ModuleService;
use OrangeHRM\Entity\OAuthClient;
use OrangeHRM\Framework\Services;
use OrangeHRM\Mcp\Command\McpDoctorCommand;
use OrangeHRM\Mcp\Service\McpApiPermissionService;
use OrangeHRM\Mcp\Tool\ApiSensitivity;
use OrangeHRM\Tests\Mcp\Tool\ApiCatalogSeedTest;
use OrangeHRM\Mcp\Tool\ApiVerb;
use OrangeHRM\Tests\Util\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group Mcp
 * @group Command
 */
class McpDoctorCommandTest extends KernelTestCase
{
    private array $insertCalls = [];
    private array $rollbackCalls = [];

    /**
     * @param array{enabled?:array<string>,oauthKey?:?string,oauthTokenKey?:?string,oauthSchema?:bool,connectionThrows?:bool,permissionRows?:?array} $opts
     */
    private function buildCommand(array $opts = []): CommandTester
    {
        // No 'oauth' entry: orangehrmCoreOAuthPlugin is a core plugin and never appears
        // in ohrm_module. The real module list on a 5.10 install is admin, auth, core,
        // leave, mcp, pim, ... with no oauth row.
        $enabled = $opts['enabled'] ?? ['mcp', 'admin', 'pim'];
        $oauthKey = array_key_exists('oauthKey', $opts) ? $opts['oauthKey'] : 'a-key';
        $oauthTokenKey = array_key_exists('oauthTokenKey', $opts) ? $opts['oauthTokenKey'] : 'a-token-key';
        $oauthSchema = $opts['oauthSchema'] ?? true;
        $connectionThrows = $opts['connectionThrows'] ?? false;
        // A healthy instance has one row per shipped API class; the doctor
        // compares the two counts, so the stub has to be the full set.
        $permissionRows = array_key_exists('permissionRows', $opts)
            ? $opts['permissionRows']
            : $this->fullPermissionMap();

        $moduleDao = $this->getMockBuilder(ModuleDao::class)->disableOriginalConstructor()->getMock();
        $moduleDao->method('getEnabledModuleNameList')->willReturn($enabled);
        $moduleService = $this->getMockBuilder(ModuleService::class)->getMock();
        $moduleService->method('getModuleDao')->willReturn($moduleDao);

        $configDao = $this->getMockBuilder(ConfigDao::class)->disableOriginalConstructor()->getMock();
        $configDao->method('getValue')->willReturnCallback(
            function (string $key) use ($oauthKey, $oauthTokenKey) {
                if ($key === ConfigService::KEY_OAUTH_ENCRYPTION_KEY) {
                    return $oauthKey;
                }
                if ($key === ConfigService::KEY_OAUTH_TOKEN_ENCRYPTION_KEY) {
                    return $oauthTokenKey;
                }
                return null;
            }
        );
        $configService = $this->getMockBuilder(ConfigService::class)->getMock();
        $configService->method('getConfigDao')->willReturn($configDao);

        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $connection->method('beginTransaction')->willReturn(true);
        if ($connectionThrows) {
            $connection->method('insert')->willReturnCallback(function (): void {
                throw new Exception('SQLSTATE[42S02]: Base table or view not found');
            });
            $connection->method('isTransactionActive')->willReturn(true);
        } else {
            $connection->method('insert')->willReturnCallback(
                function (string $table, array $row): int {
                    $this->insertCalls[] = ['table' => $table, 'row' => $row];
                    return 1;
                }
            );
            $connection->method('isTransactionActive')->willReturn(true);
        }
        $connection->method('rollBack')->willReturnCallback(function (): bool {
            $this->rollbackCalls[] = true;
            return true;
        });

        $schemaManager = $this->getMockBuilder(AbstractSchemaManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schemaManager->method('tablesExist')->willReturn($oauthSchema);
        $connection->method('createSchemaManager')->willReturn($schemaManager);

        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->method('getConnection')->willReturn($connection);

        // Which OAuth clients exist. The mobile app never counts, so the
        // default here is one real client plus the mobile registration, which
        // is what a configured instance looks like.
        $clientIds = $opts['oauthClientIds'] ?? ['orangehrm_mobile_app', 'a-registered-assistant'];
        $clients = array_map(
            static function (string $clientId): OAuthClient {
                $client = new OAuthClient();
                $client->setClientId($clientId);
                return $client;
            },
            $clientIds
        );
        $clientRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientRepository->method('findBy')->willReturn($clients);
        $em->method('getRepository')->willReturn($clientRepository);

        $permissionService = new class ($permissionRows) extends McpApiPermissionService {
            private array $rows;

            public function __construct(array $rows)
            {
                $this->rows = $rows;
            }

            public function getPermissionMap(): array
            {
                return $this->rows;
            }
        };

        $this->createKernelWithMockServices([
            Services::MODULE_SERVICE => $moduleService,
            Services::CONFIG_SERVICE => $configService,
            Services::DOCTRINE => $em,
            Services::MCP_API_PERMISSION_SERVICE => $permissionService,
        ]);

        $command = new McpDoctorCommand();
        return new CommandTester($command);
    }

    /**
     * @return array<string,array<string,mixed>> every shipped class, reads on
     */
    private function fullPermissionMap(): array
    {
        $map = [];
        foreach (ApiCatalogSeedTest::seed() as $apiClass => $entry) {
            $map[$apiClass] = [
                ApiVerb::READ => !empty($entry['read']),
                ApiVerb::CREATE => false,
                ApiVerb::UPDATE => false,
                ApiVerb::DELETE => false,
                'sensitivity' => (int) ($entry['sensitivity'] ?? ApiSensitivity::NORMAL),
            ];
        }
        return $map;
    }

    public function testAllPassExitsZero(): void
    {
        $tester = $this->buildCommand();
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('[PASS]', $output);
        $this->assertStringContainsString('7 passed, 0 failed, 0 skipped', $output);
        $this->assertNotEmpty($this->insertCalls, 'audit probe should have been attempted');
        $this->assertCount(1, $this->rollbackCalls, 'rollback must run exactly once on success path');
    }

    public function testModuleDisabledFails(): void
    {
        $tester = $this->buildCommand(['enabled' => ['admin', 'pim']]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  MCP module enabled', $output);
        $this->assertStringContainsString('Enable the MCP module', $output);
    }

    public function testOauthDependencyMissingKey(): void
    {
        $tester = $this->buildCommand(['oauthKey' => null]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  OAuth sign-in configured', $output);
        $this->assertStringContainsString('oauth.encryption_key', $output);
    }

    public function testOauthDependencyMissingTokenKey(): void
    {
        $tester = $this->buildCommand(['oauthTokenKey' => null]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  OAuth sign-in configured', $output);
        $this->assertStringContainsString('oauth.token_encryption_key', $output);
    }

    public function testOauthDependencyMissingSchema(): void
    {
        $tester = $this->buildCommand(['oauthSchema' => false]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  OAuth sign-in configured', $output);
        $this->assertStringContainsString('ohrm_oauth2_client', $output);
    }

    public function testOauthDependencyPassesWithoutAnOauthModuleRow(): void
    {
        // Regression guard: the original check required in_array('oauth', $enabledModules),
        // which can never be true, so mcp:doctor always reported FAIL on a healthy install.
        $tester = $this->buildCommand(['enabled' => ['mcp']]);
        $tester->execute(['--host' => 'localhost']);

        $this->assertStringContainsString('[PASS]  OAuth sign-in configured', $tester->getDisplay());
    }

    public function testAuditWriteFailsCarriesErrorMessage(): void
    {
        $tester = $this->buildCommand(['connectionThrows' => true]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  Audit log writable', $output);
        $this->assertStringContainsString('SQLSTATE', $output);
        $this->assertNotEmpty($this->rollbackCalls, 'rollback must run on failure path too');
    }

    public function testTlsRequiredForNonLocalhostHttp(): void
    {
        $tester = $this->buildCommand();
        $exit = $tester->execute(['--host' => 'hr.example.com']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  Secure connection', $output);
        $this->assertStringContainsString('TLS is required', $output);
    }

    public function testTlsHttpsPasses(): void
    {
        $tester = $this->buildCommand();
        $exit = $tester->execute(['--host' => 'https://hr.example.com']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('[PASS]  Secure connection', $tester->getDisplay());
    }

    public function testIpv6LocalhostPasses(): void
    {
        $tester = $this->buildCommand();
        $exit = $tester->execute(['--host' => '::1']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('[PASS]  Secure connection', $tester->getDisplay());
    }

    public function testOpenApiCheckReportsCoverage(): void
    {
        $tester = $this->buildCommand();
        $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        // The document ships in the repo, so this build describes most
        // operations. A missing document is a SKIP, never a failure: an endpoint
        // it does not describe is still exposed, just with a thinner schema.
        $this->assertStringContainsString('Tool descriptions available', $output);
        $this->assertMatchesRegularExpression(
            '/Tool descriptions available -- \\d+ of \\d+ readable operations described/',
            $output
        );
    }

    /**
     * An empty grid means nothing is reachable at all -- the likeliest cause is
     * a migration that has not run. Silence there would leave an operator
     * debugging an assistant that simply sees no tools.
     */
    public function testEmptyPermissionTableFails(): void
    {
        $tester = $this->buildCommand(['permissionRows' => []]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  API permissions installed', $output);
        $this->assertStringContainsString('Run the migrations', $output);
    }

    public function testPermissionCheckReportsEnabledToolCounts(): void
    {
        $tester = $this->buildCommand();
        $tester->execute(['--host' => 'localhost']);

        $this->assertMatchesRegularExpression(
            '/API permissions installed -- \\d+ API classes configured; tools: read \\d+\\/\\d+/',
            $tester->getDisplay()
        );
    }

    /**
     * An instance whose only OAuth client is the seeded mobile app has nothing
     * that can connect: its registration cannot be updated or deleted, so its
     * redirect URI can never point at an assistant, and McpConfigPolicy refuses
     * its tokens whatever the approved list says. Better to say so here than to
     * let an administrator discover it from a client that simply fails to
     * authorise.
     */
    public function testFailsWhenTheOnlyOAuthClientIsTheMobileApp(): void
    {
        $command = $this->buildCommand(['oauthClientIds' => ['orangehrm_mobile_app']]);
        $command->execute([]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('[FAIL]', $output);
        $this->assertStringContainsString('Assistant OAuth client registered', $output);
        $this->assertStringContainsString('Register OAuth Client', $output);
        $this->assertSame(1, $command->getStatusCode());
    }

    public function testFailsWhenNoOAuthClientIsRegisteredAtAll(): void
    {
        $command = $this->buildCommand(['oauthClientIds' => []]);
        $command->execute([]);

        $this->assertStringContainsString('Assistant OAuth client registered', $command->getDisplay());
        $this->assertSame(1, $command->getStatusCode());
    }
}
