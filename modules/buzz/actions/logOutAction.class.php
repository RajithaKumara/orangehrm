<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of logOutAction
 *
 * @author dewmal
 */
class logOutAction extends BuzzBaseAction{
    
    public function execute($request) {
        $this->logOut();

    $this->redirect('auth/login');
    }

//put your code here
}
