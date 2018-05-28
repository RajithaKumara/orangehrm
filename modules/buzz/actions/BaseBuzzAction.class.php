<?php

/**
 * OrangeHRM Enterprise is a closed sourced comprehensive Human Resource Management (HRM)
 * System that captures all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM Inc is the owner of the patent, copyright, trade secrets, trademarks and any
 * other intellectual property rights which subsist in the Licensed Materials. OrangeHRM Inc
 * is the owner of the media / downloaded OrangeHRM Enterprise software files on which the
 * Licensed Materials are received. Title to the Licensed Materials and media shall remain
 * vested in OrangeHRM Inc. For the avoidance of doubt title and all intellectual property
 * rights to any design, new software, new protocol, new interface, enhancement, update,
 * derivative works, revised screen text or any other items that OrangeHRM Inc creates for
 * Customer shall remain vested in OrangeHRM Inc. Any rights not expressly granted herein are
 * reserved to OrangeHRM Inc.
 *
 * Please refer http://www.orangehrm.com/Files/OrangeHRM_Commercial_License.pdf for the license which includes terms and conditions on using this software.
 *
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
