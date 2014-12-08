<?php

/**
 * OrangeHRM Enterprise is a closed sourced comprehensive Human Resource Management (HRM)
 * System that captures all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM Inc is the owner of the patent, copyright, trade secrets, trademarks and any
 * other intellectual property rights which subsist in the Licensed Materials. OrangeHRM Inc
 * is the owner of the media / downloaded OrangeHRM Enterprise software files on which the
 * Licensed Materials are received. Title to the Licensed Materials and media shall remain
 * vested in OrangeHRM Inc. For the avoidance of doubt title and all intellectual property
 * rights to any design, new software, new protocol, new interface, enhancement, update,
 * derivative works, revised screen text or any other items that OrangeHRM Inc creates for
 * Customer shall remain vested in OrangeHRM Inc. Any rights not expressly granted herein are
 * reserved to OrangeHRM Inc.
 *
 * Please refer http://www.orangehrm.com/Files/OrangeHRM_Commercial_License.pdf for the license which includes terms and conditions on using this software.
 *
 */

/**
 * Description of UploadPhotoForm
 *
 * @author aruna
 */
class UploadPhotoForm extends BaseForm {

    protected $widgets = array();

    public function configure() {

        $photographWidgets = $this->getPhotographWidgets();
        $photographValidators = $this->getPhotographValidators();

        $this->widgets = array_merge($this->widgets, $photographWidgets);
        $validators = array_merge($validators, $photographValidators);
        $this->setWidgets($this->widgets);
        $this->setValidators($photographValidators);
        $this->widgetSchema->setLabel('photofile', false);
        $this->widgetSchema->setLabel('phototext', false);
    }

    /**
     * Get form widgets
     * @return \sfWidgetFormInputFileEditable 
     */
    private function getPhotographWidgets() {
        $fileInput = new sfWidgetFormInputFileEditable(array(
            'edit_mode' => false,
            'with_delete' => false,
            'file_src' => ''));
        $fileInput->setAttribute('multiple', true);
        $inputText = new sfWidgetFormTextarea();
        $inputText->setAttribute('placeholder', 'Say something about these photos');
        $inputText->setAttribute('rows', '1');
        $widgets = array(
            'phototext' => $inputText,
            'photofile' => $fileInput
        );
        return $widgets;
    }

    /**
     * Get validators
     * @return \sfValidatorFile 
     */
    private function getPhotographValidators() {
        $validators = array(
            'photofile' => new sfValidatorFile(
                    array(
                'max_size' => 5000000,
                'required' => true,
                    ))
        );
        return $validators;
    }

}
