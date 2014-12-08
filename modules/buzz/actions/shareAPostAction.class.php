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
 * Description of likeOnShareAction
 *
 * @author aruna
 */
class shareAPostAction extends BuzzBaseAction {

    /**
     * this is function to get buzzService
     * @return BuzzService 
     */
    public function getBuzzService() {
        if (!$this->buzzService) {
            $this->buzzService = new BuzzService();
        }
        return $this->buzzService;
    }

    

    public function execute($request) {
       try{
            $this->loggedInUser=  $this->getUserId();
             
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
        $this->postId = $request->getParameter('postId');
        $this->error = 'no';
        
        try {
            $this->post = $this->getBuzzService()->getPostById($this->postId);
            $this->shareText = $request->getParameter('textShare');
            $this->share = $this->sharePost();
            $this->logeInUser = $this->getUserId();
            $this->commentForm = $this->getCommentForm();
            $this->editForm = new CommentForm();
        } catch (Exception $ex) {
            $this->error = 'yes';
            $this->getUser()->setFlash('error', __("This post has been deleted or you do not have permission to perform this action"));
        }
    }

    /**
     * save share in database
     * @return Share
     */
    public function sharePost() {
        $share = $this->setShare($this->postId);
        return $this->getBuzzService()->saveShare($share);
    }

    /**
     * set Share details
     * @param int $postId
     * @return \Share
     */
    public function setShare($postId) {
        $share = new Share();
        $share->setPostId($postId);
        $share->setEmployeeNumber($this->getUserId());
        $share->setNumberOfComments(0);
        $share->setNumberOfLikes(0);
        $share->setText($this->shareText);
        $share->setShareTime(date("Y-m-d H:i:s"));
        $share->setType('1');
        return $share;
    }

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

}