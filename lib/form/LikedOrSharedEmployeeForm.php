<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LikedOrSharedEmployeeForm
 *
 * @author nirmal
 */
class LikedOrSharedEmployeeForm extends BaseForm {

    public function configure() {
        $this->setWidgets($this->getWidgets());
        $this->setValidators($this->getValidators());
        $this->widgetSchema->setNameFormat($this->getNameFormat());
    }

    public function getWidgets() {
        $widgets = array(
            'id' => new sfWidgetFormInputHidden(),
            'type' => new sfWidgetFormInputHidden(),
            'event' => new sfWidgetFormInputHidden()
        );
        return $widgets;
    }

    public function getValidators() {
        $validators = array(
            'id' => new sfValidatorString(array('required' => false)),
            'type' => new sfValidatorString(array('required' => false)),
            'event' => new sfValidatorString(array('required' => false))
        );
        return $validators;
    }

    public function getNameFormat() {
        return 'likedOrSharedEmployeeForm[%s]';
    }

}
