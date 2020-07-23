<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OAuthClientRegistrationForm
 *
 * @author orangehrm
 */
class OAuthClientRegistrationForm extends sfForm {
    
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
            'client_grant_types' => new sfWidgetFormInputText(),
            'client_scopes' => new sfWidgetFormInputText(),
        );
        return $widgets;
    }
    
    public function getFormValidators() {
        $validators = array(
            'client_id' => new sfValidatorString(array('required' => true)),
            'client_secret' => new sfValidatorString(array('required' => true)),
            'redirect_uri' => new sfValidatorString(array('required' => false)),
            'client_update' => new sfValidatorString(array('required' => false)),
            'client_grant_types' => new sfValidatorString(array('required' => true)),
            'client_scopes' => new sfValidatorString(array('required' => true)),
        );
        return $validators;
    }
    
    public function getFormLabels() {
        $labels = array(
            'client_id' => __("ID") . " <em>*</em>",
            'client_secret' => __("Secret") . " <em>*</em>",
            'redirect_uri' => __("Redirect URI"),
            'client_update' => __(" "),
            'client_grant_types' => __("Allowed Grant Types") . " <em>*</em>",
            'client_scopes' => __("Allowed Scopes") . " <em>*</em>",
        );
        return $labels;
    }
}

?>
