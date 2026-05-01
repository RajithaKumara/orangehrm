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

namespace OrangeHRM\Installer\Migration\V5_9_0;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Types\Types;
use OrangeHRM\Installer\Util\V1\AbstractMigration;

class Migration extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        if (!$this->getSchemaHelper()->tableExists(['ohrm_mcp_audit_log'])) {
            $this->getSchemaHelper()->createTable('ohrm_mcp_audit_log')
                ->addColumn('id', Types::INTEGER, ['Autoincrement' => true, 'Notnull' => true])
                ->addColumn('user_id', Types::INTEGER, ['Notnull' => false])
                ->addColumn('mcp_session_id', Types::STRING, ['Length' => 64, 'Notnull' => true])
                ->addColumn('tool_name', Types::STRING, ['Length' => 200, 'Notnull' => true])
                ->addColumn('dispatched_endpoint', Types::STRING, ['Length' => 500, 'Notnull' => false])
                ->addColumn('verb', Types::STRING, ['Length' => 10, 'Notnull' => false])
                ->addColumn('parameter_digest', Types::STRING, ['Length' => 64, 'Notnull' => true])
                ->addColumn('response_status', Types::STRING, ['Length' => 32, 'Notnull' => true])
                ->addColumn('latency_ms', Types::INTEGER, ['Notnull' => false])
                ->addColumn('mcp_client_id', Types::STRING, ['Length' => 200, 'Notnull' => false])
                ->addColumn('created_at', Types::DATETIME_MUTABLE, ['Notnull' => true])
                ->addColumn('permission_profile_hash', Types::STRING, ['Length' => 64, 'Notnull' => false])
                ->setPrimaryKey(['id'])
                ->addIndex(['user_id'], 'idx_mcp_audit_user_id')
                ->addIndex(['mcp_session_id'], 'idx_mcp_audit_session_id')
                ->addIndex(['tool_name'], 'idx_mcp_audit_tool_name')
                ->addIndex(['created_at'], 'idx_mcp_audit_created_at')
                ->addIndex(['response_status'], 'idx_mcp_audit_response_status')
                ->create();

            $foreignKey = new ForeignKeyConstraint(
                ['user_id'],
                'ohrm_user',
                ['id'],
                'fk_mcp_audit_user',
                ['onDelete' => 'RESTRICT']
            );
            $this->getSchemaHelper()->addForeignKey('ohrm_mcp_audit_log', $foreignKey);

            $this->getConnection()->createQueryBuilder()
                ->insert('ohrm_module')
                ->values(
                    [
                        'name' => ':name',
                        'status' => ':status',
                        'display_name' => ':display_name',
                    ]
                )
                ->setParameter('name', 'mcp')
                ->setParameter('status', 1)
                ->setParameter('display_name', 'MCP')
                ->executeQuery();
        }
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return '5.9.0';
    }
}
