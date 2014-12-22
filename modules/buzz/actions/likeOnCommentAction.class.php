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
class likeOnCommentAction extends BaseBuzzAction {

    public function execute($request) {
        try {
            $this->loggedInUser = $this->getLogedInEmployeeNumber();
            $this->commentId = $request->getParameter('commentId');
            $this->likeAction = $request->getParameter('likeAction');
            $this->comment = $this->getBuzzService()->getCommentById($this->commentId);
            if ($this->likeAction == 'unlike') {
                $this->unlikeOnComment();
            } else {
                $this->likeOnComment();
            }
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

    /**
     * save like on comment
     * @return LikeOnComment
     */
    public function likeOnComment() {
        $like = $this->setLike();
        $unlike = $this->setUnLike();
        $delete = 'no';
        $state = 'Like';
        if ($this->comment->isUnLike($this->loggedInUser) == 'yes') {
            $this->getBuzzService()->deleteUnLikeForComment($unlike);
            $delete = 'yes';
        }
        if ($this->comment->isLike($this->loggedInUser) == 'Like') {
            $this->getBuzzService()->saveLikeForComment($like);
            $state = 'savedLike';
        }
        $commentSaved = $this->getBuzzService()->getCommentById($this->commentId);

        $arr = array('states' => $state, 'deleted' => $delete, 'likeCount' => $commentSaved->getNumberOfLikes(), 'unlikeCount' => $commentSaved->getNumberOfUnlikes());

        echo json_encode($arr);
        die();
    }

    private function unlikeOnComment() {
        $like = $this->setLike();
        $unlike = $this->setUnLike();
        $delete = 'no';
        $state = 'Like';
        if ($this->comment->isLike($this->loggedInUser) == 'Unlike') {
            $this->getBuzzService()->deleteLikeForComment($like);
            $delete = 'yes';
        }
        if ($this->comment->isUnLike($this->loggedInUser) == 'no') {
            $this->getBuzzService()->saveUnLikeForComment($unlike);
            $this->likeLabel = 'Like';
            $state = 'savedUnLike';
        }

        $commentSaved = $this->getBuzzService()->getCommentById($this->commentId);

        $arr = array('states' => $state, 'deleted' => $delete, 'likeCount' => $commentSaved->getNumberOfLikes(), 'unlikeCount' => $commentSaved->getNumberOfUnlikes());

        echo json_encode($arr);
        die();
    }

    /**
     * set like on comment data
     * @return \LikeOnComment
     */
    public function setLike() {
        $like = New LikeOnComment();
        $like->setLikeTime(date("Y-m-d H:i:s"));
        $like->setEmployeeNumber($this->getLogedInEmployeeNumber());
        $like->setCommentId($this->commentId);
        return $like;
    }

    public function setUnLike() {
        $like = New UnLikeOnComment();
        $like->setLikeTime(date("Y-m-d H:i:s"));
        $like->setEmployeeNumber($this->getLogedInEmployeeNumber());
        $like->setCommentId($this->commentId);
        return $like;
    }

//put your code here
}
