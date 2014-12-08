<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
