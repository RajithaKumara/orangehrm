<?php

/*
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

class viewShareAction extends BaseBuzzAction {

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
     * get share By Share Id
     * @return Share
     */
    public function getShare($shareId) {
        return $this->getbuzzService()->getShareById($shareId);
    }

    /**
     * 
     * @param AddTaskForm $form
     */
    private function setCommentForm($form) {
        $this->commentForm = $form;
    }

    /**
     * function to get comment form
     * @return Comment form
     */
    private function getEditForm() {
        if (!$this->editForm) {
            $this->editForm = new CommentForm();
        }
        return $this->editForm;
    }

    /**
     * get comment form 
     * @return CommentForm
     */
    private function getCommentForm() {
        if (!$this->commentForm) {
            $this->setCommentForm(new CommentForm());
        }
        return $this->commentForm;
    }

    public function execute($request) {

        try {
            $this->setForm(new DeleteOrEditShareForm());
            $this->commentForm = $this->getCommentForm();
            $this->editForm = $this->getEditForm();
            if ($request->isMethod('post')) {
                $this->form->bind($request->getParameter($this->form->getName()));
                if ($this->form->isValid()) {
                    $formValues = $this->form->getValues();

                    $this->loggedInUser = $this->getLogedInEmployeeNumber();
                    $this->shareId = $formValues['shareId'];
                    $share = $this->getShare($this->shareId);
                    $this->userId = $this->getLogedInEmployeeNumber(); 
                    $this->setShare($share);

                }
            }
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

    /**
     * set share valuves to show
     * @param type $post
     */
    private function setShare($post) {
        $this->postId = $post->getId();
        $this->postEmployee = $post->getEmployeePostShared();
        $this->postEmployeeJobTitle = $this->postEmployee->getJobTitleName();
        $this->postDate = $post->getDate();
        $this->postTime = $post->getTime();
        $this->postContent = $post->getText();
        $this->postNoOfLikes = $post->getLike()->count();
        $this->postUnlike = $post->getNumberOfUnlikes();
        $this->postShareCount = $post->calShareCount();
        $this->postType = $post->getType();
        $this->employeeID = $post->getEmployeeNumber();
        $this->commentList = $post->getComment();
        $this->postEmployeeName = $post->getEmployeeFirstLastName();
        if ($this->postEmployeeName == ' ' && $post->getEmployeeName() != null) {
            $this->postEmployeeName = $post->getEmployeeName() . ' (' . __(self::LABEL_EMPLOYEE_DELETED) . ')';
            $this->postSharerDeleted = true;
        }
        $this->isLike = $post->isLike($this->loggedInUser);
        $this->isUnlike = $post->isUnLike($this->loggedInUser);
        $this->originalPost = $post->getPostShared();
        $this->originalPostId = $this->originalPost->getId();
        $this->originalPostEmpNumber = $this->originalPost->getEmployeeNumber();
        $this->originalPostSharerName = $this->originalPost->getEmployeeFirstLastName();
        if ($this->originalPostSharerName == ' ' && $this->originalPost->getEmployeeName() != null) {
            $this->originalPostSharerName = $this->originalPost->getEmployeeName() . ' (' . __(self::LABEL_EMPLOYEE_DELETED) . ')';
            $this->originalPostSharerDeleted = true;
        }
        $this->originalPostDate = $this->originalPost->getDate();
        $this->originalPostTime = $this->originalPost->getTime();
        $this->originalPostContent = $this->originalPost->getText();
        $this->likeEmployeList = $post->getLikedEmployeeList();
    }

}
