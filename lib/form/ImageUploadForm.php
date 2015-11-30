<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImageUploadForm
 *
 * @author nirmal
 */
class ImageUploadForm extends BaseForm {

    public function configure() {
        $this->setWidgets($this->getWidgets());
        $this->setValidators($this->getValidators());
        $this->widgetSchema->setNameFormat($this->getNameFormat());
    }

    public function getWidgets() {
        $widgets = array();
        return $widgets;
    }

    public function getValidators() {
        $validators = array();
        return $validators;
    }

    public function getNameFormat() {
        return 'imageUploadForm[%s]';
    }

}
