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

namespace OrangeHRM\Installer\Migration\V5_10_0;

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

        $this->registerMcpAccessScreen();
    }

    /**
     * Registers the MCP Access screen and API under Admin > Configuration, and
     * grants both to the Admin role.
     *
     * Guarded independently of the audit-log block above so that re-running the
     * migration on a database that already has the table still installs the
     * screen. The whole method is idempotent.
     */
    private function registerMcpAccessScreen(): void
    {
        $existingScreenId = $this->createQueryBuilder()
            ->select('screen.id')
            ->from('ohrm_screen', 'screen')
            ->where('screen.name = :screenName')
            ->setParameter('screenName', self::SCREEN_NAME)
            ->executeQuery()
            ->fetchOne();

        if ($existingScreenId !== false && $existingScreenId !== null) {
            return;
        }

        // The YAML files create the screen row, the ohrm_user_role_screen grants,
        // the data group and the ohrm_api_permission entry -- the same mechanism
        // every other module uses, rather than hand-written inserts.
        $this->getDataGroupHelper()->insertScreenPermissions(__DIR__ . '/permission/screen.yaml');
        $this->getDataGroupHelper()->insertApiPermissions(__DIR__ . '/permission/api.yaml');

        $adminMenuId = $this->createQueryBuilder()
            ->select('menu_item.id')
            ->from('ohrm_menu_item', 'menu_item')
            ->where('menu_item.menu_title = :menuTitle')
            ->setParameter('menuTitle', 'Admin')
            ->andWhere('level = :level')
            ->setParameter('level', 1)
            ->executeQuery()
            ->fetchOne();

        $configurationMenuId = $this->createQueryBuilder()
            ->select('menu_item.id')
            ->from('ohrm_menu_item', 'menu_item')
            ->where('menu_item.menu_title = :menuTitle')
            ->setParameter('menuTitle', 'Configuration')
            ->andWhere('level = :level')
            ->setParameter('level', 2)
            ->andWhere('parent_id = :parentId')
            ->setParameter('parentId', $adminMenuId)
            ->executeQuery()
            ->fetchOne();

        $screenId = $this->createQueryBuilder()
            ->select('screen.id')
            ->from('ohrm_screen', 'screen')
            ->where('screen.name = :screenName')
            ->setParameter('screenName', self::SCREEN_NAME)
            ->executeQuery()
            ->fetchOne();

        $this->createQueryBuilder()
            ->insert('ohrm_menu_item')
            ->values(
                [
                    'menu_title' => ':menuTitle',
                    'screen_id' => ':screenId',
                    'parent_id' => ':parentId',
                    'level' => ':level',
                    'order_hint' => ':orderHint',
                    'status' => ':status',
                ]
            )
            ->setParameter('menuTitle', 'MCP Access')
            ->setParameter('screenId', $screenId)
            ->setParameter('parentId', $configurationMenuId)
            ->setParameter('level', 3)
            ->setParameter('orderHint', 1200)
            ->setParameter('status', 1)
            ->executeQuery();
    }

    private const SCREEN_NAME = 'Admin - MCP Access';
    private const SCREEN_ACTION = 'mcpAccess';

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return '5.10.0';
    }
}
