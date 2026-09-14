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

namespace OrangeHRM\Mcp\Subscriber;

use OrangeHRM\Mcp\Service\McpConfigurationService;
use OrangeHRM\Core\Event\ModuleEvent;
use OrangeHRM\Core\Event\ModuleStatusChange;
use OrangeHRM\Framework\Event\AbstractEventSubscriber;

class McpModuleStatusChangeSubscriber extends AbstractEventSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            ModuleEvent::MODULE_STATUS_CHANGE => [['onStatusChange', 0]],
        ];
    }

    public function onStatusChange(ModuleStatusChange $event): void
    {
        // Registered seam for later epics: clear MCP session cache (Epic 2),
        // invalidate the cached OpenAPI spec (Epic 2.5), and reset
        // permission-profile sidecars (Epic 2.7) when the mcp module
        // toggles. v1 has no such caches; intentionally a no-op.
        if ($event->getCurrentModule()->getName() !== McpConfigurationService::MODULE_NAME) {
            return;
        }
    }
}
