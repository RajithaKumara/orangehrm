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
 * Description of loggedInUserDetailsComponent
 *
 * @author aruna
 */
class loggedInUserDetailsComponent extends sfComponent {

    private $employeeService;
    protected $buzzCookieService;

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
     * Get EmployeeService
     * @returns EmployeeService
     */
    public function getEmployeeService() {
        if (is_null($this->employeeService)) {
            $this->employeeService = new EmployeeService();
            $this->employeeService->setEmployeeDao(new EmployeeDao());
        }
        return $this->employeeService;
    }

    /**
     * get logged In emplyee number from cookie service
     * @return Int
     */
    public function getLogedInEmployeeNumber() {
        $employeeNumber=$this->getBuzzCookieService()->getEmployeeNumber();
        if(UserRoleManagerFactory::getUserRoleManager()->getUser() != null){
            $employeeNumber = $this->getUser()->getAttribute('auth.empNumber');
        }
        return $employeeNumber;
    }

    public function execute($request) {

        $this->empNumber = $this->getLogedInEmployeeNumber();

        $this->employee = $this->getEmployeeService()->getEmployee($this->empNumber);
        if ($this->employee) {
            $this->name = $this->employee->getFirstAndMiddleName();
            $this->jobtitle = ' ' . $this->employee->getJobTitleName();
        } else {
            $this->name = 'Admin';
            $this->jobtitle = '  ';
        }
    }

}
