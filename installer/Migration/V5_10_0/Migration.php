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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Types\Types;
use OrangeHRM\Installer\Util\V1\AbstractMigration;
use Symfony\Component\Yaml\Yaml;
use OrangeHRM\Mcp\Service\McpConfigPolicy;
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

        $this->registerMcpConfigScreen();
        $this->createApiPermissionTable();
        $this->addApiPermissionLabelColumn();
        $this->constrainSensitivityColumn();
        $this->seedApiPermissions();
        $this->seedSwitches();
        $this->seedAllowedRoles();
        $this->createPromptTable();
        $this->seedPrompts();
    }

    /**
     * Registers the MCP Configuration screen and API under Admin > Configuration,
     * grants both to the Admin role.
     *
     * Guarded independently of the audit-log block above so that re-running the
     * migration on a database that already has the table still installs the
     * screen. The whole method is idempotent.
     */
    private function registerMcpConfigScreen(): void
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
            ->setParameter('menuTitle', 'MCP Configuration')
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
            ->addColumn('label', Types::STRING, ['Length' => 200, 'Notnull' => false])
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
     * For a database created before the label column existed.
     *
     * 5.10 is unreleased, so this only covers instances that ran an earlier
     * build of this same migration -- but leaving them with a table the entity
     * does not match is a worse thing to ship than four lines.
     */
    private function addApiPermissionLabelColumn(): void
    {
        if ($this->getSchemaHelper()->getTableColumn('ohrm_mcp_api_permission', 'label') !== null) {
            return;
        }
        $this->getSchemaHelper()->addColumn(
            'ohrm_mcp_api_permission',
            'label',
            Types::STRING,
            ['Length' => 200, 'Notnull' => false]
        );
    }

    /**
     * Narrows `sensitivity` to the two values that mean anything.
     *
     * PHP enums need 8.1 and this product still supports 7.4, so the constraint
     * has to live in the schema. It earns its keep now that rows are edited by
     * hand: a typo used to read back as `normal` through the Dao's fallback and
     * quietly drop the flag, and the database now refuses it outright.
     * `hs_hr_emp_dependents.ed_relationship_type` is the same idea.
     */
    private function constrainSensitivityColumn(): void
    {
        $this->getConnection()->executeStatement(
            "ALTER TABLE ohrm_mcp_api_permission "
            . "MODIFY sensitivity ENUM('normal','sensitive') NOT NULL DEFAULT 'normal'"
        );
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

        $labels = $this->getApiLabels();
        $sensitive = $this->getSensitiveApis();

        $unlabelled = $this->createQueryBuilder()
            ->select('p.api_name')
            ->from('ohrm_mcp_api_permission', 'p')
            ->where('p.label IS NULL')
            ->executeQuery()
            ->fetchFirstColumn();
        $unlabelled = array_flip($unlabelled);

        foreach (ApiPermissionDefaults::rows() as $apiName => $row) {
            if (array_key_exists($apiName, $existing)) {
                // Only where the label was never set. A customer who renamed
                // one keeps their wording; the cost is that our later
                // rewordings do not reach them, which is the same bargain the
                // prompt table makes.
                if (isset($unlabelled[$apiName])) {
                    $this->createQueryBuilder()
                        ->update('ohrm_mcp_api_permission', 'p')
                        ->set('p.label', ':label')
                        ->setParameter('label', $labels[$apiName] ?? null)
                        ->where('p.api_name = :apiName')
                        ->setParameter('apiName', $apiName)
                        ->executeStatement();
                }
                $sensitivity = isset($sensitive[$apiName]) ? 'sensitive' : 'normal';
                if ($existing[$apiName] !== $sensitivity) {
                    $this->createQueryBuilder()
                        ->update('ohrm_mcp_api_permission', 'p')
                        ->set('p.sensitivity', ':sensitivity')
                        ->setParameter('sensitivity', $sensitivity)
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
                    'label' => ':label',
                    'can_read' => ':canRead',
                    'can_create' => ':canCreate',
                    'can_update' => ':canUpdate',
                    'can_delete' => ':canDelete',
                    'sensitivity' => ':sensitivity',
                ])
                ->setParameter('apiName', $apiName)
                ->setParameter('label', $labels[$apiName] ?? null)
                ->setParameter('canRead', $row['read'] ? 1 : 0)
                ->setParameter('canCreate', $row['create'] ? 1 : 0)
                ->setParameter('canUpdate', $row['update'] ? 1 : 0)
                ->setParameter('canDelete', $row['delete'] ? 1 : 0)
                ->setParameter('sensitivity', isset($sensitive[$apiName]) ? 'sensitive' : 'normal')
                ->executeStatement();
        }
    }

    /**
     * The API classes flagged sensitive, keyed for lookup.
     *
     * @return array<string,true>
     */
    private function getSensitiveApis(): array
    {
        $parsed = Yaml::parseFile(__DIR__ . '/api-sensitivity.yaml');
        $classes = is_array($parsed) && isset($parsed['sensitive']) ? (array) $parsed['sensitive'] : [];

        return array_fill_keys($classes, true);
    }

    /**
     * The shipped wording for each API, read once at install time.
     *
     * A YAML file beside this migration rather than a class in the plugin: it
     * is seed data for one version, it is never consulted at runtime, and the
     * screen reads the column it populates. `permission/api.yaml` and
     * `permission/screen.yaml` next to it work the same way.
     *
     * @return array<string,string>
     */
    private function getApiLabels(): array
    {
        $parsed = Yaml::parseFile(__DIR__ . '/api-label.yaml');
        return is_array($parsed) ? $parsed : [];
    }

    /**
     * The master switch and the four verb switches.
     *
     * Written explicitly rather than left to the code defaults so that the
     * state is visible in `ohrm_config` -- an operator answering "is MCP on?"
     * or "can this instance delete?" should find the answer in the database,
     * not in a PHP constant. Existing values are left alone for the same reason
     * the grid is.
     *
     * Ships off, with Read the only verb allowed: turning MCP on is the
     * deliberate act the advisory is written about, and reading is the thing it
     * is for.
     */
    private function seedSwitches(): void
    {
        $switches = [McpConfigPolicy::KEY_ENABLED => McpConfigPolicy::DEFAULT_ENABLED];
        foreach (McpConfigPolicy::DEFAULT_VERB_SWITCHES as $verb => $enabled) {
            $switches[McpConfigPolicy::KEY_VERB_PREFIX . $verb] = $enabled;
        }

        foreach ($switches as $key => $enabled) {
            if ($this->getConfigHelper()->getConfigValue($key) !== null) {
                continue;
            }
            $this->getConfigHelper()->setConfigValue($key, $enabled ? '1' : '0');
        }
    }

    private const SCREEN_NAME = 'Admin - MCP Configuration';
    private const SCREEN_ACTION = 'mcpConfig';

    /**
     * Prompts are data, not code.
     *
     * A prompt is wording whose value is only known once people use it, and
     * wording that can only change in a release does not change. A customer --
     * or support, on their behalf -- edits one, disables one or adds their own
     * with a few statements and no deployment. The template is plain text with
     * `{{argument}}` placeholders so it stays legible in a SQL client.
     */
    private function createPromptTable(): void
    {
        if ($this->getSchemaHelper()->tableExists(['ohrm_mcp_prompt'])) {
            return;
        }

        $this->getSchemaHelper()->createTable('ohrm_mcp_prompt')
            ->addColumn('id', Types::INTEGER, ['Autoincrement' => true, 'Notnull' => true])
            ->addColumn('name', Types::STRING, ['Length' => 100, 'Notnull' => true])
            ->addColumn('title', Types::STRING, ['Length' => 200, 'Notnull' => false])
            ->addColumn('description', Types::STRING, ['Length' => 500, 'Notnull' => false])
            ->addColumn('arguments', Types::TEXT, ['Notnull' => false])
            ->addColumn('template', Types::TEXT, ['Notnull' => true])
            ->addColumn('is_enabled', Types::BOOLEAN, ['Notnull' => true, 'Default' => true])
            ->setPrimaryKey(['id'])
            // The name is what a client passes back to prompts/get, so it is
            // the row's identity.
            ->addUniqueIndex(['name'], 'uk_mcp_prompt_name')
            ->create();
    }

    /**
     * Four starters, so the capability is visible rather than theoretical.
     *
     * Seeded only when the table is completely empty, which means a customer
     * who deletes one does not get it back on the next upgrade. That is the
     * opposite rule from the permission grid, and deliberately so: a missing
     * permission row silently removes access, while a missing prompt is just a
     * phrasing somebody decided against.
     */
    private function seedPrompts(): void
    {
        $existing = $this->createQueryBuilder()
            ->select('COUNT(prompt.id)')
            ->from('ohrm_mcp_prompt', 'prompt')
            ->executeQuery()
            ->fetchOne();

        if ((int) $existing > 0) {
            return;
        }

        foreach (self::STARTER_PROMPTS as $prompt) {
            $this->createQueryBuilder()
                ->insert('ohrm_mcp_prompt')
                ->values([
                    'name' => ':name',
                    'title' => ':title',
                    'description' => ':description',
                    'arguments' => ':arguments',
                    'template' => ':template',
                    'is_enabled' => ':isEnabled',
                ])
                ->setParameter('name', $prompt['name'])
                ->setParameter('title', $prompt['title'])
                ->setParameter('description', $prompt['description'])
                ->setParameter('arguments', $prompt['arguments'])
                ->setParameter('template', $prompt['template'])
                ->setParameter('isEnabled', 1)
                ->executeStatement();
        }
    }

    /**
     * Written against tools that ship enabled, so a starter prompt works on a
     * fresh install rather than asking for something switched off.
     */
    private const STARTER_PROMPTS = [
        [
            'name' => 'my_leave_balance',
            'title' => 'My leave balance',
            'description' => 'Summarise the signed-in employee\'s leave entitlement and balance by leave type.',
            'arguments' => null,
            'template' => 'Show my leave balance for each leave type I am eligible for. '
                . 'Resolve who I am first, then list entitlement, used and remaining days, '
                . 'and say which leave period the figures cover.',
        ],
        [
            'name' => 'team_on_leave',
            'title' => 'Who is on leave',
            'description' => 'List the people away on a given date, for planning cover.',
            'arguments' => '[{"name":"date","description":"Date to check, e.g. 2026-09-30. Defaults to today.","required":false}]',
            'template' => 'List the employees on approved leave on {{date}}. '
                . 'Group them by subunit, give the leave type and whether it is a full or half day, '
                . 'and say plainly if nobody is away.',
        ],
        [
            'name' => 'employee_profile',
            'title' => 'Employee profile summary',
            'description' => 'Pull one employee\'s core record together into a short briefing.',
            'arguments' => '[{"name":"employee","description":"Employee name or ID to look up.","required":true}]',
            'template' => 'Find the employee {{employee}} and summarise their record: '
                . 'job title, sub unit, employment status, supervisor and joined date. '
                . 'If more than one person matches, list the matches and stop rather than guessing.',
        ],
        [
            'name' => 'attendance_this_week',
            'title' => 'Attendance this week',
            'description' => 'Report punch-in and punch-out activity for an employee over a date range.',
            'arguments' => '[{"name":"employee","description":"Employee name or ID.","required":true},'
                . '{"name":"from","description":"Start date, e.g. 2026-09-21.","required":false},'
                . '{"name":"to","description":"End date, e.g. 2026-09-27.","required":false}]',
            'template' => 'Report attendance for {{employee}} between {{from}} and {{to}}. '
                . 'Give total hours per day, flag any day with no record, '
                . 'and note anything that looks like a missed punch out.',
        ],
    ];

    /**
     * Ships with both assignable roles allowed to connect.
     *
     * Safe because the feature itself is off: `mcp.config.enabled` defaults to
     * false, so an instance reaches nothing until an administrator turns the
     * master switch on, and at that point the question they are answering is
     * "should my people use this", not "which of the two roles did the product
     * happen to pick". LdapConfiguration ships the same way -- sensible values
     * inside, the switch off.
     *
     * Derived roles (Supervisor, HiringManager, ...) are computed per request
     * rather than assigned, so gating on them would not behave the way an
     * administrator expects; only `is_assignable` roles are seeded.
     */
    private function seedAllowedRoles(): void
    {
        if ($this->getConfigHelper()->getConfigValue(McpConfigPolicy::KEY_ALLOWED_ROLE_IDS) !== null) {
            return;
        }

        $roleIds = $this->createQueryBuilder()
            ->select('role.id')
            ->from('ohrm_user_role', 'role')
            ->where('role.name IN (:names)')
            ->setParameter('names', ['Admin', 'ESS'], Connection::PARAM_STR_ARRAY)
            ->andWhere('role.is_assignable = :assignable')
            ->setParameter('assignable', 1)
            ->executeQuery()
            ->fetchFirstColumn();

        $this->getConfigHelper()->setConfigValue(
            McpConfigPolicy::KEY_ALLOWED_ROLE_IDS,
            (string) json_encode(array_map('intval', $roleIds))
        );
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return '5.10.0';
    }
}
