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
class likeOnCommentAction extends BuzzBaseAction {

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
        try{
            $this->loggedInUser=  $this->getUserId();
             
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
        $this->commentId = $request->getParameter('commentId');
        $this->likeAction = $request->getParameter('likeAction');
        try {

            $this->comment = $this->getBuzzService()->getCommentById($this->commentId);

            $this->likeOnComment();
        } catch (Exception $ex) {
            
        }
    }

    /**
     * save like on comment
     * @return LikeOnComment
     */
    public function likeOnComment() {
        $like = $this->setLike();
        $unlike= $this->setUnLike();
        $delete='no';
        if ($this->likeAction == 'unlike') {
            if ($this->comment->isLike($this->getUserId()) == 'Unlike') {
                $this->getBuzzService()->deleteLikeForComment($like);
                $delete='yes';
            }
            if($this->comment->isUnLike($this->getUserId()) =='no') {
                $this->getBuzzService()->saveUnLikeForComment($unlike);
                $this->likeLabel = 'Like';
                 $arr = array ('states'=>'savedUnLike','deleted'=>$delete);

    echo json_encode($arr);
    die();
            }
            $arr = array ('states'=>'Like');

    echo json_encode($arr);
    die();
        } else {
            if ($this->comment->isUnLike($this->getUserId())=='yes') {
                $this->getBuzzService()->deleteUnLikeForComment($unlike);
                $delete='yes';
            }
            if ($this->comment->isLike($this->getUserId()) == 'Like') {
                $this->getBuzzService()->saveLikeForComment($like);
                $this->likeLabel = 'Like';
                $arr = array ('states'=>'savedLike','deleted'=>$delete);

    echo json_encode($arr);
    die();
            }
            $arr = array ('states'=>'Liked');

    echo json_encode($arr);
    die();
        }
    }

    /**
     * set like on comment data
     * @return \LikeOnComment
     */
    public function setLike() {
        $like = New LikeOnComment();
        $like->setLikeTime(date("Y-m-d H:i:s"));
        $like->setEmployeeNumber($this->getUserId());
        $like->setCommentId($this->commentId);
        return $like;
    }
    
    public function setUnLike() {
        $like = New UnLikeOnComment();
        $like->setLikeTime(date("Y-m-d H:i:s"));
        $like->setEmployeeNumber($this->getUserId());
        $like->setCommentId($this->commentId);
        return $like;
    }

//put your code here
}