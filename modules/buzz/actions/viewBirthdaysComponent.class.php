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
 * Description of viewBirthdaysComponent
 *
 * @author aruna
 */
class viewBirthdaysComponent extends sfComponent {

    protected $buzzService;

    public function execute($request) {
        $this->setBuzzService(new BuzzService());

        $employeeBirthdaysForNext30Days = $this->getBuzzService()->getEmployeesHavingBdaysBetweenTwoDates(date("Y-m-d"), date('Y-m-d',strtotime("+30 days")));

        $employeeBirthdaysForNextYearFirstMonth = array();

        if (date("m") == 12) {
            $employeeBirthdaysForNextYearFirstMonth = $this->getBuzzService()->getEmployeesHavingBdaysOnNextYear(date("Y-m-d"));
        }

        $this->employeeList = array_merge($employeeBirthdaysForNext30Days, $employeeBirthdaysForNextYearFirstMonth);

        $this->employeeService = new EmployeeService();
        $this->employeesHavingBirthday = array();
        foreach ($this->employeeList as $employee) {
            array_push($this->employeesHavingBirthday, $this->employeeService->getEmployee($employee['emp_number']));
        }
    }

    protected function setBuzzService(BuzzService $buzzService) {
            $this->buzzService = $buzzService;

    }

    protected function getBuzzService() {
        if (!($this->buzzService instanceof BuzzService)) {
            $this->buzzService = new BuzzService() ;
        }
        return $this->buzzService;
    }
}
