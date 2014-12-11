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

class BuzzConfigService extends ConfigService{
    
    const KEY_NEWSFEED_SHARE_COUNT = "buzz_share_count";
    const KEY_NEWSFEED_INITIAL_COMMENT_COUNT = "buzz_initial_comments";
    const KEY_NEWSFEED_VIEWMORE_COMMENT = 'buzz_viewmore_comment';
    const KEY_NEWSFEED_LIKE_COUNT = 'buzz_like_count';
    const KEY_REFRESH_TIME='buzz_refresh_time';
    const KEY_TIME_FORMAT='buzz_time_format';
    const KEY_MOST_LIKE_POSTS='buzz_most_like_posts';
    const KEY_MOST_LIKE_SHARES='buzz_most_like_shares';
    const KEY_POST_LENTH='buzz_post_text_lenth';
    const KEY_POST_TEXT_HEIGHT='buzz_post_text_lines';

    public function getBuzzKeeyValuve($key){
        return $this->_getConfigValue($key);
    }
    
    public function getBuzzShareCount(){
        return $this->getBuzzKeeyValuve(BuzzConfigService::KEY_NEWSFEED_SHARE_COUNT);
    }
    
    public function getBuzzPostTextLenth(){
        return $this->getBuzzKeeyValuve(BuzzConfigService::KEY_POST_LENTH);
    }
    
    public function getBuzzInitialCommentCount(){
        return $this->getBuzzKeeyValuve(BuzzConfigService::KEY_NEWSFEED_INITIAL_COMMENT_COUNT);
    }
    
    public function getBuzzViewCommentCount(){
        return $this->getBuzzKeeyValuve(BuzzConfigService::KEY_NEWSFEED_VIEWMORE_COMMENT);
    }
    
    public function getBuzzLikeCount(){
        return $this->getBuzzKeeyValuve(BuzzConfigService::KEY_NEWSFEED_LIKE_COUNT);
    }
    
    public function getRefreshTime(){
        return $this->getBuzzKeeyValuve(BuzzConfigService::KEY_REFRESH_TIME);
    }
    
    public function getTimeFormat(){
        return $this->getBuzzKeeyValuve(BuzzConfigService:: KEY_TIME_FORMAT);
    }
    
    public function getMostLikePostCount(){
        return $this->getBuzzKeeyValuve(BuzzConfigService:: KEY_MOST_LIKE_POSTS);
    }
    
     public function getMostLikeShareCount(){
        return $this->getBuzzKeeyValuve(BuzzConfigService:: KEY_MOST_LIKE_SHARES);
    }
    
}
