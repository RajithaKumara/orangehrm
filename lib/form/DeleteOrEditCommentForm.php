<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DeleteOrEditCommentForm
 *
 * @author nirmal
 */
class DeleteOrEditCommentForm extends BaseForm {
    
    public function configure() {
        $this->setWidgets($this->getWidgets());
        $this->setValidators($this->getValidators());
        $this->widgetSchema->setNameFormat($this->getNameFormat());
    }

    public function getWidgets() {
        $widgets = array('commentId' => new sfWidgetFormInputHidden(),
            'textComment' => new sfWidgetFormInputHidden()
        );
        return $widgets;
    }

    public function getValidators() {
        $validators = array('commentId' => new sfValidatorString(array('required' => false)),
            'textComment' => new sfValidatorString(array('required' => false))
        );
        return $validators;
    }

    public function getNameFormat() {
        return 'deleteOrEditCommentForm[%s]';
    }
    
    
}
