<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of viewProfileAction
 *
 * @author dewmal
 */
class viewProfileAction extends BaseBuzzAction {

    
   

    public function execute($request) {
        $template = $this->getContext()->getConfiguration()->getTemplateDir('buzz', 'chatter.php');
        $this->setLayout($template . '/chatter');
        $this->searchForm = new BuzzEmployeeSearchForm();
        try {
            $this->loggedInUser = $this->getUserId();
            $this->profileUserId = $request->getParameter('empNumber');
            $this->employee = $this->getBuzzService()->getEmployee($this->profileUserId);

            $this->employeeList = $this->getBuzzService()->getEmployeeList();
            $this->intializeConstant();


            $this->setBuzzService(new BuzzService());
            $this->initializePostList();
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
        $userId = $this->profileUserId;

        $this->postList = $buzzService->getSharesByUserId($this->shareCount, $userId);
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
