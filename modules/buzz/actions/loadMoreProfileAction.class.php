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

    public function execute($request) {
        try {
            $this->loggedInUser = $this->getUserId();
            $this->lastPostId = $request->getParameter('lastPostId');
            $this->profileUserId = $request->getParameter('profileUserId');
            $this->buzzService = $this->getBuzzService();

            $this->nextSharesList = $this->buzzService->getMoreProfileShares(5, $this->lastPostId, $this->profileUserId);
            $this->editForm = new CommentForm();
            $this->commentForm = $this->getCommentForm();
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

    /**
     * 
     * @param type $buzzService
     */
    protected function setBuzzService($buzzService) {
        $this->buzzService = $buzzService;
    }

    /**
     * 
     * @return BuzzService
     */
    private function getBuzzService() {
        if (!$this->buzzService) {
            $this->setBuzzService(new BuzzService());
        }
        return $this->buzzService;
    }

    /**
     * 
     * @param AddTaskForm $form
     */
    private function setPostForm($form) {
        $this->postForm = $form;
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

}
