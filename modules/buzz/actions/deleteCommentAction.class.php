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
class deleteCommentAction extends BaseBuzzAction {

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
     * get Comment By It Id
     * @param type $commentId
     * @return type
     */
    private function getComment($commentId) {
        return $this->getBuzzService()->getCommentById($commentId);
    }

    public function execute($request) {
        try {

            $this->setForm(new DeleteOrEditCommentForm());

            if ($request->isMethod('post')) {
                $this->form->bind($request->getParameter($this->form->getName()));
                if ($this->form->isValid()) {
                    $formValues = $this->form->getValues();

                    $this->loggedInUser = $this->getLogedInEmployeeNumber();
                    $this->commentId = $formValues['commentId'];
                    $comment = $this->getComment($this->commentId);
                    $commentedEmployeeNumber = $comment->getEmployeeNumber();
                    if ($commentedEmployeeNumber == $this->loggedInUser || $this->getLoggedInEmployeeUserRole() == 'Admin') {
                        $this->deleteComment($comment);
                    }
                }
            }
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }

        return sfView::NONE;
    }

    /**
     * delete comment 
     */
    private function deleteComment($comment) {
        $this->getBuzzService()->deleteCommentForShare($comment);
    }

}
