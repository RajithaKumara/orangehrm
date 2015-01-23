<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of viewPostComponent
 *
 * @author dewmal
 */
class viewPostComponent extends sfComponent {

    protected $buzzService;
    protected $buzzConfigService;
    protected $buzzCookieService;
    protected $ohrmCookieManager;

    /**
     * 
     * @return BuzzCookieService
     */
    protected function getBuzzCookieService() {
        if (!$this->buzzCookieService instanceof BuzzCookieService) {
            $this->buzzCookieService = new BuzzCookieService();
        }
        return $this->buzzCookieService;
    }
    
    /**
     * 
     * @return CookieManager
     */
    protected function getOhrmCookieManager() {
        if (!$this->ohrmCookieManager instanceof CookieManager) {
            $this->ohrmCookieManager = new CookieManager();
        }
        return $this->ohrmCookieManager;
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

        $this->setBuzzService(new BuzzService());
        $this->setShare($this->post);
        //$this->postForm = $this->getPostForm();
        $this->commentForm = $this->getCommentForm();
        $this->editForm = $this->getEditForm();
        $this->intializeConfigValuves();
    }

    /**
     * set the share parameters to view
     * @param type $post
     */
    private function setShare($post) {
        $this->postId = $post->getId();
        $this->postEmployee = $post->getEmployeePostShared();
        $this->postEmployeeJobTitle = $this->postEmployee->getJobTitleName();
        $this->postDate = $post->getDate();
        $this->postTime = $post->getTime();
        $this->postContent = $post->getText();
        $this->postNoOfLikes = $post->getNumberOfLikes();
        $this->postUnlike = $post->getNumberOfUnlikes();
        $this->postShareCount = $post->calShareCount();
        $this->postType = $post->getType();
        $this->employeeID = $post->getEmployeeNumber();
        $this->commentList = $post->getComment();
        $this->postEmployeeName = $post->getEmployeeFirstLastName();
        $this->isLike = $post->isLike($this->loggedInUser);
        $this->isUnlike = $post->isUnLike($this->loggedInUser);
        $this->originalPost = $post->getPostShared();
        $this->originalPostId = $this->originalPost->getId();
        $this->originalPostEmpNumber = $this->originalPost->getEmployeeNumber();
        $this->originalPostSharerName = $this->originalPost->getEmployeeFirstLastName();
        $this->originalPostDate = $this->originalPost->getDate();
        $this->originalPostTime = $this->originalPost->getTime();
        $this->originalPostContent = $this->originalPost->getText();
//        $this->likeEmployeList = $post->getLikedEmployeeList();
        $this->loggedInEmployeeUserRole = $this->getLoggedInEmployeeUserRole();
//        echo $this->loggedInEmployeeUserRole;die;
    }

    /**
     * initialize config valuves
     */
    protected function intializeConfigValuves() {
        $buzzConfigService = $this->getBuzzConfigService();
        //$this->shareCount = $buzzConfigService->getBuzzShareCount();
        $this->initialcommentCount = $buzzConfigService->getBuzzInitialCommentCount();
        $this->viewMoreComment = $buzzConfigService->getBuzzViewCommentCount();
        $this->likeCount = $buzzConfigService->getBuzzLikeCount();
        //$this->refeshTime = $buzzConfigService->getRefreshTime();
        $this->postLenth = $buzzConfigService->getBuzzPostTextLenth();
        $this->postLines = $buzzConfigService->getBuzzPostTextLines();
    }

    /**
     * get loged in employee user role
     * @return type
     */
    public function getLoggedInEmployeeUserRole() {
        $employeeUserRole = null;
        if ($this->getOhrmCookieManager()->isCookieSet("Loggedin")) {
            if ($this->getUser()->getAttribute('auth.isAdmin') == 'Yes') {
                $employeeUserRole = 'Admin';
            } else {
                $employeeUserRole = 'Ess';
            }
            $employeeNumber = $this->getUser()->getAttribute('auth.empNumber');
            if ($this->getBuzzCookieService()->getEmployeeNumber() != $employeeNumber) {
                $this->getBuzzCookieService()->saveCookieValuves($employeeNumber, $employeeUserRole);
            }
        } else {
            $employeeUserRole = $this->getBuzzCookieService()->getEmployeeUserRole();
        }

        return $employeeUserRole;
    }

}
