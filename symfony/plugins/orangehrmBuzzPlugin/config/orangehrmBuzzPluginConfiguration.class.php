<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

/**
 * Configuration class for the Buzz Plugin.
 * Registers this module as a plugin.
 *
 * @author aruna
 */
class orangehrmBuzzPluginConfiguration extends sfPluginConfiguration {
    public function initialize() {  
        $enabledModules = sfConfig::get('sf_enabled_modules');  
        if (is_array($enabledModules)) {  
            sfConfig::set('sf_enabled_modules', array_merge(sfConfig::get('sf_enabled_modules'), array('buzz')));  
        } 
     // $this->dispatcher->connect('routing.load_configuration', array($this, 'listenToRoutingLoadConfigurationEvent'));
    
    }
   

//    public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {
//
//        $r = $event->getSubject();
//
//        $r->prependRoute('viewNewDashbord', new sfRoute('/dashboard/*', array('module' => 'buzz', 'action' => 'viewNewDashbord')));
//       
//   }
}
