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
class editShareAction extends BaseBuzzAction {

    /**
     * this is function to get buzzService
     * @return BuzzService 
     */
    public function getBuzzService() {
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
        $this->editedContent = $request->getParameter('textShare');
        $this->type='post';
        $this->error='no';
        
        try{
            $share = $this->getBuzzService()->getShareById($this->shareId);
            $this->post=  $this->saveEditedContent();
        } catch (Exception $ex) {
            $this->error='yes';
            $this->getUser()->setFlash('error', __("This share has been deleted or you do not have permission to perform this action"));
        }
        
       
    }

    /**
     * save edited content of post and share
     * @return Post
     */
    public function saveEditedContent() {
        $share = $this->getBuzzService()->getShareById($this->shareId);
        if ($share->getEmployeeNumber() == $this->getUserId()) {
            if ($share->getTypeName() == 'share') {
                $this->type='share';
                return $this->saveShare($share);
                
            } else {
                if ($share->getPostShared()->getEmployeeNumber() == $this->getUserId()) {
                    return $this->savePost($share->getPostShared());
                } else {
                    
                }
            }
        } else {
            
        }
    }

    /**
     * save post to the database
     * @param Post $post
     * @return Post
     */
    public function savePost($post) {

        $post->setText($this->editedContent);

        return $this->getBuzzService()->savePost($post);
    }

    /**
     * savbe share to the database
     * @param Share $share
     * @return Share
     */
    public function saveShare($share) {
        $share->setText($this->editedContent);
        return $this->getBuzzService()->saveShare($share);
    }

}