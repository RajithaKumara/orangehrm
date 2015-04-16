<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuzzWebServiceHelperUtility
 *
 * @author nirmal
 * Modified: ridwan [17th March 2015]
 */
class BuzzWebServiceHelper {

    protected $buzzService;
    protected $buzzObjectBuilder;

    const DEFAULT_SHARE_LIMIT = 10;

    /**
     * Get Buzz Service
     * @return BuzzService
     */
    public function getBuzzService() {
        if (!$this->buzzService instanceof BuzzService) {
            $this->buzzService = new BuzzService();
        }
        return $this->buzzService;
    }

    /**
     * Set Buzz Service
     * @param BuzzService $buzzService
     */
    public function setBuzzService(BuzzService $buzzService) {
        $this->buzzService = $buzzService;
    }

    /**
     * Get BuzzObjectBuilder
     * @return BuzzObjectBuilder
     */
    public function getBuzzObjectBuilder() {
        if (!$this->buzzObjectBuilder instanceof BuzzObjectBuilder) {
            $this->buzzObjectBuilder = new BuzzObjectBuilder();
        }
        return $this->buzzObjectBuilder;
    }

    /**
     * Set BuzzObjectBuilder
     * @param BuzzObjectBuilder $buzzObjectBuilder
     */
    public function setBuzzObjectBuilder(BuzzObjectBuilder $buzzObjectBuilder) {
        $this->buzzObjectBuilder = $buzzObjectBuilder;
    }

    /**
     * Get Latest shares from a given Share Id
     * 
     * @param type $recentShareId
     * @return array
     */
    public function getLatestBuzzShares($recentShareId = null) {    
        $loggedInUserEmpNum =  sfContext::getInstance()->getUser()->getAttribute("auth.empNumber"); 
        $latestShares = $this->getBuzzService()->getSharesUptoId($recentShareId);
         $postPhotosArray = array();
        foreach ($latestShares as $share){
            $post = $share->getPostShared();
            $postPhotos = $this->getBuzzService()->getPostPhotos($post->getId());
            $postPhotosArray[$post->getId()] = $postPhotos;
        }
        return $this->getBuzzObjectBuilder()->getShareCollectionArray($latestShares,$postPhotosArray,$loggedInUserEmpNum);
    }
    
     /**
     * Get recent shares of Buzz
     * 
     * @param type $limit
     * @return array
     */
    public function getBuzzShares($limit = null) {
        $loggedInUserEmpNum =  sfContext::getInstance()->getUser()->getAttribute("auth.empNumber"); 
        if (!$limit) {
            $limit = self::DEFAULT_SHARE_LIMIT;
        }   
        $latestShares = $this->getBuzzService()->getShares($limit);
        $postPhotosArray = array();
        foreach ($latestShares as $share){
            $post = $share->getPostShared();
            $postPhotos = $this->getBuzzService()->getPostPhotos($post->getId());
            $postPhotosArray[$post->getId()] = $postPhotos;
        }
        return $this->getBuzzObjectBuilder()->getShareCollectionArray($latestShares,$postPhotosArray,$loggedInUserEmpNum);
    }
    
     /**
     * Get more shares of Buzz
     * 
     * @param type $limit
     * @return array
     */
    public function getMoreBuzzShares($lastShareId,$limit) {
        $loggedInUserEmpNum =  sfContext::getInstance()->getUser()->getAttribute("auth.empNumber"); 
        if (!$limit) {
            $limit = self::DEFAULT_SHARE_LIMIT;
        }
        $latestShares = $this->getBuzzService()->getMoreShares($limit,$lastShareId);
         $postPhotosArray = array();
        foreach ($latestShares as $share){
            $post = $share->getPostShared();
            $postPhotos = $this->getBuzzService()->getPostPhotos($post->getId());
            $postPhotosArray[$post->getId()] = $postPhotos;
        }
        return $this->getBuzzObjectBuilder()->getShareCollectionArray($latestShares,$postPhotosArray,$loggedInUserEmpNum);
    }

    /**
     * 
     * @todo Break this down to several functions and test seperately
     * @param type $shareId
     * @return type
     */
    public function getShareAndPostDetailsByShareId($shareId) {
        $share = $this->getBuzzService()->getShareById($shareId);
        $post = $share->getPostShared();
        $postPhotos = $this->getBuzzService()->getPostPhotos($post->getId());

        return $this->getBuzzObjectBuilder()->getShareDetailsAsArray($share, $post, $postPhotos);
    }

    /**
     * Post content on news feed
     * @param type $loggedInEmployeeNumber
     * @param type $content
     * @param type $postedDateTime
     * @param type $extraPostOptions
     * @return type
     */
    public function postContentOnFeed($loggedInEmployeeNumber, $content, $postedDateTime, $extraPostOptions = null) {
        $post = $this->getBuzzObjectBuilder()->createPost($loggedInEmployeeNumber, $content, $postedDateTime);
        $share = $this->getBuzzObjectBuilder()->createShare($post, $postedDateTime);
        $share = $this->getBuzzService()->saveShare($share);
        if ($extraPostOptions) {
            $postId = $share->getPostId();
            $imagesArray = $this->getBuzzObjectBuilder()->extractImagesForPost($extraPostOptions, $postId);
            foreach ($imagesArray as $image) {
                $this->getBuzzService()->savePhoto($image);
            }
        }
        return $share->toArray();
    }

