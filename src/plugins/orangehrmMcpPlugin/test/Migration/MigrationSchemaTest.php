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

namespace OrangeHRM\Tests\Mcp\Migration;

use PHPUnit\Framework\TestCase;

/**
 * Schema decisions in the 5.10 migration that are easy to undo by accident and
 * expensive to discover afterwards.
 *
 * A source-level check, deliberately: the MCP suite runs without a database, and
 * the statement being guarded only ever executes on a fresh install. What it
 * protects is the reasoning, not the syntax -- the comment beside the constraint
 * says why, and this fails if someone tightens it back.
 *
 * @group Mcp
 * @group Migration
 */
class MigrationSchemaTest extends TestCase
{
    private function migrationSource(): string
    {
        $path = __DIR__ . '/../../../../../installer/Migration/V5_10_0/Migration.php';
        $source = file_get_contents($path);
        $this->assertIsString($source, 'the 5.10 migration moved; this test needs its new path');

        return $source;
    }

    /**
     * The audit log carries no foreign key to `ohrm_user`, and that is the whole
     * design: the id has to survive the account. RESTRICT would make an MCP user
     * unpurgeable, SET NULL would erase the actor the log exists to name, and
     * CASCADE would delete the evidence. Anyone adding a constraint here is
     * undoing a decision, so this fails loudly.
     */
    public function testAuditLogHasNoForeignKeyToUser(): void
    {
        $source = $this->migrationSource();

        $this->assertStringNotContainsString(
            'fk_mcp_audit_user',
            $source,
            'ohrm_mcp_audit_log.user_id must not be constrained: a purged user id stays in the log'
        );
        $this->assertStringNotContainsString(
            "addForeignKey('ohrm_mcp_audit_log'",
            $source,
            'ohrm_mcp_audit_log must carry no foreign key'
        );
    }

    /**
     * Lookups by user are the point of the table, and the column is still
     * indexed even though nothing constrains it.
     */
    public function testAuditUserColumnStaysIndexed(): void
    {
        $this->assertStringContainsString(
            "addIndex(['user_id'], 'idx_mcp_audit_user_id')",
            $this->migrationSource(),
            'dropping the constraint must not cost the index'
        );
    }
}
