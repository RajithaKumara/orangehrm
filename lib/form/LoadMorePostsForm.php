<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LoadMorePostsForm
 *
 * @author nirmal
 */
class LoadMorePostsForm extends BaseForm {

    public function configure() {
        $this->setWidgets($this->getWidgets());
        $this->setValidators($this->getValidators());
        $this->widgetSchema->setNameFormat($this->getNameFormat());
    }

    public function getWidgets() {
        $widgets = array('lastPostId' => new sfWidgetFormInputHidden(),
            'profileUserId' => new sfWidgetFormInputHidden()
        );
        return $widgets;
    }

    public function getValidators() {
        $validators = array('lastPostId' => new sfValidatorString(array('required' => false)),
            'profileUserId' => new sfValidatorString(array('required' => false))
        );
        return $validators;
    }

    public function getNameFormat() {
        return 'loadMorePostsForm[%s]';
    }

}
