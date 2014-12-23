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

    public function getLogedInEmployeeNumber() {
        $employeeNumber = null;
        if (UserRoleManagerFactory::getUserRoleManager()->getUser() != null) {
            if ($this->getUser()->getAttribute('auth.isAdmin', 'No') == 'Yes') {
                $userRole = 'Admin';
            } else {
                $userRole = 'Ess';
            }
            $employeeNumber = $this->getUser()->getAttribute('auth.empNumber');
            $this->getBuzzCookieService()->saveCookieValuves($employeeNumber, $userRole);
        } elseif ($this->getBuzzCookieService()->isSavedCookies()) {
            $employeeNumber = $this->getBuzzCookieService()->getEmployeeNumber();
        } else {
            throw new Exception('User Didnot Have');
        }

        return $employeeNumber;
    }

    public function logOut() {
        $this->getBuzzCookieService()->destroyCokkies();
    }
    
    

}
