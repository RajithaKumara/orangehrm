<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuzzUserService
 *
 * @author dewmal
 */
class BuzzUserService extends BaseService {

    /**
     * get employee Number
     * @return type
     */
    public function getEmployeeNumber() {
        $employeeNumber = $this->getUserFromSession()->getAttribute('auth.empNumber');
        return $employeeNumber;
    }

    /**
     * get employee user role
     * @return type
     */
    public function getEmployeeUserRole() {
        return $this->getLoggedInEmployeeUserRole();
    }

    public function isAdminLoged() {
        return ($this->getLoggedInEmployeeUserRole() == 'Admin');
    }

    public function getLoggedInEmployeeUserRole() {
        $employeeUserRole = null;
        if (UserRoleManagerFactory::getUserRoleManager()->getUser() != null) {
            if ($this->getUserFromSession()->getAttribute('auth.isAdmin') == 'Yes') {
                $employeeUserRole = 'Admin';
            } else {
                $employeeUserRole = 'Ess';
            }
        }
        return $employeeUserRole;
    }

    public function getUserFromSession(){
        return sfContext::getInstance()->getUser();
    }
}
