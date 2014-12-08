<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of viewNewDashbordAction
 *
 * @author dewmal
 */
class viewNewDashbordAction extends BaseOrangehrmDashboardPluginAction{
    private $advancedUserRoleManager = null;
    private $authenticationService = null;
    private $systemUserService = null;
    private $advancedSystemUserDao = null;
    private $dashboardPanelsService = null;

    public function execute($request) {
        $this->settings = $this->setAllowedGroups();
    }

    protected function setAllowedGroups() {
        $allowedGroups = array();
        $allGroups = $this->getDashboardService()->getPanelsForLayout();
        $roleIds = $this->getLoggedInUserRoleIds();
        foreach ($allGroups as $oneGroup) {
            $allowedRoleIds = $oneGroup['attributes']['permissions'];
            // interpreting ess role as ess role type
            if (in_array(UserRole::USER_ROLE_ESS, $allowedRoleIds)) {
                $allowedRoleIds[] = $this->getContext()->getUser()->getAttribute('auth.essRoleId', UserRole::USER_ROLE_ESS);
            }
            if (in_array(UserRole::USER_ROLE_SUPERVISOR, $allowedRoleIds)) {
                $allowedRoleIds[] = $this->getContext()->getUser()->getAttribute('auth.supervisorRoleId', UserRole::USER_ROLE_SUPERVISOR);
            }
            if (in_array(UserRole::USER_ROLE_ADMIN, $allowedRoleIds)) {
                if ($this->getContext()->getUser()->hasAttribute('auth.adminRoleId')) {
                    $allowedRoleIds[] = $this->getContext()->getUser()->getAttribute('auth.adminRoleId');
                }
            }
            $allowedRoleIds = array_unique($allowedRoleIds);
            if (count(array_intersect($roleIds, $allowedRoleIds)) > 0) {                
                if ($oneGroup['type'] == DashboardPanelGroup::$TYPE_MISC) {
                    $count = 0;
                    foreach ($oneGroup['panels'] as $index => $panel) {
                        $moduleNames = $panel['attributes']['ohrm_module']; 
                        $enabled = $this->isModuleEnable($moduleNames);
                        if (!$enabled) {                           
                            unset($oneGroup['panels'][$index]); 
                        } else {
                            $count++;
                        }
                    }
                    if ($count != 0) {
                        $allowedGroups[] = $oneGroup;
                    }
                } else {
                    $allowedGroups[] = $oneGroup;
                }
            }
        }
        return $allowedGroups;
    }

    protected function getLoggedInUserRoleIds() {
        $this->setAuthenticationService();
        $userId = $this->getAuthenticationService()->getLoggedInUserId();

        $this->setSystemUserService();
        $systemUser = $this->getSystemUserService()->getSystemUser($userId);

        $this->setAdvancedUserRoleManager();
        $allowed = $this->getAdvancedUserRoleManager()->getUserRoles($systemUser);
        $roleIds = array();
        foreach ($allowed as $single) {
            $roleIds[] = strval($single->getId());
        }
        return $roleIds;
    }

    protected function setAdvancedUserRoleManager() {
        if (is_null($this->advancedUserRoleManager)) {
            $this->advancedUserRoleManager = new AdvancedUserRoleManager();
        }
    }

    protected function getAdvancedUserRoleManager() {
        return $this->advancedUserRoleManager;
    }

    protected function setAuthenticationService() {
        if (is_null($this->authenticationService)) {
            $this->authenticationService = new AuthenticationService();
        }
    }

    protected function getAuthenticationService() {
        return $this->authenticationService;
    }

    protected function setSystemUserService() {
        if (is_null($this->systemUserService)) {
            $this->systemUserService = new SystemUserService();
        }
    }

    protected function getSystemUserService() {
        return $this->systemUserService;
    }
    
    protected function setAdvancedSystemUserDao(AdvancedSystemUserDao $systemUserDao) {
        $this->advancedSystemUserDao = $systemUserDao;
    }

    /**
     * 
     * @return AdvancedSystemUserDao
     */
    protected function getAdvancedSystemUserDao() {
        if (is_null($this->advancedSystemUserDao)) {
            $this->advancedSystemUserDao = new AdvancedSystemUserDao();
        }
        return $this->advancedSystemUserDao;
    }

//    protected function getLoggedInUserDetails() {
//        $userDetails['userType'] = 'ESS';
//
//        /* Value 0 is assigned for default admin */
//        $userDetails['loggedUserId'] = (empty($_SESSION['empNumber'])) ? 0 : $_SESSION['empNumber'];
//        $userDetails['empId'] = (empty($_SESSION['empID'])) ? 0 : $_SESSION['empID'];
//
//        if ($_SESSION['isSupervisor']) {
//            $userDetails['userType'] = 'Supervisor';
//        }
//
//        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 'Yes') {
//            $userDetails['userType'] = 'Admin';
//        }
//
//        return $userDetails;
//    }

    /**
     * 
     * @return DashboardPanelsService
     */
    protected function getDashboardPanelsService() {
        if (is_null($this->dashboardPanelsService)) {
            $this->dashboardPanelsService = new DashboardPanelsService();
        }
        return $this->dashboardPanelsService;
    }

    /**
     * check if one of specified module is enabled.
     * @param string $moduleNames comma seperated modules
     * @return boolean
     */
    public function isModuleEnable($moduleNames) {
        $modules = explode(',', $moduleNames);
        $enable = false;
        // if one of defined modules is enable return true
        foreach ($modules as $module) {
            $module = trim($module);
            $enable = $this->getDashboardPanelsService()->isModuleEnable($module);
            if ($enable) {
                break;
            }
        }
        return $enable;
    }
}
