<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuzzCookieService
 *
 * @author dewmal
 */
class BuzzCookieService extends BaseService {

    const COOKIE_NAME_EMPLOYE_NUMBER = 'buzzCookieEmployeeNumber';
    const COOKIE_NAME_USER_ROLE = 'buzzCookieUserRole';
      protected $buzzConfigService;
    
    /**
     * this is function to get CookieManager
     * @return cookieManager
     */
    public function getCookieManager() {
        if (!($this->cookieManager instanceof CookieManager)) {
            $this->cookieManager = new CookieManager();
        }
        return $this->cookieManager;
    }
 
    /**
     * 
     * @return BuzzConfigService
     */
    public function getBuzzConfigService() {
        if (!$this->buzzConfigService instanceof BuzzConfigService) {
            $this->buzzConfigService = new BuzzConfigService();
        }
        return $this->buzzConfigService;
    }

    /**
     * set cookie manager
     * @param CookieManager $cookieManager
     */
    public function setCookieManager(CookieManager $cookieManager) {
        $this->cookieManager = $cookieManager;
    }
    
    /**
     * save user role cookie
     * @param type $userRole
     */
    public function saveUserRole($userRole){
        $cookieValidTime = $this->getBuzzConfigService()->getCookieValidTime();
        $this->getCookieManager()->setCookie(self::COOKIE_NAME_USER_ROLE,$userRole,time() + $cookieValidTime, "/");
        return $userRole;
    }
    
    /**
     * save user role cookie
     * @param type $userRole
     */
    public function saveEmployeeNumber($employeNumber){
        $cookieValidTime = $this->getBuzzConfigService()->getCookieValidTime();
        $this->getCookieManager()->setCookie(self::COOKIE_NAME_EMPLOYE_NUMBER,$employeNumber,time() + $cookieValidTime, "/");
        return $employeNumber;
    }
    
    /**
     * get employee Number
     * @return type
     */
    public function getEmployeeNumber(){
        return $this->getCookieManager()->readCookie(self::COOKIE_NAME_EMPLOYE_NUMBER);
    }
    
    /**
     * get employee user role
     * @return type
     */
    public function getEmployeeUserRole(){
        return $this->getCookieManager()->readCookie(self::COOKIE_NAME_USER_ROLE);
    }
    
    /**
     * destroy cookie valuves saved
     */
    public function destroyCokkies(){
        $this->getCookieManager()->destroyCookie(self::COOKIE_NAME_USER_ROLE, "/");
        $this->getCookieManager()->destroyCookie(self::COOKIE_NAME_EMPLOYE_NUMBER, "/");
    }
    
    /**
     * check cookie valuve is saved
     * @return type
     */
    public function isSavedCookies(){
        return $this->getCookieManager()->isCookieSet(self::COOKIE_NAME_USER_ROLE);
    }
    
    public function saveCookieValuves($employeNumber,$userRole){
        $cookieValidTime = $this->getBuzzConfigService()->getCookieValidTime();
        
        $this->getCookieManager()->setCookie(self::COOKIE_NAME_USER_ROLE,$userRole,time() + $cookieValidTime, "/");
        $this->getCookieManager()->setCookie(self::COOKIE_NAME_EMPLOYE_NUMBER,$employeNumber,time() + $cookieValidTime, "/");
    }
    
    public function isAdminLoged(){
        
        return ($this->getCookieManager()->readCookie(self::COOKIE_NAME_USER_ROLE)=='Admin');
    }
}
