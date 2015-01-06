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
    
    /**
     * get share count 
     * @return Int
     */
    protected function getShareCount() {
        $buzzConfigService = $this->getBuzzConfigService();
        return $buzzConfigService->getBuzzShareCount();
    }

    public function execute($request) {
        try {
            $this->loggedInUser = $this->getLogedInEmployeeNumber();
            $this->lastPostId = $request->getParameter('lastPostId');
            $this->profileUserId = $request->getParameter('profileUserId');
            $this->buzzService = $this->getBuzzService();

            $this->nextSharesList = $this->buzzService->getMoreEmployeeSharesByEmployeeNumber($this->getShareCount(), $this->lastPostId, $this->profileUserId);
            $this->editForm = new CommentForm();
            $this->commentForm = $this->getCommentForm();
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

    }
