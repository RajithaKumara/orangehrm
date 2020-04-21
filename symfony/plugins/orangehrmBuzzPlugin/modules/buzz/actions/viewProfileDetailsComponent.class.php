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
            $this->workEmail = $this->employee->getEmpWorkEmail();
            $this->workTel = $this->employee->getEmpWorkTelephone();
            $this->isTerminated = $this->employee->getState();
            $this->locations = $this->employee->getLocationAsString();
        } else {
            $this->fullName = 'Admin';
            $this->firstName = 'Admin';
        }
    }

}
