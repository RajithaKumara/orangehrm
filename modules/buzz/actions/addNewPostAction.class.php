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
            $this->postForm=new CreatePostForm();
        }
        return $this->postForm;
    }
    
    /**
     * 
     * @return CommentForm
     */
    private function getEditForm() {
        if (!$this->editForm) {
            $this->editForm=new CommentForm();
        }
        return $this->editForm;
    }
    
    public function execute($request) {

        $token = $request->getPostParameter('_csrf_token');
        $content = $request->getPostParameter('content');
        $postLinkState = $request->getPostParameter('postLinkState');
        $postLinkAddress = $request->getPostParameter('postLinkAddress');
        $linkTitle = $request->getPostParameter('linkTitle');
        $linkText = $request->getPostParameter('linkText');

        $parameters = array('content' => $content, '_csrf_token' => $token);
        $this->form = $this->getPostForm();

        $this->isSuccess = 'not';
        if ($request->isMethod('post')) {
            $this->form->bind($parameters);
            if ($this->form->isValid() && $content != '') {
                $share = $this->form->save($this->getUserId(), $content);
                $this->post = $share;
                $this->setShare($share);
                $this->loggedInUser = $this->getUserId();
                $this->isSuccess = 'yes';
                if ($postLinkState == 'yes') {
                    $link = $this->setLink($share, $postLinkAddress, $linkTitle, $linkText);
                    $this->saveLink($link);
                }
            } else {        
            }
        }
        $this->commentForm = $this->getCommentForm();
        $this->editForm = $this->getEditForm();
    }

    /**
     * set parameters share to view
     * @param Post $post
     * @return share
     */
    public function setShare($share) {

        $this->postId = $share->getId();
        $this->originalPostId = $share->getPostId();
        $this->postEmployeeName = $share->getEmployeeFirstLastName();
        $this->employeeId = $share->getEmployeeNumber();
        $this->postDate = $share->getDate();
        $this->postTime = $share->getTime();
        $this->noOfLikes = $share->getNumberOfLikes();
        $this->isLike = $share->isLike($this->getUserId());
        $this->postContent = $share->getPostShared()->getText();
    }
    
    /**
     * 
     * @param type $share
     * @param type $postLinkAddress
     * @param type $linkTitle
     * @param type $linkText
     * @return \Link
     */
    private function setLink($share, $postLinkAddress, $linkTitle, $linkText) {
        $link = new Link();
        $link->setPostId($share->getPostId());
        $link->setLink($postLinkAddress);
        $link->setType(0);
        $link->setTitle($linkTitle);
        $link->setDescription($linkText);
        return $link;
    }

    /**
     * 
     * @param type $link
     * @return type
     */
    private function saveLink($link) {
        return $this->getBuzzService()->saveLink($link);
    }

}
