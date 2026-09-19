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
use OrangeHRM\Mcp\Service\McpAccessPolicy;
use OrangeHRM\Mcp\Tool\ApiPermissionDefaults;

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
        $this->createApiPermissionTable();
        $this->seedApiPermissions();
        $this->seedVerbSwitches();
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

    /**
     * The per-API-class grid: which verbs MCP may use against each endpoint
     * family.
     *
     * A sibling of `ohrm_api_permission` rather than an extension of it. That
     * table belongs to core and answers a different question -- which role may
     * use an endpoint -- and MCP writing to it would change the permission model
     * for the web UI too.
     */
    private function createApiPermissionTable(): void
    {
        if ($this->getSchemaHelper()->tableExists(['ohrm_mcp_api_permission'])) {
            return;
        }

        $this->getSchemaHelper()->createTable('ohrm_mcp_api_permission')
            ->addColumn('id', Types::INTEGER, ['Autoincrement' => true, 'Notnull' => true])
            ->addColumn('api_name', Types::STRING, ['Length' => 255, 'Notnull' => true])
            ->addColumn('can_read', Types::BOOLEAN, ['Notnull' => true, 'Default' => false])
            ->addColumn('can_create', Types::BOOLEAN, ['Notnull' => true, 'Default' => false])
            ->addColumn('can_update', Types::BOOLEAN, ['Notnull' => true, 'Default' => false])
            ->addColumn('can_delete', Types::BOOLEAN, ['Notnull' => true, 'Default' => false])
            ->addColumn('sensitivity', Types::STRING, ['Length' => 20, 'Notnull' => true, 'Default' => 'normal'])
            ->setPrimaryKey(['id'])
            // Unique because the API class is the identity of a row. Without it
            // a re-run could double every row and the screen would show each
            // endpoint family twice.
            ->addUniqueIndex(['api_name'], 'uk_mcp_api_permission_api_name')
            ->create();
    }

    /**
     * Seeds one row per API class.
     *
     * Rows are inserted only when absent, never updated, and never deleted. An
     * upgrade must not reverse a decision an administrator has already made --
     * if they switched PIM reads off, they stay off -- while a genuinely new
     * endpoint family added by this release still arrives with its shipped
     * default. A row a customer added by hand for an endpoint we do not ship
     * (ApiPermissionDefaults::NOT_SEEDED) is left alone for the same reason.
     *
     * Sensitivity is the exception that proves the rule: it is written on insert
     * and is product-owned, because a warning a tenant can downgrade is not a
     * warning. It is refreshed here for existing rows for the same reason.
     */
    private function seedApiPermissions(): void
    {
        $existing = $this->createQueryBuilder()
            ->select('p.api_name', 'p.sensitivity')
            ->from('ohrm_mcp_api_permission', 'p')
            ->executeQuery()
            ->fetchAllKeyValue();

        foreach (ApiPermissionDefaults::rows() as $apiName => $row) {
            if (array_key_exists($apiName, $existing)) {
                if ($existing[$apiName] !== $row['sensitivity']) {
                    $this->createQueryBuilder()
                        ->update('ohrm_mcp_api_permission', 'p')
                        ->set('p.sensitivity', ':sensitivity')
                        ->setParameter('sensitivity', $row['sensitivity'])
                        ->where('p.api_name = :apiName')
                        ->setParameter('apiName', $apiName)
                        ->executeStatement();
                }
                continue;
            }

            $this->createQueryBuilder()
                ->insert('ohrm_mcp_api_permission')
                ->values([
                    'api_name' => ':apiName',
                    'can_read' => ':canRead',
                    'can_create' => ':canCreate',
                    'can_update' => ':canUpdate',
                    'can_delete' => ':canDelete',
                    'sensitivity' => ':sensitivity',
                ])
                ->setParameter('apiName', $apiName)
                ->setParameter('canRead', $row['read'] ? 1 : 0)
                ->setParameter('canCreate', $row['create'] ? 1 : 0)
                ->setParameter('canUpdate', $row['update'] ? 1 : 0)
                ->setParameter('canDelete', $row['delete'] ? 1 : 0)
                ->setParameter('sensitivity', $row['sensitivity'])
                ->executeStatement();
        }
    }

    /**
     * The four top-level verb switches.
     *
     * Written explicitly rather than left to the code default so that the state
     * is visible in `ohrm_config` -- an operator answering "can this instance
     * delete?" should find the answer in the database, not in a PHP constant.
     * Existing values are left alone for the same reason the grid is.
     */
    private function seedVerbSwitches(): void
    {
        foreach (McpAccessPolicy::DEFAULT_VERB_SWITCHES as $verb => $enabled) {
            $key = McpAccessPolicy::KEY_VERB_PREFIX . $verb;
            if ($this->getConfigHelper()->getConfigValue($key) !== null) {
                continue;
            }
            $this->getConfigHelper()->setConfigValue($key, $enabled ? '1' : '0');
        }
    }

    /**
     * The pre-5.10 per-tool-name list, superseded by the per-class grid above.
     *
     * Left behind rather than deleted: it costs nothing, and an instance that
     * rolls back to 5.9 finds its old setting intact instead of a surface that
     * silently switched itself off.
     */
    private const CONFIG_KEY_LEGACY_ENABLED_TOOLS = 'mcp.access.enabled_tools';

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
