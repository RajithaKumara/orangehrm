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
 * Description of viewPostComponent
 *
 * @author dewmal
 */
class viewPostComponent extends sfComponent {

    protected $buzzService;
    protected $buzzConfigService;
    protected $buzzUserService;
    protected $ohrmCookieManager;

    /**
     * 
     * @return BuzzUserService
     */
    protected function getBuzzUserService() {
        if (!$this->buzzUserService instanceof BuzzUserService) {
            $this->buzzUserService = new BuzzUserService();
        }
        return $this->buzzUserService;
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
        $this->postType = $post->getType();
        $this->employeeID = $post->getEmployeeNumber();
        $this->commentList = $post->getComment();
        $this->postEmployeeName = $post->getEmployeeFirstLastName($this->postEmployee);
        if ($this->postEmployeeName == ' ' && $post->getEmployeeName() != null) {
            $this->postEmployeeName = $post->getEmployeeName() . ' (' . __(BaseBuzzAction::LABEL_EMPLOYEE_DELETED) . ')';
            $this->postSharerDeleted = true;
        }
        $this->isLike = $post->isLike($this->loggedInUser);
        $this->isUnlike = $post->isUnLike($this->loggedInUser);
        $this->originalPost = $post->getPostShared();
        $this->postPhotos = $this->getBuzzService()->getPostPhotos($this->originalPost->getId());
        $this->postShareCount = $post->calShareCount($this->originalPost);
        $this->originalPostId = $this->originalPost->getId();
        $this->originalPostEmpNumber = $this->originalPost->getEmployeeNumber();
        $this->originalPostSharerName = $this->originalPost->getEmployeeFirstLastName();
        if ($this->originalPostSharerName == ' ' && $this->originalPost->getEmployeeName() != null) {
            $this->originalPostSharerName = $this->originalPost->getEmployeeName() . ' (' . __(BaseBuzzAction::LABEL_EMPLOYEE_DELETED) . ')';
            $this->originalPostSharerDeleted = true;
        }
        $this->originalPostDate = $this->originalPost->getDate();
        $this->originalPostTime = $this->originalPost->getTime();
        $this->originalPostContent = $this->originalPost->getText();
        $this->likeEmployeList = $post->getLikedEmployeeList();
        $this->loggedInEmployeeUserRole = $this->getLoggedInEmployeeUserRole();
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
        $this->commentLength = $buzzConfigService->getBuzzCommentTextLenth();

//        $this->initialcommentCount = $this->getUser()->getAttribute("initial_comment_count");
//        $this->viewMoreComment = $this->getUser()->getAttribute("view_more_comment");
//        $this->likeCount = $this->getUser()->getAttribute("like_count");
//        $this->postLenth = $this->getUser()->getAttribute("post_length");
//        $this->postLines = $this->getUser()->getAttribute("post_lines");
//        echo $this->postLenth; die;
    }

    /**
     * get loged in employee user role
     * @return type
     */
    public function getLoggedInEmployeeUserRole() {
        $employeeUserRole = $this->getBuzzUserService()->getEmployeeUserRole();
        return $employeeUserRole;
    }

}
