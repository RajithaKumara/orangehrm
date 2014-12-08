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

    const SHARE_COUNT = 5;

    public function execute($request) {
        $this->setBuzzService(new BuzzService());
        $mostLikedShares = $this->buzzService->getMostLikedShares(mostLikedSharesComponent::SHARE_COUNT);
        $mostCommentedShares = $this->buzzService->getMostCommentedShares(mostLikedSharesComponent::SHARE_COUNT);

        $this->result_ml_shares = array();
        $this->result_ml_shares_like_count = array();
        $this->result_mc_shares = array();
        $this->result_mc_shares_comment_count = array();

        foreach ($mostLikedShares as $share) {
            $s = $this->buzzService->getShareById($share['share_id']);
            $n = $share['no_of_likes'];
            array_push($this->result_ml_shares, $s);
            array_push($this->result_ml_shares_like_count, $n);
        }
        foreach ($mostCommentedShares as $share) {
            $s = $this->buzzService->getShareById($share['share_id']);
            $n = $share['no_of_comments'];
            array_push($this->result_mc_shares, $s);
            array_push($this->result_mc_shares_comment_count, $n);
        }
    }

    protected function setBuzzService($nfService) {
        if (!$this->buzzService) {
            $this->buzzService = $nfService;
        }
    }

}
