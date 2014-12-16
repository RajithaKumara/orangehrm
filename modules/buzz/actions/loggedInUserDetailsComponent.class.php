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

    protected $buzzService;
      private $employeeService;

    /**
     * Get EmployeeService
     * @returns EmployeeService
     */
    public function getEmployeeService() {
        if(is_null($this->employeeService)) {
            $this->employeeService = new EmployeeService();
            $this->employeeService->setEmployeeDao(new EmployeeDao());
        }
        return $this->employeeService;
    }

    public function execute($request) {
        $this->buzzService = new BuzzService();
        $this->empNumber = $this->getUserId();


        $this->employee = $this->getEmployeeService()->getEmployee($this->empNumber);
        if ($this->employee) {
            $this->name = $this->employee->getFirstAndMiddleName();
            $this->jobtitle = ' '.$this->employee->getJobTitleName();
        } else {
            $this->name = 'Admin';
            $this->jobtitle='  ';
        }
    }

    public function getUserId(){
        
        $cookie_name='buzzCookie';
        
        if(UserRoleManagerFactory::getUserRoleManager()->getUser()!=null){
           
            
            $cookie_valuve = $this->getUser()->getEmployeeNumber();
            if($cookie_valuve==""){
                 setcookie($cookie_name, 'Admin', time() + 3600 * 24 * 30, "/");
            }else{
                setcookie($cookie_name,$cookie_valuve , time() + 3600 * 24 * 30, "/"); 
            }
            
           
              
            return  $cookie_valuve;
        }elseif (isset($_COOKIE[$cookie_name]) ){
            if($_COOKIE[$cookie_name]=='Admin'){
                return '';
            }
               
            return $_COOKIE[$cookie_name];
        }else{
            throw new Exception('User Didnot Have');
        }
    }

}
