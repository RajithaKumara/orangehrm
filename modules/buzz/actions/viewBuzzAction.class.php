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
class viewBuzzAction extends BuzzBaseAction {

    protected $buzzService;
    protected $buzzConfigService;

    public function execute($request) {

        $template = $this->getContext()->getConfiguration()->getTemplateDir('buzz', 'chatter.php');
        $this->setLayout($template . '/chatter');
        $this->intializeConstant();
        $this->postForm = $this->getPostForm();
        $this->commentForm = $this->getCommentForm();
        $this->editForm = new CommentForm();
        $this->uploadImageForm = new UploadPhotoForm(); //image upload form
        $this->setBuzzService(new BuzzService());
        $this->initializePostList();
        try {
            $this->loggedInUser = $this->getUserId();
        } catch (Exception $ex) {
           $this->redirect('auth/login');
        }


        $this->videoForm = new CreateVideoForm();  // video form added
        $this->employeeList = $this->buzzService->getEmployeesHavingBdaysBetween(date("Y-m-d"), date('Y-m-t'));
        $this->anniversaryEmpList = $this->buzzService->getEmployeesHavingAnniversaryOn(date("m"));
    }

    /**
     * Retrieving the list of posts from database.
     */
    protected function initializePostList() {
        $buzzService = $this->getBuzzService();

        $this->postList = $buzzService->getShares($this->shareCount);
    }

    protected function intializeConstant() {
        $buzzConfigService = $this->getBuzzConfigService();
        $this->shareCount = $buzzConfigService->getBuzzShareCount();
        $this->commentCount = $buzzConfigService->getBuzzInitialCommentCount();
        $this->viewMoreComment = $buzzConfigService->getBuzzViewCommentCount();
        $this->likeCount = $buzzConfigService->getBuzzLikeCount();
        $this->refeshTime = $buzzConfigService->getRefreshTime();
    }

    /**
     * 
     * @param type $buzzService
     */
    protected function setBuzzService($buzzService) {
        $this->buzzService = $buzzService;
    }

    /**
     * 
     * @return BuzzService
     */
    private function getBuzzService() {
        if (!$this->buzzService) {
            $this->setBuzzService(new BuzzService());
        }
        return $this->buzzService;
    }

    /**
     * 
     * @return BuzzConfigService
     */
    private function getBuzzConfigService() {
        if (!$this->buzzConfigService) {
            $this->buzzConfigService = new BuzzConfigService();
        }
        return $this->buzzConfigService;
    }

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
     * 
     * @return AddTaskForm
     */
    private function getCommentForm() {
        if (!$this->commentForm) {
            $this->setCommentForm(new CommentForm());
        }
        return $this->commentForm;
    }

}
