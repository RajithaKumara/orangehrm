<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of loadMoreProfileAction
 *
 * @author dewmal
 */
class loadMoreProfileAction extends BaseBuzzAction {

    protected $buzzService;
    protected $systemUserService;

    /**
     * 
     * @param AddTaskForm $form
     */
    private function setPostForm($form) {
        $this->postForm = $form;
    }

    /**
     * 
     * @return SystemUserService
     */
    private function getSystemUserService() {
        if (!$this->systemUserService) {
            $this->systemUserService = new SystemUserService();
        }

        return $this->systemUserService;
    }

    /**
     * 
     * @return AddTaskForm
     */
    private function getPostForm() {
        if (!$this->postForm) {
            $this->setPostForm(new CreatePostForm());
        }
        return $this->postForm;
    }

    /**
     * 
     * @param AddTaskForm $form
     */
    private function setCommentForm($form) {
        $this->commentForm = $form;
    }

    /**
     * 
     * @return AddTaskForm
     */
    private function getCommentForm() {
        if (!$this->commentForm) {
            $this->setCommentForm(new CommentForm());
        }
        return $this->commentForm;
    }

    public function execute($request) {
        try {
            $this->loggedInUser = $this->getLogedInEmployeeNumber();
            $this->lastPostId = $request->getParameter('lastPostId');
            $this->profileUserId = $request->getParameter('profileUserId');
            $this->buzzService = $this->getBuzzService();
            $this->shareCount = $this->getShareCount();
            if ($this->profileUserId == $this->getSystemUserService()->getSystemUser(1)->getEmployee()->empNumber && $this->profileUserId != null) {
                $lastPost = $this->buzzService->getShareById($this->lastPostId);
                if ($lastPost->getEmployeeNumber() == null) {
                    $this->postListAsEmploye = array();
                } else {
                    $this->postListAsEmployee = $this->buzzService->getMoreEmployeeSharesByEmployeeNumber($this->shareCount, $this->lastPostId, $this->profileUserId);
                }
            } else {
                $this->postListAsEmployee = $this->buzzService->getMoreEmployeeSharesByEmployeeNumber($this->shareCount, $this->lastPostId, $this->profileUserId);
            }
            if (sizeof($this->postListAsEmployee) < $this->shareCount && $this->profileUserId == $this->getSystemUserService()->getSystemUser(1)->getEmployee()->empNumber && $this->profileUserId != null) {

                if (sizeof($this->postListAsEmployee) == 0) {
                    $this->postListAsAdmin = $this->buzzService->getMoreEmployeeSharesByEmployeeNumber($this->shareCount, $this->lastPostId, NULL);
                } else {
                    $this->postListAsAdmin = $this->buzzService->getSharesByEmployeeNumber(($this->shareCount - sizeof($this->postListAsEmployee)), null);
                }
            }
            $this->editForm = new CommentForm();
            $this->commentForm = $this->getCommentForm();
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

    /**
     * get share count 
     * @return Int
     */
    protected function getShareCount() {
        $buzzConfigService = $this->getBuzzConfigService();
        return $buzzConfigService->getBuzzShareCount();
    }

}
