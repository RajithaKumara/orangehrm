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

use OrangeHRM\Mcp\Tool\Curated\CurrentEmployeeTool;
use OrangeHRM\Mcp\Tool\Curated\ListEmployeesTool;
use OrangeHRM\Mcp\Tool\Curated\ListJobTitlesTool;
use OrangeHRM\Mcp\Tool\Curated\ListLeaveTypesTool;
use OrangeHRM\Mcp\Tool\ToolInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group Mcp
 * @group Tool
 */
class ToolAnnotationsTest extends TestCase
{
    /**
     * @return ToolInterface[]
     */
    private function allTools(): array
    {
        return [
            new CurrentEmployeeTool(),
            new ListEmployeesTool(),
            new ListJobTitlesTool(),
            new ListLeaveTypesTool(),
        ];
    }

    /**
     * The prototype ships a read-only surface. If this ever fails, a write tool
     * was added and it must also pass through the write-discipline interceptor
     * before it is safe to expose.
     */
    public function testEveryShippedToolIsReadOnly(): void
    {
        foreach ($this->allTools() as $tool) {
            $this->assertFalse(
                $tool->isWriteOperation(),
                $tool->getName() . ' claims to be a write tool, but no write path exists yet'
            );
            $this->assertFalse($tool->isDestructive(), $tool->getName() . ' must not be destructive');
        }
    }

    /**
     * A destructive tool is necessarily a write tool. The inverse combination
     * would be incoherent and would mislead a client reading the annotations.
     */
    public function testDestructiveImpliesWrite(): void
    {
        foreach ($this->allTools() as $tool) {
            // Asserted unconditionally rather than inside an `if ($tool->isDestructive())`:
            // no tool is destructive today, so a guarded assertion would never run
            // and the test would silently prove nothing.
            $this->assertTrue(
                !$tool->isDestructive() || $tool->isWriteOperation(),
                $tool->getName() . ' is marked destructive but not marked as a write'
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
        }
    }

    /**
     * Tool names are part of the public contract: a model's prompt may reference
     * them, so they must stay stable and predictable.
     */
    public function testToolNamesFollowTheMcpPrefixConvention(): void
    {
        foreach ($this->allTools() as $tool) {
            $this->assertMatchesRegularExpression('/^mcp_[a-z0-9_]+$/', $tool->getName());
        }
    }
}
