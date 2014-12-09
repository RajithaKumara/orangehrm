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
class likeOnShareAction extends BuzzBaseAction {

    /**
     * this is function to get buzzService
     * @return buzzService 
     */
    public function getBuzzService() {
        if (!$this->buzzService) {
            $this->buzzService = new BuzzService();
        }
        return $this->buzzService;
    }

    

    public function execute($request) {
        
            $this->loggedInUser=  $this->getUserId();
            $this->shareId = $request->getParameter('shareId');
        
        $this->likeAction = $request->getParameter('likeAction');
        $this->error = 'no';
        $this->commentForm = $this->getCommentForm();
        $this->editForm = new CommentForm();
        $this->share = $this->getBuzzService()->getShareById($this->shareId);
       
             
        
        
         $this->likeOnShare();
        try {

            
            
        } catch (Exception $ex) {
            
            $this->error = 'yes';
            $this->getUser()->setFlash('error', __("This post has been deleted or you do not have permission to perform this action"));
        }
    }

    /**
     * save like on share to database
     * @return LikeOnshare
     */
    public function likeOnShare() {
        $like = $this->setLike();
        $unlike= $this->setUnLike();
        
        if ($this->likeAction == 'unlike') {
            $delete='no';
            if ($this->share->isLike($this->loggedInUser) == 'Unlike') {
                
                $this->getBuzzService()->deleteLikeForShare($like);
                $delete='yes';
                
            }
            if($this->share->isUnLike($this->loggedInUser)== 'no') {
                $this->getBuzzService()->saveUnLikeForShare($unlike);
                $this->likeLabel = 'Like';
                  $arr = array ('states'=>'savedUnLike','deleted'=>$delete);

                    echo json_encode($arr);
                    die();
            }
            
              $arr = array ('states'=>'Like','deleted'=>$delete);

    echo json_encode($arr);
    die();
        } else {
            $delete='no';
            if ($this->share->isUnLikeUser($this->loggedInUser)=='yes') {
                $this->getBuzzService()->deleteUnLikeForShare($unlike);
                $delete='yes';
            }
            if ($this->share->isLike($this->loggedInUser) == 'Like') {
                $this->getBuzzService()->saveLikeForShare($like);
                $this->likeLabel = 'Like';
                $arr = array ('states'=>'savedLike','deleted'=>$delete);

    echo json_encode($arr);
    die();
            }
            $arr = array ('states'=>'Liked','deleted'=>$delete);

    echo json_encode($arr);
    die();
        }
    }

    /**
     * set like on share Data
     * @return \LikeOnShare
     */
    public function setLike() {
        $like = New LikeOnShare();
        $like->setLikeTime(date("Y-m-d H:i:s"));
        $like->setEmployeeNumber($this->getUserId());
        $like->setShareId($this->shareId);
        return $like;
    }
    
    public function setUnLike() {
        $like = New UnLikeOnShare();
        $like->setLikeTime(date("Y-m-d H:i:s"));
        $like->setEmployeeNumber($this->getUserId());
        $like->setShareId($this->shareId);
        return $like;
    }
    /**
     * 
     * @param CommentForm $form
     */
    private function setCommentForm($form) {
        $this->commentForm = $form;
    }

    /**
     * 
     * @return CommentForm
     */
    private function getCommentForm() {
        if (!$this->commentForm) {
            $this->setCommentForm(new CommentForm());
        }
        return $this->commentForm;
    }

//put your code here
}