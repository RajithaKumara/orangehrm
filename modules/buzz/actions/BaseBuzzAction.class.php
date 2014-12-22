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

    const COOKIE_NAME = 'buzzCookie';
    protected $buzzService;
    protected $buzzConfigService;
    
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

    public function getLogedInEmployeeNumber() {
        $employeeNumber = null;
        $cookieValidTime= $this->getBuzzConfigService()->getCookieValidTime();
        if (UserRoleManagerFactory::getUserRoleManager()->getUser() != null) {

            $cookie_valuve = $this->getUser()->getEmployeeNumber();
            if ($cookie_valuve == "") {
                //get it from the configuration
                setcookie(self::COOKIE_NAME, 'Admin', time() + $cookieValidTime, "/");
                
            } else {
                setcookie(self::COOKIE_NAME, $cookie_valuve, time() + $cookieValidTime, "/");
            }

            $employeeNumber = $cookie_valuve;
        } elseif (isset($_COOKIE[self::COOKIE_NAME])) {
            if ($_COOKIE[self::COOKIE_NAME] == 'Admin') {
                $employeeNumber = null;
                 
            }else{
                $employeeNumber = $_COOKIE[self::COOKIE_NAME];
            }
        } else {
            throw new Exception('User Didnot Have');
        }
        
        return $employeeNumber;
    }

    public function logOut() {
        unset($_COOKIE[self::COOKIE_NAME]);
        setcookie(self::COOKIE_NAME, '', time() - 3600, "/");
    }

}
