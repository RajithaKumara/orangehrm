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
      private $employeeService;

    /**
     * Get EmployeeService
     * @returns EmployeeService
     */
    public function getEmployeeService() {
        if(is_null($this->employeeService)) {
            $this->employeeService = new EmployeeService();
            $this->employeeService->setEmployeeDao(new EmployeeDao());
        }
        return $this->employeeService;
    }
    
    /**
     * get employee search form
     * @return searchForm
     */
    private function getSearchForm(){
        if(!($this->searchForm instanceof BuzzEmployeeSearchForm)){
            $this->searchForm = new BuzzEmployeeSearchForm();
        }
        return $this->searchForm;
    }

    public function execute($request) {
        $template = $this->getContext()->getConfiguration()->getTemplateDir('buzz', 'chatter.php');
        $this->setLayout($template . '/chatter');
        $this->searchForm = $this->getSearchForm();
        try {
            $this->loggedInUser = $this->getLogedInEmployeeNumber();
            $this->profileUserId = $request->getParameter('empNumber');
            $this->employee = $this->getEmployeeService()->getEmployee($this->profileUserId);
            $this->intializeConfigValuves();
            $this->initializePostList();
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }

    }

    /**
     * Retrieving the list of posts from database.
     */
    protected function initializePostList() {
        $buzzService = $this->getBuzzService();
        $userId = $this->profileUserId;

        $this->postList = $buzzService->getSharesByEmployeeNumber($this->shareCount, $userId);
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
