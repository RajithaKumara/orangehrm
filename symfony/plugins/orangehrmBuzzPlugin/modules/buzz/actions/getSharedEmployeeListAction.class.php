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
 * Description of getSharedEmployeeListAction
 *
 * @author aruna
 */
class getSharedEmployeeListAction extends BaseBuzzAction {

    /**
     * @param sfForm $form
     * @return
     */
    protected function setForm(sfForm $form) {
        if (is_null($this->form)) {
            $this->form = $form;
        }
    }

    public function execute($request) {
        try {

            $this->setForm(new LikedOrSharedEmployeeForm());

            if ($request->isMethod('post')) {
                $this->form->bind($request->getParameter($this->form->getName()));
                if ($this->form->isValid()) {
                    $formValues = $this->form->getValues();
                    $this->id = $formValues['id'];
                    $this->event = $formValues['event'];
                    $this->loggedInUser = $this->getLogedInEmployeeNumber();
                    $this->buzzService = $this->getBuzzService();
                    $this->loggedInEmployeeId = $this->getLogedInEmployeeNumber();
                    $this->post = $this->buzzService->getShareById($this->id)->getPostShared();
                    $this->sharedEmployeeDetailsList = $this->getPostSharedEmployeeNameList($this->post);

                    if ($this->event === "click") {
                        
                    } else {
                        foreach ($this->sharedEmpNameList as $value) {
                            echo $value;
                            echo "\n";
                        }
                        sfView::NONE;
                        die;
                    }
                }
            }
        } catch (Exception $ex) {
            $this->redirect('auth/login');
        }
    }

    /**
     * get post shared employee list
     * @param type $post
     * @return array EployeeList
     */
    private function getPostSharedEmployeeList($post) {
        $sharedEmployeeList = array();
        $isAdminShare = false;
        foreach ($post->getShare() as $share) {
            if ($share->getEmployeeNumber() == null) {
                $isAdminShare = true;
            } else {
                $employee = $share->getEmployeePostShared();
                array_push($sharedEmployeeList, $employee);
            }
        }
//        $sharedUniqueEmployeeList=array_unique($sharedEmployeeList);
        $sharedUniqueEmployeeList = $this->generateUniqueEmployeeList($sharedEmployeeList);
        if ($isAdminShare) {
            array_push($sharedUniqueEmployeeList, new Employee());
        }
        return $sharedUniqueEmployeeList;
    }

    /**
     * Returns a unique list of employees from the $employeeList
     * @param array $employeeList
     * @return array
     */
    private function generateUniqueEmployeeList($employeeList) {
        $uniqueEmployeeList = array();
        foreach ($employeeList as $employee) {
            $id = $employee->getEmpNumber();
            isset($uniqueEmployeeList[$id]) or $uniqueEmployeeList[$id] = $employee;
        }
        return $uniqueEmployeeList;
    }

    /**
     * get post shared employee name list;
     * @param type $post
     * @return array employee name list
     */
    private function getPostSharedEmployeeNameList($post) {
        $sharedEmployeeDetailsList = array();
        foreach ($post->getShare() as $share) {

            if ($share->getEmployeeNumber() == null) {
                $empName = 'Admin';
            } else {
                $employee = $share->getEmployeePostShared();
                $empName = $employee->getFirstAndLastNames();
                $jobTitle = $employee->getJobTitleName();

                if ($empName == ' ' && $share->getEmployeeName() != null) {
                    $empName = $share->getEmployeeName() . ' (' . __(self::LABEL_EMPLOYEE_DELETED) . ')';
                    $employeeDeleted = true;
                }
            }

            $employeeDetails = array(self::EMP_DELETED => $employeeDeleted, self::EMP_NUMBER => $share->getEmployeeNumber(), self::EMP_NAME => $empName, self::EMP_JOB_TITLE => $jobTitle);
            $sharedEmployeeDetailsList[$share->getEmployeeNumber()] = $employeeDetails;
        }
        return $sharedEmployeeDetailsList;
    }

}
