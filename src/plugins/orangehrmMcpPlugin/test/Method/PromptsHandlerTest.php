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

namespace OrangeHRM\Tests\Mcp\Method;

use OrangeHRM\Entity\McpPrompt;
use OrangeHRM\Mcp\JsonRpc\Envelope;
use OrangeHRM\Mcp\JsonRpc\EnvelopeException;
use OrangeHRM\Mcp\Method\PromptsGetHandler;
use OrangeHRM\Mcp\Method\PromptsListHandler;
use OrangeHRM\Mcp\Service\McpPromptService;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group Method
 */
class PromptsHandlerTest extends TestCase
{
    private function prompt(
        string $name,
        string $template,
        ?string $arguments = null,
        bool $enabled = true
    ): McpPrompt {
        $prompt = new McpPrompt();
        $prompt->setName($name);
        $prompt->setTitle(ucfirst(str_replace('_', ' ', $name)));
        $prompt->setDescription('Describes ' . $name);
        $prompt->setArguments($arguments);
        $prompt->setTemplate($template);
        $prompt->setEnabled($enabled);
        return $prompt;
    }

    /**
     * @param McpPrompt[] $prompts
     */
    private function service(array $prompts): McpPromptService
    {
        return new class ($prompts) extends McpPromptService {
            private array $prompts;

            public function __construct(array $prompts)
            {
                $this->prompts = $prompts;
            }

            public function getEnabledPrompts(): array
            {
                return array_values(array_filter(
                    $this->prompts,
                    static function (McpPrompt $prompt): bool {
                        return $prompt->isEnabled();
                    }
                ));
            }

            public function getEnabledPromptByName(string $name): ?McpPrompt
            {
                foreach ($this->getEnabledPrompts() as $prompt) {
                    if ($prompt->getName() === $name) {
                        return $prompt;
                    }
                }
                return null;
            }
        };
    }

    private function envelope(string $method, array $params = []): Envelope
    {
        return Envelope::fromPayload(['jsonrpc' => '2.0', 'id' => 1, 'method' => $method, 'params' => $params]);
    }

    public function testListPublishesWhatTheRowHolds(): void
    {
        $service = $this->service([
            $this->prompt(
                'team_on_leave',
                'Who is away on {{date}}?',
                '[{"name":"date","description":"Date to check","required":false}]'
            ),
        ]);

        $result = (new PromptsListHandler($service))->handle($this->envelope('prompts/list'));

        $this->assertCount(1, $result['prompts']);
        $this->assertSame('team_on_leave', $result['prompts'][0]['name']);
        $this->assertSame('Team on leave', $result['prompts'][0]['title']);
        $this->assertSame(
            [['name' => 'date', 'description' => 'Date to check', 'required' => false]],
            $result['prompts'][0]['arguments']
        );
    }

    /**
     * Switching a prompt off has to behave exactly like deleting it, or an
     * administrator who disables one still sees it offered.
     */
    public function testDisabledPromptsAreInvisible(): void
    {
        $service = $this->service([
            $this->prompt('live_one', 'Hello'),
            $this->prompt('retired_one', 'Hello', null, false),
        ]);

        $result = (new PromptsListHandler($service))->handle($this->envelope('prompts/list'));
        $this->assertSame(['live_one'], array_column($result['prompts'], 'name'));

        $this->expectException(EnvelopeException::class);
        (new PromptsGetHandler($service))->handle(
            $this->envelope('prompts/get', ['name' => 'retired_one'])
        );
    }

    public function testGetSubstitutesArguments(): void
    {
        $service = $this->service([$this->prompt('profile', 'Summarise {{employee}} for {{month}}.')]);

        $result = (new PromptsGetHandler($service))->handle($this->envelope('prompts/get', [
            'name' => 'profile',
            'arguments' => ['employee' => 'Odis Adalwin', 'month' => 'September'],
        ]));

        $this->assertSame('user', $result['messages'][0]['role']);
        $this->assertSame(
            'Summarise Odis Adalwin for September.',
            $result['messages'][0]['content']['text']
        );
    }

    /**
     * A row is edited by hand in a SQL client, so a placeholder with no
     * argument is a typo someone needs to see. Blanking it would quietly
     * change what the prompt asks for.
     */
    public function testUnmatchedPlaceholdersSurvive(): void
    {
        $service = $this->service([$this->prompt('profile', 'Summarise {{employee}} for {{month}}.')]);

        $result = (new PromptsGetHandler($service))->handle($this->envelope('prompts/get', [
            'name' => 'profile',
            'arguments' => ['employee' => 'Odis Adalwin'],
        ]));

        $this->assertStringContainsString('{{month}}', $result['messages'][0]['content']['text']);
    }

    public function testRequiredArgumentsAreEnforced(): void
    {
        $service = $this->service([
            $this->prompt('profile', 'Summarise {{employee}}.', '[{"name":"employee","required":true}]'),
        ]);

        try {
            (new PromptsGetHandler($service))->handle($this->envelope('prompts/get', ['name' => 'profile']));
            $this->fail('a missing required argument should be refused');
        } catch (EnvelopeException $e) {
            $this->assertStringContainsString('employee', $e->getMessage());
        }
    }

    public function testUnknownPromptIsRefused(): void
    {
        $this->expectException(EnvelopeException::class);
        (new PromptsGetHandler($this->service([])))->handle(
            $this->envelope('prompts/get', ['name' => 'nothing_here'])
        );
    }

    /**
     * Malformed JSON in the arguments column must not take the listing down:
     * the column is edited by hand, and a broken row should cost that row its
     * arguments, not the whole catalog.
     */
    public function testBrokenArgumentsJsonDegradesToNoArguments(): void
    {
        $service = $this->service([$this->prompt('profile', 'Hello', '{not json at all')]);

        $result = (new PromptsListHandler($service))->handle($this->envelope('prompts/list'));

        $this->assertCount(1, $result['prompts']);
        $this->assertArrayNotHasKey('arguments', $result['prompts'][0]);
    }
}
