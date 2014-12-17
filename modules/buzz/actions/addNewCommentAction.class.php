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
 * Description of addNewCommentAction
 *
 * @author aruna
 */
class addNewCommentAction extends BaseBuzzAction {

    

    public function execute($request) {
        try {
            $this->loggedInUser = $this->getUserId();
            $this->commentText = $request->getParameter('commentText');
            $this->shareId = $request->getParameter('shareId');
            $this->error = 'no';

            try {
                $share = $this->getBuzzService()->getShareById($this->shareId);
                $this->editForm = new CommentForm();
                $this->saveComment();
            } catch (Exception $ex) {
                $this->error = 'yes';
                $this->getUser()->setFlash('error', __("This share has been deleted or you do not have permission to perform this action"));
            }
        } catch (Exception $ex) {
            $this->error = 'redirect';
        }
    }

    /**
     * save comment to the database
     * @return Comment
     */
    public function saveComment() {
        $comment = $this->setComment();
        $this->comment = $this->getBuzzService()->saveCommentShare($comment);
        $this->commentPostId = $this->comment->getShareId();
        $this->commentEmployeeName = $this->comment->getEmployeeFirstLastName();
        $this->commentContent = $this->comment->getCommentText();
        $this->commentDate = $this->comment->getDate();
        $this->commentTime = $this->comment->getTime();
        $this->commentId = $this->comment->getId();
        $this->commentNoOfLikes = $this->comment->getNumberOfLikes();
        $this->isLikeComment = $this->comment->isLike($this->getUserId());
        $this->commentEmployeeId = $this->getUserId();                
                
                
        $this->commentNoOfLikes = $this->comment->getNumberOfLikes();
        $this->commentNoOfUnLikes = $this->comment->getNumberOfUnlikes();
                if ($this->comment->isUnLike($this->getUserId())) {
                    $this->isUnlike = 'yes';
                }
                
               
    }

    /**
     * set valuves to the comment
     * @return Comment
     */
    public function setComment() {
        $comment = new Comment();
        $comment->setShareId($this->shareId);
        $comment->setEmployeeNumber($this->getUserId());
        $comment->setCommentText($this->commentText);
        $comment->setCommentTime(date("Y-m-d H:i:s"));
        $comment->setNumberOfLikes(0);
         $comment->setNumberOfUnlikes(0);
        return $comment;
    }

}
