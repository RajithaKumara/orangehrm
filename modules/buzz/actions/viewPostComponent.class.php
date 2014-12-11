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
    

    public function execute($request) {
        
        $this->setBuzzService(new BuzzService());
        $this->loggedInUser = $this->getUserId();
        $this->setShare($this->post);
        $this->postForm = $this->getPostForm();
        $this->commentForm = $this->getCommentForm();
        $this->editForm = new CommentForm();
        //$this->uploadImageForm = new UploadPhotoForm(); //image upload form
       
        $this->intializeConstant();
        
       
        
        //$this->videoForm= new CreateVideoForm();  // video form added
        
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
        $this->postShareCount= $post->calShareCount();
        $this->postType = $post->getType();
        $this->employeeID = $post->getEmployeeNumber();
        $this->commentList = $post->getComment();
        $this->postEmployeeName = $post->getEmployeeFirstLastName();
        $this->isLike = $post->isLike($this->loggedInUser);
        if($post->isUnLike($this->loggedInUser)=='yes'){
        $this->isUnlike= 'yes';
        }
        $this->originalPost = $post->getPostShared();
        $this->originalPostId = $this->originalPost->getId();
        $this->originalPostEmpNumber = $this->originalPost->getEmployeeNumber();
        $this->originalPostSharerName = $this->originalPost->getEmployeeFirstLastName();
        $this->originalPostDate = $this->originalPost->getDate();
        $this->originalPostTime = $this->originalPost->getTime();
        $this->originalPostContent = $this->originalPost->getText();
        $this->likeEmployeList = $post->getLikedEmployeeList();
    }
     protected function intializeConstant() {
        $buzzConfigService = $this->getBuzzConfigService();
        $this->shareCount = $buzzConfigService->getBuzzShareCount();
        $this->initialcommentCount = $buzzConfigService->getBuzzInitialCommentCount();
        $this->viewMoreComment = $buzzConfigService->getBuzzViewCommentCount();
        $this->likeCount = $buzzConfigService->getBuzzLikeCount();
        $this->refeshTime = $buzzConfigService->getRefreshTime();
        $this->postLenth = $buzzConfigService->getBuzzPostTextLenth();
        $this->postLines = $buzzConfigService->getBuzzPostTextLines();
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
            $this->buzzConfigService= new BuzzConfigService();
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

    /**
     * Returns the user who is currently logged in.
     * @return User 
     */
    private function getLoggedInUser() {
        return $this->getUser();
    }
    
    public function getUserId(){
        
        $cookie_name='buzzCookie';
        
        if(UserRoleManagerFactory::getUserRoleManager()->getUser()!=null){
           
            
            $cookie_valuve = $this->getUser()->getEmployeeNumber();
            if($cookie_valuve==""){
                 setcookie($cookie_name, 'Admin', time() + 3600 * 24 * 30, "/");
            }else{
                setcookie($cookie_name, $cookie_valuve, time() + 3600 * 24 * 30, "/"); 
            }
            
           
              
            return  $cookie_valuve;
        }elseif (isset($_COOKIE[$cookie_name]) ){
            if($_COOKIE[$cookie_name]=='Admin'){
                return '';
            }
               
            return $_COOKIE[$cookie_name];
        }else{
            throw new Exception('User Didnot Have');
        }
    }

//put your code here
}
