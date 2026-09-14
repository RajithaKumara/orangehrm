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

namespace OrangeHRM\Mcp\Api;

use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\ResourceEndpoint;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Mcp\Service\McpAccessPolicy;
use OrangeHRM\Mcp\Traits\Service\McpAccessPolicyTrait;
use OrangeHRM\Mcp\Traits\Service\McpToolRegistryTrait;

/**
 * Read and write the organisation's MCP access policy.
 *
 * Admin-only by virtue of the `ohrm_api_permission` registration and the screen
 * permission installed by the V5_10_0 migration -- this endpoint decides who may
 * reach HR data through an AI assistant, so it is an administrative control.
 *
 * GET returns both the current settings and the reference data the screen needs
 * to render (roles, tools, OAuth clients) so the page needs one round trip.
 */
class McpAccessConfigAPI extends Endpoint implements ResourceEndpoint
{
    use McpAccessPolicyTrait;
    use McpToolRegistryTrait;
    use EntityManagerHelperTrait;

    public const PARAMETER_ENABLED = 'enabled';
    public const PARAMETER_MODE = 'mode';
    public const PARAMETER_ALLOWED_ROLE_IDS = 'allowedRoleIds';
    public const PARAMETER_USER_OVERRIDES = 'userOverrides';
    public const PARAMETER_ENABLED_TOOLS = 'enabledTools';
    public const PARAMETER_APPROVED_CLIENT_IDS = 'approvedClientIds';

    /**
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        $policy = $this->getMcpAccessPolicy();

        return new EndpointResourceResult(ArrayModel::class, [
            self::PARAMETER_ENABLED => $policy->isEnabled(),
            self::PARAMETER_MODE => $policy->getMode(),
            self::PARAMETER_ALLOWED_ROLE_IDS => $policy->getAllowedRoleIds(),
            self::PARAMETER_USER_OVERRIDES => $this->decorateOverrides($policy->getUserOverrides()),
            self::PARAMETER_ENABLED_TOOLS => $policy->getEnabledTools(),
            self::PARAMETER_APPROVED_CLIENT_IDS => $policy->getApprovedClientIds(),
            'clientApprovalEnforced' => $policy->isClientApprovalEnforced(),
            // Reference data, so the screen renders in one round trip.
            'availableRoles' => $this->getSelectableRoles(),
            'availableTools' => $this->getAvailableTools(),
            'availableClients' => $this->getOAuthClients(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        // The route supplies `id: 0` so GenericRestController routes GET to
        // getOne(); the validator rejects parameters it has not been told about.
        return new ParamRuleCollection(new ParamRule(CommonParams::PARAMETER_ID));
    }

    /**
     * @inheritDoc
     */
    public function update(): EndpointResult
    {
        $policy = $this->getMcpAccessPolicy();

        $policy->setEnabled(
            $this->getRequestParams()->getBoolean(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ENABLED)
        );
        $policy->setMode(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_MODE,
                McpAccessPolicy::MODE_READ_ONLY
            )
        );
        $policy->setAllowedRoleIds(
            $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ALLOWED_ROLE_IDS, [])
        );
        $policy->setEnabledTools(
            $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ENABLED_TOOLS, [])
        );
        $policy->setApprovedClientIds(
            $this->getRequestParams()->getArray(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_APPROVED_CLIENT_IDS,
                []
            )
        );

        // Overrides arrive as [{userId, allowed}, ...] because a JSON object with
        // numeric keys is awkward to validate and round-trips badly.
        $overrides = [];
        foreach (
            $this->getRequestParams()->getArray(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_USER_OVERRIDES,
                []
            ) as $entry
        ) {
            if (is_array($entry) && isset($entry['userId'])) {
                $overrides[(int) $entry['userId']] = !empty($entry['allowed']);
            }
        }
        $policy->setUserOverrides($overrides);

        return $this->getOne();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(CommonParams::PARAMETER_ID),
            new ParamRule(self::PARAMETER_ENABLED, new Rule(Rules::BOOL_VAL)),
            new ParamRule(
                self::PARAMETER_MODE,
                new Rule(Rules::IN, [[McpAccessPolicy::MODE_READ_ONLY, McpAccessPolicy::MODE_READ_WRITE]])
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_ALLOWED_ROLE_IDS, new Rule(Rules::ARRAY_TYPE))
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_USER_OVERRIDES, new Rule(Rules::ARRAY_TYPE))
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_ENABLED_TOOLS, new Rule(Rules::ARRAY_TYPE))
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(self::PARAMETER_APPROVED_CLIENT_IDS, new Rule(Rules::ARRAY_TYPE))
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * Roles a human is actually assigned. Derived roles such as Supervisor and
     * HiringManager are computed per request rather than assigned, so gating on
     * them here would not behave the way an administrator expects.
     */
    private function getSelectableRoles(): array
    {
        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT id, name, display_name FROM ohrm_user_role WHERE is_assignable = 1 ORDER BY name'
        );

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'displayName' => $row['display_name'] ?? $row['name'],
            ];
        }, $rows);
    }

    private function getAvailableTools(): array
    {
        $tools = [];
        foreach ($this->getMcpToolRegistry()->all() as $tool) {
            $tools[] = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'module' => $tool->getModule(),
            ];
        }
        return $tools;
    }

    private function getOAuthClients(): array
    {
        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT client_id, name, is_confidential FROM ohrm_oauth2_client WHERE enabled = 1 ORDER BY name'
        );

        return array_map(static function (array $row): array {
            return [
                'clientId' => $row['client_id'],
                'name' => $row['name'],
                'confidential' => (bool) $row['is_confidential'],
            ];
        }, $rows);
    }

    /**
     * @param array<int,bool> $overrides
     * @return array<int,array{userId:int,userName:string,allowed:bool}>
     */
    private function decorateOverrides(array $overrides): array
    {
        if ($overrides === []) {
            return [];
        }

        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT id, user_name FROM ohrm_user WHERE id IN (:ids)',
            ['ids' => array_keys($overrides)],
            ['ids' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
        );

        $names = [];
        foreach ($rows as $row) {
            $names[(int) $row['id']] = $row['user_name'];
        }

        $decorated = [];
        foreach ($overrides as $userId => $allowed) {
            $decorated[] = [
                'userId' => $userId,
                // A user deleted after the override was saved still shows, so an
                // administrator can see and clear the stale entry.
                'userName' => $names[$userId] ?? ('(deleted user #' . $userId . ')'),
                'allowed' => $allowed,
            ];
        }
        return $decorated;
    }
}
