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
            } else {
                $userRole = 'Ess';
            }
            $employeeNumber = $this->getUser()->getAttribute('auth.empNumber');
            if ($this->getBuzzCookieService()->getEmployeeNumber() != $employeeNumber) {
                $this->getBuzzCookieService()->saveCookieValuves($employeeNumber, $userRole);
            }
        } elseif ($this->getBuzzCookieService()->isSavedCookies()) {
            $employeeNumber = $this->getBuzzCookieService()->getEmployeeNumber();
        } else {
            throw new Exception('User Didnot Have');
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
