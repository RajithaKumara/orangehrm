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
    protected $buzzCookieService;
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
     * @return BuzzCookieService
     */
    protected function getBuzzCookieService() {
        if (!$this->buzzCookieService instanceof BuzzCookieService) {
            $this->buzzCookieService = new BuzzCookieService();
        }
        return $this->buzzCookieService;
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
        $employeeNumber = null;
        if (UserRoleManagerFactory::getUserRoleManager()->getUser() != null) {
            if ($this->getUser()->getAttribute('auth.isAdmin') == 'Yes') {
                $userRole = 'Admin';
                $isAdmin = true;
            } else {
                $userRole = 'Ess';
            }
            $employeeNumber = $this->getUser()->getAttribute('auth.empNumber');
            if ($this->getBuzzCookieService()->getEmployeeNumber() != $employeeNumber || ($isAdmin && $employeeNumber == null)) {
                $this->getBuzzCookieService()->saveCookieValuves($employeeNumber, $userRole);
            }
        } elseif ($this->getBuzzCookieService()->isSavedCookies()) {
            $employeeNumber = $this->getBuzzCookieService()->getEmployeeNumber();
        } else {
            $this->redirect('auth/logout');
        }

        return $employeeNumber;
    }

    /**
     * function to delete cookies
     */
    public function logOut() {
        $this->getBuzzCookieService()->destroyCokkies();
    }

    /**
     * get loged in employee user role
     * @return type
     */
    public function getLoggedInEmployeeUserRole() {
        $employeeUserRole = null;
        if (UserRoleManagerFactory::getUserRoleManager()->getUser() != null) {
            if ($this->getUser()->getAttribute('auth.isAdmin') == 'Yes') {
                $employeeUserRole = 'Admin';
            } else {
                $employeeUserRole = 'Ess';
            }
            $employeeNumber = $this->getUser()->getAttribute('auth.empNumber');
            if ($this->getBuzzCookieService()->getEmployeeNumber() != $employeeNumber) {
                $this->getBuzzCookieService()->saveCookieValuves($employeeNumber, $employeeUserRole);
            }
        } elseif ($this->getBuzzCookieService()->isSavedCookies()) {
            $employeeUserRole = $this->getBuzzCookieService()->getEmployeeUserRole();
        } else {
            throw new Exception('User Didnot Have');
        }

        return $employeeUserRole;
    }

}
