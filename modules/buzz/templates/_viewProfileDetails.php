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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewProfileSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewprofileSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewProfileDetailsComponent'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewProfileDetailsComponent'));
?>

<div class="rightBarBodyAllProf">
    <div id="profile-img-container">
        <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $empNum); ?>" border="0" id="empPicPof" 
             width="105%"  style=""/>
    </div>
</div>
<div class="rightBarBodyAllProf">
    <div id="flipPersonal"><?php echo $fullName; ?>
        <img  id="lessDetails" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>"  height="30px" width="30px"/>

        <img id="moreDetails" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" height="30px" width="30px"/>

        <div id="employeeStatus">
            <?php
            if ($isTerminated == "TERMINATED") {
                echo "(" . __("Past Employee") . ")";
            }
            ?>
        </div>
    </div>
    <div id="panelPersonal">

        <div class="inlineBlock">
            <div id="panelfirst"><?php echo __("Location"); ?></div>
            <div id="panelsecond"> 
                <?php
                echo $locations;
                ?>
            </div>
        </div>

        <div class="inlineBlock">
            <div id="panelfirst"><?php echo __("Work Email"); ?></div>
            <div id="panelsecond"> 
                <?php
                if (sizeof($workEmail) > 0) {
                    if (sizeof($workEmail > 16)) {
                        $email = explode("@", $workEmail);
                    }
                    echo $email[0] . "@ " . $email[1];
                }
                ?>
            </div>
        </div>

        <div class="inlineBlock">
            <div id="panelfirst"><?php echo __("Work Telephone"); ?></div>
            <div id="panelsecond"> <?php echo $workTel; ?></div>
        </div>

        <div class="inlineBlock">
            <div id="panelfirst"><?php echo __("Job Title"); ?></div>
            <div id="panelsecond"> <?php echo $jobtitle; ?></div>
        </div>
    </div>
</div>
<div class="rightBarBodyAllProf">
    <div id="flipStat"><?php echo strtoupper($firstName) . '\'S ' . __('STATISTICS'); ?>
        <img  id="lessStat" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>"  height="30px" width="30px"/>

        <img id="moreStat" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>"  height="30px" width="30px"/>

    </div>
    <div id="panelStat">

        <div id="statisticsComponent">
            <?php include_component('buzz', 'viewStatistics', array('profileUserId' => $empNum)); ?>
        </div>

    </div>
</div>


