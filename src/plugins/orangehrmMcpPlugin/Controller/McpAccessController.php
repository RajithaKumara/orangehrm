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

namespace OrangeHRM\Mcp\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Mcp\Controller\OAuthMetadataController as Metadata;

/**
 * Admin > Configuration > MCP Access.
 *
 * Access to this screen is governed by the `ohrm_user_role_screen` grant the
 * V5_10_0 migration installs for the Admin role, the same mechanism every other
 * admin screen uses.
 */
class McpAccessController extends AbstractVueController
{
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('mcp-access');

        // The endpoint an administrator must hand to the AI client. Computed
        // here rather than in the page so subpath and reverse-proxy installs
        // show the URL that actually works.
        $component->addProp(new Prop(
            'mcp-endpoint',
            Prop::TYPE_STRING,
            Metadata::getAppOrigin($request) . Metadata::MCP_RESOURCE_PATH
        ));

        $this->setComponent($component);
    }
}
