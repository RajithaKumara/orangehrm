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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use OrangeHRM\Core\Dao\ModuleDao;
use OrangeHRM\Core\Service\ModuleService;
use OrangeHRM\Entity\OAuthClient;
use OrangeHRM\Framework\Services;
use OrangeHRM\Mcp\Command\McpDoctorCommand;
use OrangeHRM\Mcp\Service\McpConfigPolicy;
use OrangeHRM\Tests\Util\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group Mcp
 * @group Command
 */
class McpDoctorCommandTest extends KernelTestCase
{
    /**
     * @param array{enabled?:array<string>,oauthClientIds?:array<string>,mcpEnabled?:bool,approvedClientIds?:array<string>} $opts
     */
    private function buildCommand(array $opts = []): CommandTester
    {
        // No 'oauth' entry: orangehrmCoreOAuthPlugin is a core plugin and never appears
        // in ohrm_module. The real module list on a 5.10 install is admin, auth, core,
        // leave, mcp, pim, ... with no oauth row.
        $enabled = $opts['enabled'] ?? ['mcp', 'admin', 'pim'];
        $moduleDao = $this->getMockBuilder(ModuleDao::class)->disableOriginalConstructor()->getMock();
        $moduleDao->method('getEnabledModuleNameList')->willReturn($enabled);
        $moduleService = $this->getMockBuilder(ModuleService::class)->getMock();
        $moduleService->method('getModuleDao')->willReturn($moduleDao);

        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

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

        // The two switches the policy owns: whether MCP is turned on, and which
        // clients an administrator has approved. The default is a configured
        // instance -- on, with the registered assistant approved.
        $mcpEnabled = $opts['mcpEnabled'] ?? true;
        $approved = $opts['approvedClientIds'] ?? ['a-registered-assistant'];
        $policy = $this->getMockBuilder(McpConfigPolicy::class)->disableOriginalConstructor()->getMock();
        $policy->method('isEnabled')->willReturn($mcpEnabled);
        $policy->method('getApprovedClientIds')->willReturn($approved);

        $this->createKernelWithMockServices([
            Services::MODULE_SERVICE => $moduleService,
            Services::DOCTRINE => $em,
            Services::MCP_CONFIG_POLICY => $policy,
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
        // The discovery check skips without a URL to judge: nothing in a CLI
        // invocation says where the application is mounted, and the command
        // takes a bare host by default.
        $this->assertStringContainsString('3 passed, 0 failed, 1 skipped', $output);
    }

    public function testModuleDisabledFails(): void
    {
        $tester = $this->buildCommand(['enabled' => ['admin', 'pim']]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  MCP enabled', $output);
        $this->assertStringContainsString('switched off in the database', $output);
    }

    /**
     * The reading that used to be impossible: the module row ships enabled and
     * no screen turns it off, so reporting only that said "MCP module enabled"
     * to an administrator who had just switched MCP off.
     */
    public function testSwitchedOffReportsAsDisabled(): void
    {
        $tester = $this->buildCommand(['mcpEnabled' => false]);
        $exit = $tester->execute(['--host' => 'localhost']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  MCP enabled', $output);
        $this->assertStringContainsString('switch at the top of the MCP Configuration screen', $output);
    }

    /**
     * Approval is enforced the moment the list is non-empty, so an id matching
     * no client refuses every caller while every other check still passes.
     */
    public function testApprovedClientThatDoesNotExistFails(): void
    {
        $command = $this->buildCommand(['approvedClientIds' => ['never-registered']]);
        $command->execute([]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('[FAIL]', $output);
        $this->assertStringContainsString('never-registered', $output);
        $this->assertSame(1, $command->getStatusCode());
    }

    /**
     * Registering an OAuth client says nothing about assistants -- customers
     * create them for payroll syncs and reporting tools. Without an approved
     * list there is nothing to identify one by, so the check says so rather
     * than counting unrelated integrations.
     */
    public function testWithoutAnApprovedListTheAssistantCannotBeIdentified(): void
    {
        $command = $this->buildCommand(['approvedClientIds' => []]);
        $command->execute([]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('[SKIP]', $output);
        $this->assertStringContainsString('No AI application has been approved yet', $output);
        $this->assertSame(0, $command->getStatusCode(), 'not knowing is not a failure');
    }

    public function testTlsRequiredForNonLocalhostHttp(): void
    {
        $tester = $this->buildCommand();
        $exit = $tester->execute(['--host' => 'hr.example.com']);
        $output = $tester->getDisplay();

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('[FAIL]  Secure connection', $output);
        $this->assertStringContainsString('only connect over https', $output);
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
        $command = $this->buildCommand(['oauthClientIds' => ['orangehrm_mobile_app'], 'approvedClientIds' => []]);
        $command->execute([]);

        $output = $command->getDisplay();
        $this->assertStringContainsString('[FAIL]', $output);
        $this->assertStringContainsString('Approved AI application', $output);
        $this->assertStringContainsString('Register OAuth Client', $output);
        $this->assertSame(1, $command->getStatusCode());
    }

    public function testFailsWhenNoOAuthClientIsRegisteredAtAll(): void
    {
        $command = $this->buildCommand(['oauthClientIds' => [], 'approvedClientIds' => []]);
        $command->execute([]);

        $this->assertStringContainsString('No application is registered', $command->getDisplay());
        $this->assertSame(1, $command->getStatusCode());
    }

    /**
     * An MCP client drops the path when it builds the discovery URL, so an
     * instance served from a subpath advertises endpoints nobody can reach.
     * The only symptom otherwise is a client that will not connect, with
     * nothing to say why.
     */
    public function testFailsWhenTheInstanceIsServedFromASubpath(): void
    {
        $command = $this->buildCommand();
        $command->execute(['--host' => 'https://hr.example.com/orangehrm/web/index.php/api/mcp']);

        $output = $command->getDisplay();
        $this->assertStringContainsString('[FAIL]', $output);
        $this->assertStringContainsString('Assistants can find this server', $output);
        $this->assertStringContainsString('/orangehrm/web/index.php', $output);
        $this->assertSame(1, $command->getStatusCode());
    }

    public function testPassesWhenTheInstanceOwnsTheHostRoot(): void
    {
        $command = $this->buildCommand();
        $command->execute(['--host' => 'https://hr.example.com']);

        $this->assertStringContainsString('4 passed, 0 failed, 0 skipped', $command->getDisplay());
        $this->assertSame(0, $command->getStatusCode());
    }

    /**
     * The /api/mcp suffix is the URL an administrator actually has to hand --
     * it is what they paste into the client -- so it must not be mistaken for
     * a subpath.
     */
    public function testTheMcpEndpointSuffixIsNotMistakenForASubpath(): void
    {
        $command = $this->buildCommand();
        $command->execute(['--host' => 'https://hr.example.com/api/mcp']);

        $this->assertStringContainsString('4 passed, 0 failed, 0 skipped', $command->getDisplay());
    }
}
