<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of viewProfileDetailsComponent
 *
 * @author dewmal
 */
class viewProfileDetailsComponent extends sfComponent {

    public function execute($request) {
        $this->logedInUser = $this->logedInUserId;
        if ($this->employee) {
            $this->empNum = $this->employee->getEmpNumber();
            $this->firstName = $this->employee->getFirstName();
            $this->fullName = $this->employee->getFirstName() . " " . $this->employee->getLastName();
            $this->jobtitle = $this->employee->getJobTitleName();
            if($this->employee->getEmpBirthday()!=null){
            $this->birthDay = date('M-d', strtotime($this->employee->getEmpBirthday()));
            }
            $this->gender = $this->employee->getGenderAsString();
            $this->workEmail = $this->employee->getEmpWorkEmail();
            $this->workTel = $this->employee->getEmpWorkTelephone();

            $this->locations = $this->employee->getLocationAsString();
        } else {
            $this->fullName = 'Admin';
            $this->firstName = 'Admin';
        }
    }

//put your code here
}
