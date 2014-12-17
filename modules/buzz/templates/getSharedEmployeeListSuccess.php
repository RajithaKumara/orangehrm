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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/getSharedEmployeeListSuccess'));
?>

<div class="" id='<?php echo 'postlikehidebody_' . $id ?>'>
    <div class="empListAllBlock">
        <div class="empListBlock" >
            <?php foreach ($sharedEmpList as $employee) { ?>
                <div id="employeeView">
                    <?php if ($employee->getEmpNumber() != "") { ?>
                        <div id="empFirstRaw">
                            <div id="empProfilePicContainer">
                                <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employee->getEmpNumber()); ?>" border="0" id="empPic" height="60" width="60"/>
                            </div>
                            <div id="employeeUserName">
                                <div id="empname"  >
                                    <?php echo $employee->getFirstName() . " " . $employee->getLastName(); ?>                 

                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div id="empFirstRaw">
                            <div id="empProfilePicContainer">
                                <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber="); ?>" border="0" id="empPic" height="60" width="60"/>
                            </div>
                            <div id="employeeUserName">
                                <div id="empname"  >
                                    <?php echo __('Admin'); ?>                 

                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
        
    </div>
</div>
