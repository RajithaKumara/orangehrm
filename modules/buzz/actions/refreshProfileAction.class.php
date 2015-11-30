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
 * Description of refreshPageAction
 *
 * @author aruna
 */
class refreshProfileAction extends BaseBuzzAction {

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
     * this is reurn form to edit comment
     * @return CommentEditForm
     */
    private function getEditForm() {
        if (!($this->editForm instanceof CommentForm)) {
            $this->editForm = new CommentForm();
        }
        return $this->editForm;
    }

    /**
     * function to set comment form
     * @param CommentForm
     */
    private function setCommentForm($form) {
        $this->commentForm = $form;
    }

    /**
     * this is to get comment form
     * @return CommentForm
     */
    private function getCommentForm() {
        if (!($this->commentForm instanceof CommentForm)) {
            $this->setCommentForm(new CommentForm());
        }
        return $this->commentForm;
    }

    public function execute($request) {
        try {

            $this->setForm(new LoadMorePostsForm());

            if ($request->isMethod('post')) {
                $this->form->bind($request->getParameter($this->form->getName()));
                if ($this->form->isValid()) {
                    $formValues = $this->form->getValues();
                    
                    $this->loggedInUser = $this->getLogedInEmployeeNumber();
                    $this->lastPostId = $formValues['lastPostId'];
                    $this->profileUserId = $formValues['profileUserId'];
                    
                    $this->buzzService = $this->getBuzzService();
                    $this->loggedInUser = $this->getLogedInEmployeeNumber();
                    $this->fullSharesList = $this->buzzService->getEmployeeSharesUptoShareId($this->lastPostId, $this->profileUserId);
                    $this->commentForm = $this->getCommentForm();
                    $this->editForm = $this->getEditForm();
                }
            }
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

}
