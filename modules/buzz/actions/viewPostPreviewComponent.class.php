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
 * Description of viewPostPreviewComponent
 *
 * @author aruna
 */
class viewPostPreviewComponent extends sfComponent {

    protected $buzzService;
    protected $buzzConfigService;
    const COOKIE_NAME = 'buzzCookie';
    
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

        $this->setShare($this->post);
        $this->commentForm = $this->getCommentForm();
        $this->editForm = $this->getEditForm();
        $this->setBuzzService(new BuzzService());

        $this->loggedInUser = $this->getLogedInEmployeeNumber();
    }

    /**
     * set post valuves to show
     * @param type $post
     */
    private function setShare($post) {
        $this->postId = $post->getId();
        $this->postDate = $post->getDate();
        $this->postTime = $post->getTime();
        $this->postContent = $post->getText();
        $this->postNoOfLikes = $post->getLike()->count();
        $this->postType = $post->getType();
        $this->employeeID = $post->getEmployeeNumber();
        $this->commentList = $post->getComment();
        $this->postEmployeeName = $post->getEmployeeFirstLastName();
        $this->isLike = $post->isLike($this->loggedInUser);
        $this->originalPost = $post->getPostShared();
        $this->originalPostId = $this->originalPost->getId();
        $this->originalPostEmpNumber = $this->originalPost->getEmployeeNumber();
        $this->originalPostSharerName = $this->originalPost->getEmployeeFirstLastName();
        $this->originalPostDate = $this->originalPost->getDate();
        $this->originalPostTime = $this->originalPost->getTime();
        $this->originalPostContent = $this->originalPost->getText();
        $this->likeEmployeList = $post->getLikedEmployeeList();
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

    public function getLogedInEmployeeNumber() {
        $employeeNumber = null;
        if (UserRoleManagerFactory::getUserRoleManager()->getUser() != null) {

            $cookie_valuve = $this->getUser()->getEmployeeNumber();
            if ($cookie_valuve == "") {
                //get it from the configuration
                setcookie(self::COOKIE_NAME, 'Admin', time() + 3600 * 24 * 30, "/");
                
            } else {
                setcookie(self::COOKIE_NAME, $cookie_valuve, time() + 3600 * 24 * 30, "/");
            }

            $employeeNumber = $cookie_valuve;
        } elseif (isset($_COOKIE[self::COOKIE_NAME])) {
            if ($_COOKIE[self::COOKIE_NAME] == 'Admin') {
                $employeeNumber = null;
                 
            }else{
                $employeeNumber = $_COOKIE[self::COOKIE_NAME];
            }
        } else {
            throw new Exception('User Didnot Have');
        }
        
        return $employeeNumber;
    }
    

}
