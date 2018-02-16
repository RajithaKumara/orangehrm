<?php

/*
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

class viewLikedEmployeesAction extends BaseBuzzAction {

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
                    $this->actions = $formValues['type'];
                    $this->loggedInUser = $this->getLogedInEmployeeNumber();
                    $this->error = 'no';
                    if ($this->actions == 'post') {
                        $share = $this->getBuzzService()->getShareById($this->id);
                        $likes = $share->getLike();
                        $this->employeeList = $this->extractEmployeeInformation($likes);
                    } else {
                        $comment = $this->getBuzzService()->getCommentById($this->id);
                        $likes = $comment->getLike();
                        $this->employeeList = $this->extractEmployeeInformation($likes, false);
                    }
                }
            }
        } catch (Exception $ex) {
            $this->error = 'yes';
        }
    }

    public function extractEmployeeInformation($likes, $isPost = true) {
        $likedEmployeeDetailsList = array();

        foreach ($likes as $like) {
            if ($like->getEmployeeNumber() == null) {
                $empName = 'Admin';
            } else {
                if ($isPost) {
                    $employee = $like->getEmployeeLike();
                } else {
                    $employee = $like->getEmployeeLike()->getFirst();
                }
                if($employee instanceof Employee) {
                    $empName = $employee->getFirstAndLastNames();
                    $jobTitle = $employee->getJobTitleName();
                } else {
                    $empName = $like->getEmployeeName() . ' (' . __(self::LABEL_EMPLOYEE_DELETED) . ')';
                    $employeeDeleted = true;
                }


                if ($empName == ' ' && $like->getEmployeeName() != null) {
                    $empName = $like->getEmployeeName() . ' (' . __(self::LABEL_EMPLOYEE_DELETED) . ')';
                    $employeeDeleted = true;
                }
            }
            $employeeDetails = array(self::EMP_DELETED => $employeeDeleted, self::EMP_NUMBER => $like->getEmployeeNumber(), self::EMP_NAME => $empName, self::EMP_JOB_TITLE => $jobTitle);
            $likedEmployeeDetailsList[$like->getEmployeeNumber()] = $employeeDetails;
        }
        return $likedEmployeeDetailsList;
    }

}
