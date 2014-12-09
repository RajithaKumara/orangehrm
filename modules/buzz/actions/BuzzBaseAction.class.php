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
abstract class BuzzBaseAction extends sfAction{
    const COOKIE_NAME='buzzCookie';
    static $state = '';
    public function getUserId(){
        
        $cookie_name='buzzCookie';
        
        if(UserRoleManagerFactory::getUserRoleManager()->getUser()!=null){
            $cookie_valuve='';
            if($this->getUser()){
            $cookie_valuve = $this->getUser()->getEmployeeNumber();
            }
            setcookie($cookie_name, $cookie_valuve, time() + 3600 * 24 * 30, "/");
            return $cookie_valuve;
        } elseif (isset($_COOKIE[$cookie_name])) {
            return $_COOKIE[$cookie_name];
        } else {
            throw new Exception('User Didnot Have');
        }
    }
    
    public function logOut(){
        BuzzBaseAction::$state='logOut';
         $cookie_name='buzzCookie';
    unset($_COOKIE[$cookie_name]);
// empty value and expiration one hour before
    $res = setcookie($cookie_name, '', time() - 3600,"/");

    
    }
}
