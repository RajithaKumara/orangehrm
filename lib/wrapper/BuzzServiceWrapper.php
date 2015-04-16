<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuzzServiceWrapper
 *
 * @author nirmal
 */
class BuzzServiceWrapper implements WebServiceWrapper {

    protected $buzzWebServiceHelper;

    const DEFAULT_SHARE_LIMIT = 10;

    public function getServiceInstance() {
        if (!$this->buzzWebServiceHelper instanceof BuzzWebServiceHelper) {
            $this->buzzWebServiceHelper = new BuzzWebServiceHelper();
        }
        return $this->buzzWebServiceHelper;
    }

    /**
     * Get Current Logged in Employee Number
     * @param type $options
     */
    public function getLoggedInEmployeeNumber() {
        return sfContext::getInstance()->getUser()->getAttribute("auth.empNumber");
    }
    
    /**
     * Get Current Logged in Employee
     * @param type $options
     */
    public function getLoggedInEmployee() {
        return $this->getServiceInstance()->getLoggedInEmployee();
    }

    /**
     * Get latest shares from a given share Id
     * 
     * @param integer $recentShareId
     * @return Doctrine_Collection
     */
    public function getLatestBuzzShares($recentShareId) {
        return $this->getServiceInstance()->getLatestBuzzShares($recentShareId);
    }
    
     /**
     * Get recent [at first load] shares default number of shares are 10
     * 
     * @param Integer $limit
     * @return type
     */
    public function getBuzzShares($limit) {
        return $this->getServiceInstance()->getBuzzShares($limit);
    }
    
    
     /**
     * Get shares older than a given share Id
     * 
     * @param integer $lastShareId
     * @param integer $limit
     * @return type
     */
    public function getMoreBuzzShares($lastShareId, $limit) {
        return $this->getServiceInstance()->getMoreBuzzShares($lastShareId, $limit);
    }
    

    /**
     * Get share and post details by share id, this will retun post details, comment and like details, etc
     * @param integer $shareId
     */
    public function getShareAndPostDetailsByShareId($shareId) {
        if (is_null($shareId)) {
            throw new Exception("Valid parameters are not provided");
        } else {
            return $this->getServiceInstance()->getShareAndPostDetailsByShareId($shareId);
        }
    }

    /**
     * Post content on news feed
     * @param type $options
     */
    public function postContentOnFeed($contentText, $image_data) {
        if (is_null($contentText)) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber();
            return $this->getServiceInstance()->postContentOnFeed($empNumber, $contentText, date("Y-m-d H:i:s"), $image_data);
        }
    }
    
     /**
     * Post content on news feed
     * @param Integer $shareId
     * @param String $contentText
     */
    public function commentOnShare($shareId, $contentText) {
        if (is_null($shareId && $contentText)) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber();
            return $this->getServiceInstance()->commentOnShare($shareId, $empNumber, $contentText, date("Y-m-d H:i:s"));
        }
    }
    
    /**
     * Like on a share / post
     * @param Integer $shareId
     */
    public function likeOnShare($shareId){
        if (is_null($shareId)) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber();
            return $this->getServiceInstance()->likeOnShare($shareId, $empNumber, date("Y-m-d H:i:s"));
        }
    }
    
    /**
     * Dislike on a share / post
     * @param Integer $shareId
     */
    public function disLikeOnShare($shareId){
        if (is_null($shareId)) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber();
            return $this->getServiceInstance()->dislikeOnShare($shareId,$empNumber,  date("Y-m-d H:i:s"));
        }
    }
    
    /**
     * Like on a comment
     * @param Integer $commentId
     */
    public function likeOnComment($commentId){
        if (is_null($commentId)) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber();
            return $this->getServiceInstance()->likeOnComment($commentId, $empNumber, date("Y-m-d H:i:s"));
        }
    }
    
    /**
     * Dislike on a comment
     * @param Integer $commentId
     */
    public function dislikeOnComment($commentId){
        if (is_null($commentId)) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber();
            return $this->getServiceInstance()->dislikeOnComment($commentId, $empNumber, date("Y-m-d H:i:s"));
        }
    }

}