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
class viewStatisticsAction extends BaseBuzzAction {

    protected $buzzService;

    /**
     * return number of shares that user share
     * @param int $userId
     * @return Int
     */
    private function getNoOfSharesBy($userId) {
        return $this->buzzService->getNoOfSharesByEmployeeNumber($userId);
    }

    /**
     * return number of comments that user commented
     * @param int $userId
     * @return Int
     */
    private function getNoOfCommentsBy($userId) {
        return $this->buzzService->getNoOfCommentsByEmployeeNumber($userId);
    }

    /**
     * return number of likes that user like on shares
     * @param int $userId
     * @return Int
     */
    private function getNoOfShareLikesFor($userId) {
        return $this->buzzService->getNoOfShareLikesForEmployeeByEmployeeNumber($userId);
    }

    /**
     * return number of likes that user like on comments
     * @param int $userId
     * @return Int
     */
    private function getNoOfCommentLikesFor($userId) {
        return $this->buzzService->getNoOfCommentLikesForEmployeeByEmployeeNumber($userId);
    }

    /**
     * return number of shares that user share
     * @param int $userId
     * @return Int
     */
    private function getNoOfCommentsFor($userId) {
        return $this->buzzService->getNoOfCommentsForEmployeeByEmployeeNumber($userId);
    }

    public function execute($request) {
        $this->loggedInUser = $this->getLogedInEmployeeNumber();
        
        $this->buzzService = $this->getBuzzService();
        $this->profileUserId = $request->getParameter('profileUserId');
        $this->noOfShares = $this->getNoOfSharesBy($this->profileUserId);
        $this->noOfComments = $this->getNoOfCommentsBy($this->profileUserId);
        $this->noOfShareLikesRecieved = $this->getNoOfShareLikesFor($this->profileUserId);
        $this->noOfCommentLikesRecieved = $this->getNoOfCommentLikesFor($this->profileUserId);
        $this->noOfCommentsRecieved = $this->getNoOfCommentsFor($this->profileUserId);
    }

}
