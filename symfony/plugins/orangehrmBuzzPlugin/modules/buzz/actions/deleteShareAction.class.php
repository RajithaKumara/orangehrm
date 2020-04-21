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
 * Description of likeOnShareAction
 *
 * @author aruna
 */
class deleteShareAction extends BaseBuzzAction {

    /**
     * @param sfForm $form
     * @return
     */
    protected function setForm(sfForm $form) {
        if (is_null($this->form)) {
            $this->form = $form;
        }
    }

    /**
     * get share by Id and return it
     * @param type $shareId
     * @return Share
     */
    public function getShare($shareId) {
        return $this->getBuzzService()->getShareById($shareId);
    }

    /**
     * delete Share if it bis post then delete post
     */
    public function deleteShare($share) {
        try {
            if ($share->getEmployeeNumber() == $this->getLogedInEmployeeNumber() || $this->getLoggedInEmployeeUserRole() == 'Admin') {
                $this->getBuzzService()->deleteShare($share->getId());
            }
        } catch (Exception $ex) {
            
        }
    }

    /**
     * delete post by Id 
     * @param type $share
     */
    private function deletePost($share) {
        if (($share->getPostShared()->getEmployeeNumber() == $this->getLogedInEmployeeNumber()) || $this->getLoggedInEmployeeUserRole() == 'Admin') {
            $this->getBuzzService()->deletePost($share->getPostId());
        }
    }

    public function execute($request) {
        try {
            $this->setForm(new DeleteOrEditShareForm());

            if ($request->isMethod('post')) {
                $this->form->bind($request->getParameter($this->form->getName()));
                if ($this->form->isValid()) {
                    $formValues = $this->form->getValues();

                    $this->loggedInUser = $this->getLogedInEmployeeNumber();
                    $this->shareId = $formValues['shareId'];
                    
                    $share = $this->getShare($this->shareId);
                    if ($share->getType() == 0) {
                        $this->deletePost($share);
                    } else {
                        $this->deleteShare($share);
                    }
                }
            }
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }

        return sfView::NONE;
    }

}
