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
     * Get Latest shares of Buzz
     * 
     * @param type $limit
     * @return type
     */
    public function getLatestBuzzShares($limit = null) {
        if (!$limit) {
            $limit = self::DEFAULT_SHARE_LIMIT;
        }

        $latestShares = $this->getBuzzService()->getShares($limit);
        return $this->getBuzzObjectBuilder()->getShareCollectionArray($latestShares);
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
        return $share;
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
        return $this->getBuzzService()->saveCommentShare($comment);
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

        if ($share->isUnLikeUser($loggedInEmployeeNumber) == 'yes') {
            $this->getBuzzService()->deleteUnLikeForShare($dislikeOnShare);
        }

        if ($share->isLike($loggedInEmployeeNumber) == 'Like') {
            $this->getBuzzService()->saveLikeForShare($likeOnShare);
        }
        $shareSaved = $this->getBuzzService()->getShareById($shareId);
        return $shareSaved;
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

        if ($share->isLike($loggedInEmployeeNumber) == 'Unlike') {
            $this->getBuzzService()->deleteLikeForShare($likeOnShare);
        }

        if ($share->isUnLike($loggedInEmployeeNumber) == 'no') {
            $this->getBuzzService()->saveUnLikeForShare($dislikeOnShare);
        }

        $shareSaved = $this->getBuzzService()->getShareById($shareId);
        return $shareSaved;
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

        if ($comment->isUnLike($loggedInEmployeeNumber) == 'yes') {
            $this->getBuzzService()->deleteUnLikeForComment($dislikeOnComment);
        }

        if ($comment->isLike($loggedInEmployeeNumber) == 'Like') {
            $this->getBuzzService()->saveLikeForComment($likeOnComment);
        }

        $commentSaved = $this->getBuzzService()->getCommentById($commentId);
        return $commentSaved;
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

        if ($comment->isLike($loggedInEmployeeNumber) == 'Unlike') {
            $this->getBuzzService()->deleteLikeForComment($likeOnComment);
        }

        if ($comment->isUnLike($loggedInEmployeeNumber) == 'no') {
            $this->getBuzzService()->saveUnLikeForComment($dislikeOnComment);
        }

        $commentSaved = $this->getBuzzService()->getCommentById($commentId);

        return $commentSaved;
    }

}