    /**
     * 
     * @param type $shareId
     * @param type $loggedInEmployeeNumber
     * @param type $commentText
     * @param type $postedDateTime
     * @return type
     */
    public function commentOnShare($shareId, $loggedInEmployeeNumber, $commentText, $postedDateTime) {
        $comment = $this->getBuzzObjectBuilder()->createCommentOnShare($shareId, $loggedInEmployeeNumber, $commentText, $postedDateTime);
        $result = $this->getBuzzService()->saveCommentShare($comment);
        return $result->toArray();
    }

   /**
    * Like on share
    * @param type $shareId
    * @param type $loggedInEmployeeNumber
    * @param type $postedDateTime
    * @return type
    */
    public function likeOnShare($shareId, $loggedInEmployeeNumber, $postedDateTime) {
        $likeOnShare = $this->getBuzzObjectBuilder()->createLikeOnShare($shareId, $loggedInEmployeeNumber, $postedDateTime);
        $dislikeOnShare = $this->getBuzzObjectBuilder()->createDislikeOnShare($shareId, $loggedInEmployeeNumber, $postedDateTime);

        $share = $this->getBuzzService()->getShareById($shareId);

        if ($share->isShareUnLike($loggedInEmployeeNumber)) {
            $this->getBuzzService()->deleteUnLikeForShare($dislikeOnShare);
        }

        if (!$share->isShareLike($loggedInEmployeeNumber)) {
            $this->getBuzzService()->saveLikeForShare($likeOnShare);
        }
        $shareSaved = $this->getBuzzService()->getShareById($shareId);
        return $shareSaved->toArray();
    }

    /**
     * Dislike on share
     * @param type $shareId
     * @param type $loggedInEmployeeNumber
     * @param type $postedDateTime
     * @return type
     */
    public function dislikeOnShare($shareId, $loggedInEmployeeNumber, $postedDateTime) {
        $likeOnShare = $this->getBuzzObjectBuilder()->createLikeOnShare($shareId, $loggedInEmployeeNumber, $postedDateTime);
        $dislikeOnShare = $this->getBuzzObjectBuilder()->createDislikeOnShare($shareId, $loggedInEmployeeNumber, $postedDateTime);

        $share = $this->getBuzzService()->getShareById($shareId);

        if ($share->isShareLike($loggedInEmployeeNumber)) {
            $this->getBuzzService()->deleteLikeForShare($likeOnShare);
        }

        if (!$share->isShareUnLike($loggedInEmployeeNumber)) {
            $this->getBuzzService()->saveUnLikeForShare($dislikeOnShare);
        }

        $shareSaved = $this->getBuzzService()->getShareById($shareId);
        return $shareSaved->toArray();
    }

    /**
     * Like on comment
     * @param type $commentId
     * @param type $loggedInEmployeeNumber
     * @param type $postedDateTime
     * @return type
     */
    public function likeOnComment($commentId, $loggedInEmployeeNumber, $postedDateTime) {
        $likeOnComment = $this->getBuzzObjectBuilder()->createLikeOnComment($commentId, $loggedInEmployeeNumber, $postedDateTime);
        $dislikeOnComment = $this->getBuzzObjectBuilder()->createDislikeOnComment($commentId, $loggedInEmployeeNumber, $postedDateTime);

        $comment = $this->getBuzzService()->getCommentById($commentId);

        if ($comment->isCommentUnLike($loggedInEmployeeNumber)) {
            $this->getBuzzService()->deleteUnLikeForComment($dislikeOnComment);
        }

        if (!$comment->isCommentLike($loggedInEmployeeNumber)) {
            $this->getBuzzService()->saveLikeForComment($likeOnComment);
        }

        $commentSaved = $this->getBuzzService()->getCommentById($commentId);
        return $commentSaved->toArray();
    }

    /**
     * Dislike on comment
     * @param type $commentId
     * @param type $loggedInEmployeeNumber
     * @param type $postedDateTime
     * @return type
     */
    public function dislikeOnComment($commentId, $loggedInEmployeeNumber, $postedDateTime) {
        $likeOnComment = $this->getBuzzObjectBuilder()->createLikeOnComment($commentId, $loggedInEmployeeNumber, $postedDateTime);
        $dislikeOnComment = $this->getBuzzObjectBuilder()->createDislikeOnComment($commentId, $loggedInEmployeeNumber, $postedDateTime);

        $comment = $this->getBuzzService()->getCommentById($commentId);

        if ($comment->isCommentLike($loggedInEmployeeNumber)) {
            $this->getBuzzService()->deleteLikeForComment($likeOnComment);
        }

        if (!$comment->isCommentUnLike($loggedInEmployeeNumber)) {
            $this->getBuzzService()->saveUnLikeForComment($dislikeOnComment);
        }

        $commentSaved = $this->getBuzzService()->getCommentById($commentId);

        return $commentSaved->toArray();
    }
    
    /**
     * get logged in employee object
     * @return Doctrine
     */
    
    public function getLoggedInEmployee(){
        $loggedInUserEmpNum =  sfContext::getInstance()->getUser()->getAttribute("auth.empNumber");  
        $employeeService = new EmployeeService();
        $employeeService->setEmployeeDao(new EmployeeDao());        
        $loggedUser = $employeeService->getEmployee($loggedInUserEmpNum);
        $jobTitle = $loggedUser->getJobTitleName();
        $loggedUser->setCustom1($jobTitle);
        return $loggedUser->toArray();
    }
    
    

}
