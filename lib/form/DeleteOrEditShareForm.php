<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DeleteOrEditShareForm
 *
 * @author nirmal
 */
class DeleteOrEditShareForm extends BaseForm {

    public function configure() {
        $this->setWidgets($this->getWidgets());
        $this->setValidators($this->getValidators());
        $this->widgetSchema->setNameFormat($this->getNameFormat());
    }

    public function getWidgets() {
        $widgets = array('shareId' => new sfWidgetFormInputHidden(),
            'textShare' => new sfWidgetFormInputHidden()
        );
        return $widgets;
    }

    public function getValidators() {
        $validators = array('shareId' => new sfValidatorString(array('required' => false)),
            'textShare' => new sfValidatorString(array('required' => false))
        );
        return $validators;
    }

    public function getNameFormat() {
        return 'deleteOrEditShareForm[%s]';
    }

}
