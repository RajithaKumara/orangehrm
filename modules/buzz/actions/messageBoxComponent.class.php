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
 * Description of messageBoxComponent
 *
 * @author aruna
 */
class messageBoxComponent extends sfComponent {

    const SUCCESS_HEADING = "Success";
    const ERROR_HEADING = "Error!";
    const CONFIRM_HEADING = "Confirm";
    const DELETE_MESSAGE_TYPE = "delete";
    const DELETE_MESSAGE_BODY = "Do you really want to delete this?";
    const HIDDEN_ELEMENT_CLASS = "hidden-element";
    const DELETE_CONFIRM_BTN_ID = "delete_confirm";
    const DELETE_DISCARD_BTN_ID = "delete_discard";

    public function execute($request) {
        
        switch ($this->messageType){
            case messageBoxComponent::DELETE_MESSAGE_TYPE:
                $this->messageHeading = messageBoxComponent::CONFIRM_HEADING;
                $this->messageBody = messageBoxComponent::DELETE_MESSAGE_BODY;
                $this->okBtnClass = messageBoxComponent::HIDDEN_ELEMENT_CLASS;
                $this->yesBtnId = messageBoxComponent::DELETE_CONFIRM_BTN_ID;
                $this->noBtnId = messageBoxComponent::DELETE_DISCARD_BTN_ID;
                break;
        }
        
    }

}
