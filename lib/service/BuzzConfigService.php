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

class BuzzConfigService extends ConfigService {

    protected $buzzConfigValues = "";

    const KEY_NEWSFEED_SHARE_COUNT = "buzz_share_count";
    const KEY_NEWSFEED_INITIAL_COMMENT_COUNT = "buzz_initial_comments";
    const KEY_NEWSFEED_VIEWMORE_COMMENT = 'buzz_viewmore_comment';
    const KEY_NEWSFEED_LIKE_COUNT = 'buzz_like_count';
    const KEY_REFRESH_TIME = 'buzz_refresh_time';
    const KEY_TIME_FORMAT = 'buzz_time_format';
    const KEY_MOST_LIKE_POSTS = 'buzz_most_like_posts';
    const KEY_MOST_LIKE_SHARES = 'buzz_most_like_shares';
    const KEY_POST_LENTH = 'buzz_post_text_lenth';
    const KEY_POST_TEXT_HEIGHT = 'buzz_post_text_lines';
    const KEY_BUZZ_POST_SHARE_COUNT = 'buzz_post_share_count';
    const KEY_BUZZ_COOKIE_VALID_TIME = 'buzz_cookie_valid_time';
    const KEY_BUZZ_IMAGE_MAX_DIMENSION = 'buzz_image_max_dimension';
    const DEFAULT_BUZZ_IMAGE_MAX_DIMENSION = '1024';
    

    public function __construct() {
        parent::__construct();
        $this->setAllBuzzValues();
    }

    /**
     * Get all the config values related to buzz
     */
    public function setAllBuzzValues() {
        $configValues = $this->getAllValues();
        foreach ($configValues as $key => $value) {
            $arr = explode("_", $key);
            if ($arr[0] == "buzz") {
                $this->buzzConfigValues[$key] = $value;
            }
        }
        return TRUE;
    }

    /**
     * get config valuve for buzz keey
     * @param type $key
     * @return key
     */
    public function getBuzzKeeyValuve($key) {
        return $this->_getConfigValue($key);
    }

    /**
     * get Initial share count 
     * @return Int
     */
    public function getBuzzShareCount() {
        return $this->buzzConfigValues[BuzzConfigService::KEY_NEWSFEED_SHARE_COUNT];
    }

    /**
     * get post lenth to show
     * @return int
     */
    public function getBuzzPostTextLenth() {
        return $this->buzzConfigValues[BuzzConfigService::KEY_POST_LENTH];
    }

    /**
     * get post line count
     * @return int
     */
    public function getBuzzPostTextLines() {
        return $this->buzzConfigValues[BuzzConfigService::KEY_POST_TEXT_HEIGHT];
    }

    /**
     * get buzz initial comment shown count
     * @return type
     */
    public function getBuzzInitialCommentCount() {
        return $this->buzzConfigValues[BuzzConfigService::KEY_NEWSFEED_INITIAL_COMMENT_COUNT];
    }

    public function getBuzzViewCommentCount() {
        return $this->buzzConfigValues[BuzzConfigService::KEY_NEWSFEED_VIEWMORE_COMMENT];
    }

    /**
     * get buzz initial shown like count
     * @return int
     */
    public function getBuzzLikeCount() {
        return $this->buzzConfigValues[BuzzConfigService::KEY_NEWSFEED_LIKE_COUNT];
    }

    /**
     * get buzz initial shown share count
     * @return int
     */
    public function getPostShareCount() {
        return $this->buzzConfigValues[BuzzConfigService::KEY_BUZZ_POST_SHARE_COUNT];
    }

    /**
     * get buzz refresh time
     * @return int
     */
    public function getRefreshTime() {
        return $this->buzzConfigValues[BuzzConfigService::KEY_REFRESH_TIME];
    }

    /**
     * get buzz time format
     * @return string
     */
    public function getTimeFormat() {
        return $this->buzzConfigValues[BuzzConfigService:: KEY_TIME_FORMAT];
    }

    /**
     * get buzz most like post shown count
     * @return int
     */
    public function getMostLikePostCount() {
        return $this->buzzConfigValues[BuzzConfigService:: KEY_MOST_LIKE_POSTS];
    }

    /**
     * get buzz most like share shown count
     * @return int 
     */
    public function getMostLikeShareCount() {
        return $this->buzzConfigValues[BuzzConfigService:: KEY_MOST_LIKE_SHARES];
    }

    /**
     * get buzz cookie valid time
     * @return int 
     */
    public function getCookieValidTime() {
        return $this->buzzConfigValues[BuzzConfigService:: KEY_BUZZ_COOKIE_VALID_TIME];
    }

    /**
     * get maximum image width
     * @return int 
     */
    public function getMaxImageDimension() {
        if (isset($this->buzzConfigValues[self:: KEY_BUZZ_IMAGE_MAX_DIMENSION])) {
            return $this->buzzConfigValues[self:: KEY_BUZZ_IMAGE_MAX_DIMENSION];
        } else {
            return self::DEFAULT_BUZZ_IMAGE_MAX_DIMENSION;
        }
    }

}
