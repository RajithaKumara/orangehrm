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

namespace OrangeHRM\Tests\Mcp\Tool;

use OrangeHRM\Mcp\Tool\Rest\GenericTool;
use OrangeHRM\Mcp\Tool\Rest\RestRoute;
use OrangeHRM\Mcp\Tool\ToolInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group Tool
 */
class ToolAnnotationsTest extends TestCase
{
    private const LOCATION_API = 'OrangeHRM\\Admin\\Api\\LocationAPI';

    /**
     * One tool per verb, which is what the annotations vary on. Built from hand
     * made routes rather than from the route collection so the test stays a unit
     * test: the framework's routes are not loaded here, and the contract under
     * test belongs to the tool, not to any particular endpoint.
     *
     * @return ToolInterface[]
     */
    private function allTools(): array
    {
        return array_map(function (string $method): GenericTool {
            return new GenericTool(new RestRoute($method, '/api/v2/admin/locations', self::LOCATION_API, []));
        }, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']);
    }

    /**
     * A destructive tool is necessarily a write tool. The inverse combination
     * would be incoherent and would mislead a client reading the annotations.
     */
    public function testDestructiveImpliesWrite(): void
    {
        foreach ($this->allTools() as $tool) {
            $this->assertTrue(
                !$tool->isDestructive() || $tool->isWriteOperation(),
                $tool->getName() . ' is marked destructive but not marked as a write'
            );
        }
    }

    /**
     * Only DELETE is destructive. A client uses this hint to decide what to put
     * in front of a human, so a PUT wrongly marked destructive trains people to
     * click through the confirmations that matter.
     */
    public function testOnlyDeleteIsDestructive(): void
    {
        foreach ($this->allTools() as $tool) {
            $this->assertSame(
                $tool->getVerb() === 'DELETE',
                $tool->isDestructive(),
                $tool->getName() . ' disagrees with its verb about being destructive'
            );
        }
    }

    public function testEveryToolDeclaresTheContractToolsListNeeds(): void
    {
        foreach ($this->allTools() as $tool) {
            $this->assertNotSame('', $tool->getName());
            $this->assertNotSame('', $tool->getDescription());
            $this->assertArrayHasKey('type', $tool->getInputSchema());
            $this->assertNotSame('', $tool->getApiClass());
            $this->assertStringStartsWith('/api/v2/', $tool->getPath());
            $this->assertNotSame('', $tool->getModule());
            // The verb decides which switch governs the tool, which grant the
            // dispatcher checks and which HTTP method it sends. A tool that
            // reports the wrong one is dispatched wrongly three ways.
            $this->assertContains(
                $tool->getVerb(),
                ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
                $tool->getName() . ' reports an unusable verb'
            );
            $this->assertSame(
                $tool->getVerb() !== 'GET',
                $tool->isWriteOperation(),
                $tool->getName() . ' disagrees with itself about whether it writes'
            );
        }
    }

    /**
     * Tool names are part of the public contract: a model's prompt may reference
     * them, so they must stay stable and predictable.
     */
    public function testToolNamesFollowTheRestPrefixConvention(): void
    {
        foreach ($this->allTools() as $tool) {
            $this->assertMatchesRegularExpression('/^rest_[a-z0-9_]+$/', $tool->getName());
        }
    }
}
