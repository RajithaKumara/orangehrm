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

use OrangeHRM\Framework\Console\Command;
use OrangeHRM\Mcp\Service\McpHealthCheck;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prints the readiness checks for whoever installed the instance.
 *
 * The checks themselves live in McpHealthCheck, because the MCP Configuration
 * screen shows the same ones to an administrator who will never open a
 * terminal. This command is the console rendering of that service and nothing
 * more.
 */
class McpDoctorCommand extends Command
{
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
        $results = (new McpHealthCheck())->all((string) $input->getOption('host'));

        $io = $this->getIO();
        $io->title('mcp:doctor');

        $passed = $failed = $skipped = 0;
        foreach ($results as $r) {
            $line = sprintf('%-7s %s', '[' . strtoupper($r['status']) . ']', $r['name']);
            // Printed for a pass too: a check that passes with "68 of 490 tools
            // enabled" is telling the operator something they came to find out.
            if ($r['message'] !== '') {
                $line .= ' -- ' . $r['message'];
            }
            $io->writeln($line);
            if ($r['status'] === McpHealthCheck::STATUS_PASS) {
                $passed++;
            } elseif ($r['status'] === McpHealthCheck::STATUS_FAIL) {
                $failed++;
            } else {
                $skipped++;
            }
        }

        $io->newLine();
        $io->writeln(sprintf('%d passed, %d failed, %d skipped', $passed, $failed, $skipped));

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
