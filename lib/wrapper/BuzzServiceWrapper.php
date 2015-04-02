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
    public function getLoggedInEmployeeNumber($options) {
        return sfContext::getInstance()->getUser()->getAttribute("auth.empNumber");
    }
    
    /**
     * Get Current Logged in Employee
     * @param type $options
     */
    public function getLoggedInEmployee($options) {
        return $this->getServiceInstance()->getLoggedInEmployee();
    }

    /**
     * Get latest shares from a given share Id
     * 
     * @param type $options
     * @return type
     */
    public function getLatestBuzzShares($options) {
        return $this->getServiceInstance()->getLatestBuzzShares($options['recentShareId']);
    }
    
     /**
     * Get recent [at first load] shares default number of shares are 10
     * 
     * @param type $options
     * @return type
     */
    public function getBuzzShares($options) {
        return $this->getServiceInstance()->getBuzzShares($options['limit']);
    }
    
    
     /**
     * Get shares older than a given share Id
     * 
     * @param type $options
     * @return type
     */
    public function getMoreBuzzShares($options) {
        return $this->getServiceInstance()->getMoreBuzzShares($options['lastShareId'],$options['limit']);
    }
    

    /**
     * Get share and post details by share id, this will retun post details, comment and like details, etc
     * @param type $options
     */
    public function getShareAndPostDetailsByShareId($options) {
        if (is_null($options['shareId'])) {
            throw new Exception("Valid parameters are not provided");
        } else {
            return $this->getServiceInstance()->getShareAndPostDetailsByShareId($options['shareId']);
        }
    }

    /**
     * Post content on news feed
     * @param type $options
     */
    public function postContentOnFeed($options, $extraPostOptions) {
        if (is_null($options['contentText'])) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber($options);
            return $this->getServiceInstance()->postContentOnFeed($empNumber, $options['contentText'], date("Y-m-d H:i:s"), $extraPostOptions);
        }
    }
    
     /**
     * Post content on news feed
     * @param type $options
     */
    public function commentOnShare($options) {
        if (is_null($options['shareId'] && $options['contentText'])) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber($options);
            return $this->getServiceInstance()->commentOnShare($options['shareId'], $empNumber, $options['contentText'], date("Y-m-d H:i:s"));
        }
    }
    
    /**
     * Like on a share / post
     * @param type $options
     */
    public function likeOnShare($options){
        if (is_null($options['shareId'])) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber($options);
            return $this->getServiceInstance()->likeOnShare($options['shareId'], $empNumber, date("Y-m-d H:i:s"));
        }
    }
    
    /**
     * Dislike on a share / post
     * @param type $options
     */
    public function disLikeOnShare($options){
        if (is_null($options['shareId'])) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber($options);
            return $this->getServiceInstance()->dislikeOnShare($options['shareId'],$empNumber,  date("Y-m-d H:i:s"));
        }
    }
    
    /**
     * Like on a comment
     * @param type $options
     */
    public function likeOnComment($options){
        if (is_null($options['commentId'])) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber($options);
            return $this->getServiceInstance()->likeOnComment($options['commentId'], $empNumber, date("Y-m-d H:i:s"));
        }
    }
    
    /**
     * Dislike on a comment
     * @param type $options
     */
    public function dislikeOnComment($options){
        if (is_null($options['commentId'])) {
            throw new Exception("Valid parameters are not provided");
        } else {
            $empNumber = $this->getLoggedInEmployeeNumber($options);
            return $this->getServiceInstance()->dislikeOnComment($options['commentId'], $empNumber, date("Y-m-d H:i:s"));
        }
    }

}