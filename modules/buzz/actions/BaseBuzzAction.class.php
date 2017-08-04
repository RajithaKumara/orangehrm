<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of baseAction
 *
 * @author dewmal
 */
abstract class BaseBuzzAction extends sfAction {

    protected $buzzService;
    protected $buzzConfigService;
    protected $BuzzUserService;
    protected $ohrmCookieManager;
    protected $employeeService;

    const EMP_DELETED = 'empDeleted';
    const EMP_NUMBER = 'empNumber';
    const EMP_NAME = 'empName';
    const EMP_JOB_TITLE = 'jobTitle';
    const LABEL_EMPLOYEE_DELETED = 'Deleted Employee';

    /**
     * 
     * @return BuzzService
     */
    protected function getBuzzService() {
        if (!$this->buzzService instanceof BuzzService) {
            $this->buzzService = new BuzzService();
        }
        return $this->buzzService;
    }

    /**
     * Get Employee Service
     * @return EmployeeService
     */
    protected function getEmployeeService() {
        if (!$this->employeeService instanceof EmployeeService) {
            $this->employeeService = new EmployeeService();
        }
        return $this->employeeService;
    }

    /**
     * 
     * @return BuzzConfigService
     */
    protected function getBuzzConfigService() {
        if (!$this->buzzConfigService instanceof BuzzConfigService) {
            $this->buzzConfigService = new BuzzConfigService();
        }
        return $this->buzzConfigService;
    }

    /**
     * 
     * @return BuzzUserService
     */
    protected function getBuzzUserService() {
        if (!$this->buzzUserService instanceof BuzzUserService) {
            $this->buzzUserService = new BuzzUserService();
        }
        return $this->buzzUserService;
    }

    /**
     * 
     * @return CookieManager
     */
    protected function getOhrmCookieManager() {
        if (!$this->ohrmCookieManager instanceof CookieManager) {
            $this->ohrmCookieManager = new CookieManager();
        }
        return $this->ohrmCookieManager;
    }

    /**
     * get logged in employee number
     * @return employee number
     * @throws Exception
     */
    public function getLogedInEmployeeNumber() {
        $employeeNumber = $this->getBuzzUserService()->getEmployeeNumber();
        return $employeeNumber;
    }

    /**
     * get loged in employee user role
     * @return type
     */
    public function getLoggedInEmployeeUserRole() {
        $employeeUserRole = $this->getBuzzUserService()->getEmployeeUserRole();
        return $employeeUserRole;
    }

}
