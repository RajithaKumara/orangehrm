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
 * Description of viewStatisticsAction
 *
 * @author aruna
 */
class viewStatisticsAction extends BuzzBaseAction {

    protected $buzzService;

    /**
     * 
     * @param BuzzService $buzzService
     */
    protected function setBuzzService($buzzService) {
        $this->buzzService = $buzzService;
    }

    public function execute($request) {
        $this->setBuzzService(new BuzzService());
        $this->loggedInUserId = $this->getUserId();
        $this->noOfShares = $this->getNoOfSharesBy($this->loggedInUserId);
        $this->noOfComments = $this->getNoOfCommentsBy($this->loggedInUserId);
        $this->noOfShareLikesRecieved = $this->getNoOfShareLikesFor($this->loggedInUserId);
        $this->noOfCommentLikesRecieved = $this->getNoOfCommentLikesFor($this->loggedInUserId);
        $this->noOfCommentsRecieved = $this->getNoOfCommentsFor($this->loggedInUserId);
    }

    private function getNoOfSharesBy($userId) {
        return $this->buzzService->getNoOfSharesBy($userId);
    }

    private function getNoOfCommentsBy($userId) {
        return $this->buzzService->getNoOfCommentsBy($userId);
    }

    private function getNoOfShareLikesFor($userId) {
        return $this->buzzService->getNoOfShareLikesFor($userId);
    }

    private function getNoOfCommentLikesFor($userId) {
        return $this->buzzService->getNoOfCommentLikesFor($userId);
    }

    private function getNoOfCommentsFor($userId) {
        return $this->buzzService->getNoOfCommentsFor($userId);
    }

}
