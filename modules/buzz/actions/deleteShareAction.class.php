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
 * Description of likeOnShareAction
 *
 * @author aruna
 */
class deleteShareAction extends BaseBuzzAction {

    /**
     * this is function to get buzzService
     * @return BuzzService 
     */
    public function getBuzzervice() {
        if (!$this->buzzService) {
            $this->buzzService = new BuzzService();
        }
        return $this->buzzService;
    }

    

    public function execute($request) {
        try{
            $this->loggedInUser=  $this->getUserId();
             
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
        $this->shareId = $request->getParameter('shareId');
        $this->deleteShare();
        return sfView::NONE;
    }

    /**
     * delete Share if it bis post then delete post
     */
    public function deleteShare() {
        try {
            $share = $this->getBuzzervice()->getShareById($this->shareId);
        if ($share->getType() == '0') {
            if ($share->getPostShared()->getEmployeeNumber() == $this->getUserId()) {
                $this->getBuzzervice()->deletePost($share->getPostId());
            } elseif($this->getUserId ()==''){
                $this->getBuzzervice()->deletePost($share->getPostId());
            }
        } else {
            if ($share->getEmployeeNumber() == $this->getUserId()) {
                $this->getBuzzervice()->deleteShare($share->getId());
            } 
            elseif($this->getUserId ()==''){
                $this->getBuzzervice()->deleteShare($share->getId());
            }
        }
            
        } catch (Exception $ex) {
            
        }
        
    }

//put your code here
}
