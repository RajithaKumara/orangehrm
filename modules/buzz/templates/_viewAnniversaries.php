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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewAnniversaries'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewAnniversaries'));
?>
<div class ="rightBarBodyAll">
    <div class="rightBarHeading" id="rightBarHeadingAnniv"><?php echo __('UPCOMING ANNIVERSARIES'); ?> 
        <img id="moreAniversary" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" 
             height="30px" width="30px"/>
        <img id="lessAniversary" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" 
             height="30px" width="30px"/>
    </div>
    <div class ="rightBarBody">
        <div class="toggling" style="display:none;" id="upcomingAnnivMonth"><?php echo __(date('M')).' '.date('Y'); ?></div>
        <ul class="toggling" style="display:none;" id="upcomingAnnivList">    
            <?php if (count($anniversaryEmpList) == 0) { ?>
                <li id="anniversaryPostNull">
                    <div id="anniversaryUserName">
                        <a href="#" class="name" id="name2">
                            <?php echo __("No Anniversaries For This Month"); ?>
                        </a>
                    </div>
                </li>
            <?php } ?>
            <?php
            foreach ($anniversaryEmpList as $employee) {
                if ($employee->getTerminationId() == NULL) {
                    ?>
                    <li id="anniversaryPost">
                        <div id="annivPicAndNameContainer">
                            <div id="annivProfilePicContainer">
                                <img alt="<?php echo __("Employee Photo"); ?>" 
                                     src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employee->getEmpNumber()); ?>" id="empPic"/>
                            </div>  
                            <?php $employeeFirstAndLastNames = $employee->getFirstName() . " " . $employee->getLastName(); ?>
                            <div id="anniversaryUserName" title="<?php echo $employeeFirstAndLastNames; ?>">
                                <a href= '<?php echo url_for("buzz/viewProfile?empNumber=" . $employee->getEmpNumber()); ?>' class="name">
                                    <?php
                                    if (strlen($employeeFirstAndLastNames) > 18) {
                                        echo substr($employeeFirstAndLastNames, 0, 18) . '...';
                                    } else {
                                        echo $employeeFirstAndLastNames;
                                    }
                                    ?>
                                </a>
                            </div>        
                        </div>
                        <br>
                        <br>
                        <?php $jobTitle = $employee->getJobTitleName(); ?>
                        <div id="birthdayUserJobTitle" title="<?php echo $jobTitle; ?>">
                            <?php
                            if (strlen($jobTitle) > 25) {
                                echo substr($jobTitle, 0, 25) . '...';
                            }else{
                                echo $jobTitle;
                            }
                            ?>
                        </div>
                        <div id="annivDate">
                            <?php echo __(date('F', strtotime($employee->getJoinedDate()))).' '. date('d', strtotime($employee->getJoinedDate())); ?>
                        </div>
                        <div id="anniversaryText"><?php
                            $years = (date('Y') - (date('Y', strtotime($employee->getJoinedDate()))));
                            ?><div id="yearsBox" ><?php
                            if ($years > 1) {
                                echo $years.' '.__('years');
                            } else {
                                echo $years.' '.__('year');
                            }
                            ?>
                            </div>
                            <div id="joinedDate">
                                <?php echo __("Joined Date") . " : " . date('Y', strtotime($employee->getJoinedDate())).'-'.__(date('M', strtotime($employee->getJoinedDate()))).'-'.date('d', strtotime($employee->getJoinedDate())); ?>
                            </div>
                        </div>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
    </div>
</div>