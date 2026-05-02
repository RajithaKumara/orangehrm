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
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OrangeHRM\Core\Dao\ConfigDao;
use OrangeHRM\Core\Dao\ModuleDao;
use OrangeHRM\Core\Service\ConfigService;
use OrangeHRM\Core\Service\ModuleService;
use OrangeHRM\Framework\Services;
use OrangeHRM\Mcp\Command\McpDoctorCommand;
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
     * @param array{enabled?:array<string>,oauthKey?:?string,connectionThrows?:bool} $opts
     */
    private function buildCommand(array $opts = []): CommandTester
    {
        $enabled = $opts['enabled'] ?? ['mcp', 'oauth'];
        $oauthKey = array_key_exists('oauthKey', $opts) ? $opts['oauthKey'] : 'a-key';
        $connectionThrows = $opts['connectionThrows'] ?? false;

        $moduleDao = $this->getMockBuilder(ModuleDao::class)->disableOriginalConstructor()->getMock();
        $moduleDao->method('getEnabledModuleNameList')->willReturn($enabled);
        $moduleService = $this->getMockBuilder(ModuleService::class)->getMock();
        $moduleService->method('getModuleDao')->willReturn($moduleDao);

        $configDao = $this->getMockBuilder(ConfigDao::class)->disableOriginalConstructor()->getMock();
        $configDao->method('getValue')->willReturnCallback(
            fn (string $key) => $key === ConfigService::KEY_OAUTH_ENCRYPTION_KEY ? $oauthKey : null
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

        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->method('getConnection')->willReturn($connection);

        $this->createKernelWithMockServices([
            Services::MODULE_SERVICE => $moduleService,
            Services::CONFIG_SERVICE => $configService,
            Services::DOCTRINE => $em,
        ]);

        $command = new McpDoctorCommand();
        return new CommandTester($command);
    }

    public function testAllPassExitsZero(): void
    {
        $tester = $this->buildCommand();
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('[PASS]', $output);
        $this->assertStringContainsString('[SKIP]  OpenAPI loaded', $output);
        $this->assertStringContainsString('4 passed, 0 failed, 1 skipped', $output);
        $this->assertNotEmpty($this->insertCalls, 'audit probe should have been attempted');
        $this->assertCount(1, $this->rollbackCalls, 'rollback must run exactly once on success path');
    }

    public function testModuleDisabledFails(): void
    {
        $tester = $this->buildCommand(['enabled' => ['oauth']]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  Module enabled', $output);
        $this->assertStringContainsString('Enable the MCP module', $output);
    }

    public function testOauthDependencyMissingKey(): void
    {
        $tester = $this->buildCommand(['oauthKey' => null]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  OAuth dependency', $output);
        $this->assertStringContainsString('oauth.encryption_key', $output);
    }

    public function testAuditWriteFailsCarriesErrorMessage(): void
    {
        $tester = $this->buildCommand(['connectionThrows' => true]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  Audit log write path', $output);
        $this->assertStringContainsString('SQLSTATE', $output);
        $this->assertNotEmpty($this->rollbackCalls, 'rollback must run on failure path too');
    }

    public function testTlsRequiredForNonLocalhostHttp(): void
    {
        $tester = $this->buildCommand();
        $exit = $tester->execute(['--host' => 'hr.example.com']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  TLS / localhost', $output);
        $this->assertStringContainsString('TLS is required', $output);
    }

    public function testTlsHttpsPasses(): void
    {
        $tester = $this->buildCommand();
        $exit = $tester->execute(['--host' => 'https://hr.example.com']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('[PASS]  TLS / localhost', $tester->getDisplay());
    }

    public function testIpv6LocalhostPasses(): void
    {
        $tester = $this->buildCommand();
        $exit = $tester->execute(['--host' => '::1']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('[PASS]  TLS / localhost', $tester->getDisplay());
    }

    public function testOpenApiAlwaysSkippedRegardlessOfOtherChecks(): void
    {
        $tester = $this->buildCommand();
        $tester->execute(['--host' => 'localhost']);
        $this->assertStringContainsString('[SKIP]  OpenAPI loaded', $tester->getDisplay());
        $this->assertStringContainsString('Pending Story 2.5', $tester->getDisplay());
    }
}
