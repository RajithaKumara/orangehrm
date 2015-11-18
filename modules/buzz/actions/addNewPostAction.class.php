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
 * Description of addNewPostAction
 *
 * @author aruna
 */
class addNewPostAction extends BaseBuzzAction {

    /**
     * 
     * @param CommentForm $form
     */
    private function setCommentForm($form) {
        $this->commentForm = $form;
    }

    /**
     * 
     * @return CommentForm
     */
    private function getCommentForm() {
        if (!$this->commentForm) {
            $this->setCommentForm(new CommentForm());
        }
        return $this->commentForm;
    }

    /**
     * 
     * @return PostForm
     */
    private function getPostForm() {
        if (!$this->postForm) {
            $this->postForm = new CreatePostForm();
        }
        return $this->postForm;
    }

    /**
     * 
     * @return CommentForm
     */
    private function getEditForm() {
        if (!$this->editForm) {
            $this->editForm = new CommentForm();
        }
        return $this->editForm;
    }

    public function execute($request) {

        $this->form = $this->getPostForm();
        $this->loggedInUser = $this->getLogedInEmployeeNumber();
        
        if($this->loggedInUser){
            $loggedInEmployee = $this->getEmployeeService()->getEmployee($this->loggedInUser );
        }

        $this->isSuccessfullyAddedPost = false;
        if ($request->isMethod('post')) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->postSaved = $this->form->save($this->loggedInUser, $loggedInEmployee);
                $this->isSuccessfullyAddedPost = true;
            } else {
                
            }
        }
        $this->commentForm = $this->getCommentForm();
        $this->editForm = $this->getEditForm();
    }

}
