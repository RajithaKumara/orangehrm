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
    <div class="rightBarHeading" id="rightBarHeadingAnniv"><?php echo 'UPCOMING ANNIVERSARIES' ?> 
        <img id="moreAniversary" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" 
             height="30px" width="30px"/>
        <img id="lessAniversary" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" 
             height="30px" width="30px"/>
    </div>
    <div class ="rightBarBody">
        <div class="toggling" hidden="true" id="upcomingAnnivMonth"><?php echo date('M Y'); ?></div>
        <ul class="toggling" hidden="true" id="upcomingAnnivList">    
            <?php if (count($anniversaryEmpList) == 0) { ?>
                <li id="anniversaryPostNull">
                    <div id="anniversaryUserName">
                        <a href="#" class="name" id="name2">
                            <?php echo _("No Anniversaries For This Month"); ?>
                        </a>
                    </div>
                </li>
            <?php } ?>
            <?php foreach ($anniversaryEmpList as $employee) { ?>
                <li id="anniversaryPost">
                    <div id="annivPicAndNameContainer">
                        <div id="annivProfilePicContainer">
                            <img alt="<?php echo __("Employee Photo"); ?>" 
                                 src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employee['emp_number']); ?>" id="empPic"/>
                        </div>  
                        <div id="anniversaryUserName">
                            <a href="#" class="name">
                                <?php echo $employee['emp_firstname'] . " " . $employee['emp_lastname']; ?>
                            </a>
                        </div>        
                    </div>
                    <br>
                    <br>
                    <div id="annivDate">
                        <?php echo date('F d', strtotime($employee['joined_date'])); ?>
                    </div>
                    <div id="anniversaryText"><?php
                        $years = (date('Y') - (date('Y', strtotime($employee['joined_date']))));
                        ?><div id="yearsBox" ><?php
                        if ($years > 1) {
                            echo __($years . ' years');
                        } else {
                            echo __($years . ' year');
                        }
                        ?>
                        </div>
                        <div id="joinedDate">
                            <?php echo "Joined Date : " . date('Y-M-d', strtotime($employee['joined_date'])); ?>
                        </div>
                    </div>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>
</div>