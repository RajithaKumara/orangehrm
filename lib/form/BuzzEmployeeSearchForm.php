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
 * Description of EmployeeSearchForm
 *
 * @author dewmal
 */
class BuzzEmployeeSearchForm extends BaseForm{
    
    public function configure() {

        $widgets['emp_name'] = new ohrmWidgetEmployeeNameAutoFill(array('jsonList' => $this->getEmployeeListAsJson()), array('class' => 'formInputText'));
        $this->setWidgets($widgets);
        $this->setvalidators(array(
            'emp_name' => new ohrmValidatorEmployeeNameAutoFill()
        ));

       

       

        $this->getWidgetSchema()->setNameFormat('searchChatter[%s]');
        $this->getWidgetSchema()->setLabels($this->getFormLabels());
    }

    protected function getFormLabels() {
        $labels = array(
            'emp_name' => __('Name')
            
        );

        return $labels;
    }

    public function getStylesheets() {
        $styleSheets = parent::getStylesheets();
        $styleSheets[plugin_web_path('orangehrmCorporateDirectoryPlugin', 'css/viewDirectorySuccess.css')] = 'all';
        return $styleSheets;
    }

    

   

    
    public function getEmployeeListAsJson() {
        $jsonArray = array();
        $employeeService = new EmployeeService();
        $employeeService->setEmployeeDao(new EmployeeDao());
        $employeeList = $employeeService->getEmployeeList();

        $jsonArray[] = array('name' => __('All'), 'id' => '');
        foreach ($employeeList as $employee) {
            $name = $employee->getFirstName() . " " . $employee->getMiddleName();
            $name = trim(trim($name) . " " . $employee->getLastName());
            if ($employee->getTerminationId()) {
                $name = $name . ' (' . __('Past Employee') . ')';
            }
                $jsonArray[] = array('name' => $name, 'id' => $employee->getEmpNumber());
            }
        
        $jsonString = json_encode($jsonArray);

        return $jsonString;
    }
}
