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
 * Admin > Configuration > MCP Configuration.
 *
 * Access to this screen is governed by the `ohrm_user_role_screen` grant the
 * V5_10_0 migration installs for the Admin role, the same mechanism every other
 * admin screen uses.
 */
class McpConfigController extends AbstractVueController
{
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('mcp-config');

        // The endpoint an administrator must hand to the AI client. Computed
        // here rather than in the page so subpath and reverse-proxy installs
        // show the URL that actually works.
        $component->addProp(new Prop(
            'mcp-endpoint',
            Prop::TYPE_STRING,
            Metadata::getAppOrigin($request) . Metadata::MCP_RESOURCE_PATH
        ));

        // The URL that actually serves the document, which on a subpath install
        // is not where RFC 8414 says to look: a compliant client strips the path
        // and asks the host root, and that only answers if the webserver
        // rewrites it. Showing the working URL is the useful thing here --
        // an administrator pastes it into a client that cannot discover the
        // endpoint itself, and pasting one that 404s helps nobody.
        $component->addProp(new Prop(
            'oauth-metadata-url',
            Prop::TYPE_STRING,
            Metadata::getAppOrigin($request) . Metadata::AUTHORIZATION_SERVER_PATH
        ));

        $this->setComponent($component);
    }
}
