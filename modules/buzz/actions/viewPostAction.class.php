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

class viewPostAction extends BaseBuzzAction{
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
        $this->postId=$request->getParameter('postId');
        $post=  $this->getPost();
        $this->userId=  $this->getUserId();
        $this->commentForm = $this->getCommentForm();
        $this->post=$post;
        $shares=$post->getShare();
        foreach ($shares as $share ){
            if($share->getTypeName()=='post'){
                $this->share=$share;
                
            }
            
        }
        $this->setShare($this->share);
        $this->editForm=new CommentForm();
        $this->commentForm=new CommentForm();
    }

    public function getPost(){
        return $this->getBuzzService()->getPostById($this->postId);
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
    
    private function setShare($post) {
        $this->postId = $post->getId();
        $this->postEmployee = $post->getEmployeePostShared();
        $this->postEmployeeJobTitle = $this->postEmployee->getJobTitleName();
        $this->postDate = $post->getDate();
        $this->postTime = $post->getTime();
        $this->postContent = $post->getText();
        $this->postNoOfLikes = $post->getLike()->count();
        $this->postUnlike= $post->getNumberOfUnlikes();
        $this->shareCount= $post->calShareCount();
        $this->postType = $post->getType();
        $this->employeeID = $post->getEmployeeNumber();
        $this->commentList = $post->getComment();
        $this->postEmployeeName = $post->getEmployeeFirstLastName();
        $this->isLike = $post->isLike($this->loggedInUser);
       
        $this->isUnlike= $post->isUnLike($this->loggedInUser);
        
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
     * @param AddTaskForm $form
     */
    private function setCommentForm($form) {
        $this->commentForm = $form;
    }
    
    

}
