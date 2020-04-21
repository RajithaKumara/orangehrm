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
 * Description of mostLikedSharesComponent
 *
 * @author aruna
 */
class mostLikedSharesComponent extends sfComponent {

    protected $buzzService;
    /**
     * return buzz service
     * @return buzzService
     */
    protected function getBuzzService() {
        if (!($this->buzzService instanceof BuzzService)) {
            $this->buzzService = new BuzzService();
        }
        return $this->buzzService;
    }
     
    /**
     * 
     * @return BuzzConfigService
     */
    private function getBuzzConfigService() {
        if (!$this->buzzConfigService) {
            $this->buzzConfigService = new BuzzConfigService();
        }
        return $this->buzzConfigService;
    }
    
    public function execute($request) {
        $this->buzzService= $this->getBuzzService();
        $mostLikeShareCount= $this->getBuzzConfigService()->getMostLikeShareCount();
        $mostLikePostCount= $this->getBuzzConfigService()->getMostLikePostCount();
        $mostLikedShares = $this->buzzService->getMostLikedShares($mostLikeShareCount);
        $mostCommentedShares = $this->buzzService->getMostCommentedShares($mostLikePostCount);

        $this->result_ml_shares = array();
        $this->result_ml_shares_like_count = array();
        $this->result_mc_shares = array();
        $this->result_mc_shares_comment_count = array();

        $this->setMostLikeShares($mostLikedShares);
        $this->setMostCommentedShares($mostCommentedShares);
    }
    
    /**
     * set most like shares for view
     * @param type $mostLikedShares
     */
    private function setMostLikeShares($mostLikedShares) {
        foreach ($mostLikedShares as $share) {
            $s = $this->buzzService->getShareById($share['share_id']);
            $n = $share['no_of_likes'];
            array_push($this->result_ml_shares, $s);
            array_push($this->result_ml_shares_like_count, $n);
        }
    }
    
    /**
     * set most commented post for view
     * @param type $mostCommentedShares
     */
    private function setMostCommentedShares($mostCommentedShares) {
        foreach ($mostCommentedShares as $share) {
            $s = $this->buzzService->getShareById($share['share_id']);
            $n = $share['no_of_comments'];
            array_push($this->result_mc_shares, $s);
            array_push($this->result_mc_shares_comment_count, $n);
        }
    }
   

}
