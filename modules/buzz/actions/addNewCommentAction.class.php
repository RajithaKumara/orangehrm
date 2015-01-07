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

    /**
     * function to get edit form
     * @return CommentForm
     */
    private function getEditForm() {
        if (!($this->editForm instanceof CommentForm)) {
            $this->editForm = new CommentForm();
        }
        return $this->editForm;
    }

    /**
     * function to get edit form
     * @return CommentForm
     */
    private function getCommentForm() {
        if (!($this->commentForm instanceof CommentForm)) {
            $this->commentForm = new CommentForm();
        }
        return $this->commentForm;
    }

    public function execute($request) {
        try {
            $this->loggedInUser = $this->getLogedInEmployeeNumber();
            $this->loggedInEmployeeUserRole = $this->getLoggedInEmployeeUserRole();
            $this->commentForm = $this->getCommentForm();
            $this->editForm = $this->getEditForm();
            $this->isSuccessfullyAddedComment = false;
            if ($request->isMethod('post')) {
                $this->commentForm->bind($request->getParameter($this->commentForm->getName()));
                if ($this->commentForm->isValid()) {
                    if ($this->getBuzzService()->getShareById($this->commentForm->getValue('shareId')) != null) {
                        $commentSaved = $this->commentForm->saveComment($this->loggedInUser);
                        $this->setCommentVariablesForView($commentSaved);
                        $this->isSuccessfullyAddedComment = true;
                    } else {
                        $this->getUser()->setFlash('error', __("This share has been deleted or you do not have permission to perform this action"));
                    }
                } else {
                    $this->getUser()->setFlash('error', __("This share has been deleted or you do not have permission to perform this action"));
                }
            }
        } catch (Exception $ex) {
            $this->error = 'redirect';
        }
    }

    /**
     * save comment to the database
     * @return Comment
     */
    public function setCommentVariablesForView($comment) {
        $this->comment = $comment;
        $this->commentPostId = $this->comment->getShareId();
        $this->commentEmployeeName = $this->comment->getEmployeeFirstLastName();
        $this->commentEmployeeJobTitle = $this->comment->getEmployeeComment()->getJobTitleName();
        $this->commentContent = $this->comment->getCommentText();
        $this->commentDate = $this->comment->getDate();
        $this->commentTime = $this->comment->getTime();
        $this->commentId = $this->comment->getId();
        $this->commentNoOfLikes = $this->comment->getNumberOfLikes();
        $this->isLikeComment = $this->comment->isLike($this->getLogedInEmployeeNumber());
        $this->commentEmployeeId = $this->getLogedInEmployeeNumber();
        $this->commentNoOfLikes = $this->comment->getNumberOfLikes();
        $this->commentNoOfUnLikes = $this->comment->getNumberOfUnlikes();
        if ($this->comment->isUnLike($this->getLogedInEmployeeNumber())) {
            $this->isUnlike = 'yes';
        }
    }

}
