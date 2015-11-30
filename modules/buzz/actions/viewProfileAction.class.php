<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of viewProfileAction
 *
 * @author dewmal
 */
class viewProfileAction extends BaseBuzzAction {

    const TERMINATED = "TERMINATED";

    /**
     * get employee search form
     * @return searchForm
     */
    private function getSearchForm() {
        if (!($this->searchForm instanceof BuzzEmployeeSearchForm)) {
            $this->searchForm = new BuzzEmployeeSearchForm();
        }
        return $this->searchForm;
    }

    /**
     * return action validate form for validate actions
     * @return ActionValidateForm
     */
    private function getActionValidateForm() {
        if (!$this->actionValidateForm instanceof ActionValidatingForm) {
            $this->actionValidateForm = new ActionValidatingForm();
        }
        return $this->actionValidateForm;
    }

    public function execute($request) {
        $template = $this->getContext()->getConfiguration()->getTemplateDir('buzz', 'chatter.php');
        $this->setLayout($template . '/chatter');
        $this->searchForm = $this->getSearchForm();
        $this->actionValidateForm = $this->getActionValidateForm();

        try {
            $this->loggedInUser = $this->getLogedInEmployeeNumber();
            $this->profileUserId = $request->getParameter('empNumber');
            $this->employee = $this->getEmployeeService()->getEmployee($this->profileUserId);
            if ($this->employee) {
                if ($this->employee->getState() == viewProfileAction::TERMINATED) {
                    $this->redirect('buzz/viewBuzz');
                }
            }
            $this->intializeConfigValuves();
            $this->initializePostList();

            $this->refreshStatsForm = $this->getRefreshStatsForm();
            $this->likedOrSharedEmployeeForm = $this->getLikedOrSharedEmployeeForm();
            $this->loadMorePostsForm = $this->getLoadMorePostsForm();
            $this->deleteOrEditShareForm = $this->getDeleteOrEditShareForm();
            $this->deleteOrEditCommentForm = $this->getDeleteOrEditCommentForm();
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

    protected function getRefreshStatsForm() {
        return new RefreshStatsForm();
    }

    protected function getLikedOrSharedEmployeeForm() {
        return new LikedOrSharedEmployeeForm();
    }

    protected function getLoadMorePostsForm() {
        return new LoadMorePostsForm();
    }

    protected function getDeleteOrEditShareForm() {
        return new DeleteOrEditShareForm();
    }

    protected function getDeleteOrEditCommentForm() {
        return new DeleteOrEditCommentForm();
    }

    /**
     * Retrieving the list of posts from database.
     */
    protected function initializePostList() {
        $buzzService = $this->getBuzzService();
        $userId = $this->profileUserId;

        $this->postList = $buzzService->getSharesByEmployeeNumber($this->shareCount, $userId);
        $this->allShareCount = $buzzService->getNoOfSharesByEmployeeNumber($userId);
    }

    /**
     * initialize config valuves from database
     */
    protected function intializeConfigValuves() {
        $buzzConfigService = $this->getBuzzConfigService();
        $this->shareCount = $buzzConfigService->getBuzzShareCount();
        $this->commentCount = $buzzConfigService->getBuzzInitialCommentCount();
        $this->viewMoreComment = $buzzConfigService->getBuzzViewCommentCount();
        $this->likeCount = $buzzConfigService->getBuzzLikeCount();
        $this->refeshTime = $buzzConfigService->getRefreshTime();
    }

}
