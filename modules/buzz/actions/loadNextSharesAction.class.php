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
 * Description of loadNextSharesAction
 *
 * @author aruna
 */
class loadNextSharesAction extends BaseBuzzAction {

    /**
     * 
     * @param AddTaskForm $form
     */
    private function setPostForm($form) {
        $this->postForm = $form;
    }

    /**
     * 
     * @return AddTaskForm
     */
    private function getPostForm() {
        if (!($this->postForm instanceof CreatePostForm)) {
            $this->setPostForm(new CreatePostForm());
        }
        return $this->postForm;
    }

    /**
     * 
     * @param AddTaskForm $form
     */
    private function setCommentForm($form) {
        $this->commentForm = $form;
    }

    /**
     * 
     * @return AddTaskForm
     */
    private function getCommentForm() {
        if (!($this->commentForm instanceof CommentForm)) {
            $this->setCommentForm(new CommentForm());
        }
        return $this->commentForm;
    }
    
    /**
     * 
     * @return AddTaskForm
     */
    private function getEditForm() {
        if (!($this->editForm instanceof CommentForm)) {
            $this->editForm=new CommentForm();
        }
        return $this->editForm;
    }

    public function execute($request) {
        try {
            $this->loggedInUser = $this->getLogedInEmployeeNumber();
            $this->lastPostId = $request->getParameter('lastPostId');
            $this->buzzService = $this->getBuzzService();

            $this->nextSharesList = $this->buzzService->getMoreShares(5, $this->lastPostId);
            $this->editForm = $this->getEditForm();
            $this->commentForm = $this->getCommentForm();
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

}
