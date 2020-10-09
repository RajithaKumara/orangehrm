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
 *
 */

class languagePackageAction extends baseAdminAction
{
    /**
     * @var null|I18NService
     */
    private $i18nService = null;

    public function execute($request)
    {
        $this->getI18NService()->syncI18NSourcesLangStrings();

        $this->_setListComponent(
            $this->getI18NService()->getLanguages(),
            new ResourcePermission(true, true, true, true)
        );
    }

    private function _setListComponent($languageList, $permissions)
    {
        $configurationFactory = $this->_getConfigurationFactory();
        $runtimeDefinitions = [];
        $buttons = [];

        if ($permissions->canCreate()) {
            $buttons['Add'] = ['label' => 'Add'];
        }

        if (!$permissions->canDelete()) {
            $runtimeDefinitions['hasSelectableRows'] = false;
        } else {
            $buttons['Delete'] = [
                'label' => 'Delete',
                'type' => 'submit',
                'data-toggle' => 'modal',
                'data-target' => '#deleteConfModal',
                'class' => 'delete'
            ];
        }

        $runtimeDefinitions['title'] = __('Language Packages');
        $runtimeDefinitions['buttons'] = $buttons;
        $configurationFactory->setRuntimeDefinitions($runtimeDefinitions);
        ohrmListComponent::setActivePlugin('orangehrmAdminPlugin');
        ohrmListComponent::setConfigurationFactory($configurationFactory);
        ohrmListComponent::setListData($languageList);
    }

    /**
     * @return LanguagePackageHeaderFactory
     */
    private function _getConfigurationFactory()
    {
        return new LanguagePackageHeaderFactory();
    }

    /**
     * @return I18NService
     */
    private function getI18NService()
    {
        if (is_null($this->i18nService)) {
            $this->i18nService = new I18NService();
        }
        return $this->i18nService;
    }
}
