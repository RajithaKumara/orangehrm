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
 * Description of viewBuzzAction
 *
 * @author aruna
 */
class viewBuzzAction extends BaseBuzzAction {

    /**
     * function to set post form
     * @param PostForm $form
     */
    private function setPostForm($form) {
        $this->postForm = $form;
    }

    /**
     * get Post form
     * @return PostForm
     */
    private function getPostForm() {
        if (!$this->postForm) {
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
     * function to get upload photo form
     * @return AddTaskForm
     */
    private function getUploadImageForm() {
        if (!$this->uploadImageForm) {
            $this->uploadImageForm = new UploadPhotoForm();
        }
        return $this->uploadImageForm;
    }

    /**
     * function to get upload photo form
     * @return AddTaskForm
     */
    private function getVideoForm() {
        if (!$this->videoForm) {
            $this->videoForm = new CreateVideoForm();
        }
        return $this->videoForm;
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

    /**
     * return action validate form for validate actions
     * @return ActionValidateForm
     */
    private function getActionValidateForm() {
        if (!$this->actionValidateForm instanceof ActionValidatingForm) {
            $this->actionValidateForm = new ActionValidatingForm();
        }
        return $this->actionValidateForm;
    }

    public function execute($request) {

//echo date("h:i:s", $this->getUser()->getLastRequestTime());die;
//        echo sfConfig::getAll();die;
        $template = $this->getContext()->getConfiguration()->getTemplateDir('buzz', 'chatter.php');
        $this->setLayout($template . '/chatter');

        try {
            $this->loggedInUser = $this->getLogedInEmployeeNumber();
            $this->setConfigurationValues();
            $this->postForm = $this->getPostForm();
            $this->commentForm = $this->getCommentForm();
            $this->editForm = $this->getEditForm();
            $this->uploadImageForm = $this->getUploadImageForm(); //image upload form
            $this->actionValidateForm = $this->getActionValidateForm();
            $this->buzzService = $this->getBuzzService();
            $this->allShareCount = $this->buzzService->getSharesCount();
            $this->initializePostList();
            $this->videoForm = $this->getVideoForm();  // video form added
            $this->employeeList = $this->buzzService->getEmployeesHavingBdaysBetweenTwoDates(date("Y-m-d"), date('Y-m-t'));
            $this->anniversaryEmpList = $this->buzzService->getEmployeesHavingAnniversaryOnMonth(date("Y-m-d"));
        } catch (Exception $ex) {
            $this->forward('auth', 'login');
        }
        
        $this->getUser()->setAuthenticated(FALSE);
//        echo $this->getUser()->isTimedOut(); die;
    }

    /**
     * Retrieving the list of posts from database.
     */
    protected function initializePostList() {
        $this->postList = $this->getBuzzService()->getShares($this->shareCount);
    }

    protected function setConfigurationValues() {
        $buzzConfigService = $this->getBuzzConfigService();
        $this->shareCount = $buzzConfigService->getBuzzShareCount();
        $this->commentCount = $buzzConfigService->getBuzzInitialCommentCount();
        $this->viewMoreComment = $buzzConfigService->getBuzzViewCommentCount();
        $this->likeCount = $buzzConfigService->getBuzzLikeCount();
        $this->refeshTime = $buzzConfigService->getRefreshTime();
    }

}
