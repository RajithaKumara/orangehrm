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
 * Description of OAuthClientRegistrationForm
 *
 * @author orangehrm
 */
class OAuthClientRegistrationForm extends sfForm {
    protected $oauthService = null;

    /**
     * @return OAuthService
     */
    public function getOAuthService(): OAuthService
    {
        if (is_null($this->oauthService)) {
            $this->oauthService = new OAuthService();
        }
        return $this->oauthService;
    }

    /**
     * @param OAuthService $oauthService
     */
    public function setOAuthService(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function configure() {
        
        $this->setWidgets($this->getFormWidgets());
        $this->setValidators($this->getFormValidators());
        $this->widgetSchema->setLabels($this->getFormLabels());
        $this->getWidgetSchema()->setNameFormat('oauth[%s]');
    }
    
    public function getFormWidgets() {
        $widgets = array(
            'client_id' => new sfWidgetFormInputText(),
            'client_secret' => new sfWidgetFormInputText(),
            'redirect_uri' => new sfWidgetFormInputText(),
            'client_update' => new sfWidgetFormInputText(),
            'client_grant_types' => new sfWidgetFormSelectCheckbox(
                ['choices' => $this->getAllowedGrantTypes()]
            ),
            'client_scopes' => new sfWidgetFormSelectCheckbox(
                ['choices' => $this->getAllowedScopes()]
            ),
        );
        return $widgets;
    }
    
    public function getFormValidators() {
        $validators = array(
            'client_id' => new sfValidatorString(array('required' => true)),
            'client_secret' => new sfValidatorString(array('required' => false)),
            'redirect_uri' => new sfValidatorString(array('required' => false)),
            'client_update' => new sfValidatorString(array('required' => false)),
            'client_grant_types' => new sfValidatorChoice(
                array('choices' => array_keys($this->getAllowedGrantTypes()), 'multiple' => true)
            ),
            'client_scopes' => new sfValidatorChoice(
                array('choices' => array_keys($this->getAllowedScopes()), 'multiple' => true)
            ),
        );
        return $validators;
    }
    
    public function getFormLabels() {
        $labels = array(
            'client_id' => __("ID") . " <em>*</em>",
            'client_secret' => __("Secret"),
            'redirect_uri' => __("Redirect URI"),
            'client_update' => __(" "),
            'client_grant_types' => __("Allowed Grant Types") . " <em>*</em>",
            'client_scopes' => __("Allowed Scopes") . " <em>*</em>",
        );
        return $labels;
    }

    public function getAllowedGrantTypes()
    {
        return [
            GrantType::CLIENT_CREDENTIALS => __('Client Credentials') . sprintf(' (%s)', GrantType::CLIENT_CREDENTIALS),
            GrantType::USER_CREDENTIALS => __('User Credentials') . sprintf(' (%s)', GrantType::USER_CREDENTIALS),
            GrantType::REFRESH_TOKEN => __('Refresh Token') . sprintf(' (%s)', GrantType::REFRESH_TOKEN)
        ];
    }

    public function getAllowedScopes()
    {
        $allowedScopes = [];
        $scopes = $this->getOAuthService()->listOAuthScopes();
        foreach ($scopes as $scope) {
            $allowedScopes[$scope->getScope()] = $scope->getScope();
        }
        return $allowedScopes;
    }
}
