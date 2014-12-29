<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewProfileSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewprofileSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewProfileDetailsComponent'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewProfileDetailsComponent'));
?>

<div class="rightBarBodyAllProf">
    <div style="height: 260px;overflow: hidden">
        <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $empNum); ?>" border="0" id="empPicPof" 
             width="105%"  style=""/>
    </div>
</div>
<div class="rightBarBodyAllProf">
    <div id="flipPersonal"><?php echo $fullName; ?>
        <img  id="lessDetails" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>"  height="30px" width="30px"/>

        <img id="moreDetails" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" height="30px" width="30px"/>

    </div>
    <div id="panelPersonal">

        <div class="inlineBlock">
            <div id="panelfirst">Gender</div>
            <div id="panelsecond"> <?php echo $gender; ?></div>
        </div>

        <div class="inlineBlock">
            <div id="panelfirst">Birth Day</div>
            <div id="panelsecond"> <?php echo $birthDay; ?></div>
        </div>

        <div class="inlineBlock">
            <div id="panelfirst">Locations</div>
            <div id="panelsecond"> <?php echo $locations; ?></div>
        </div>

        <div class="inlineBlock">
            <div id="panelfirst">Work Email</div>
            <div id="panelsecond"> <?php echo $workEmail; ?></div>
        </div>

        <div class="inlineBlock">
            <div id="panelfirst">Work Tel.</div>
            <div id="panelsecond"> <?php echo $workTel; ?></div>
        </div>

        <div class="inlineBlock">
            <div id="panelfirst">Job Title</div>
            <div id="panelsecond"> <?php echo $jobtitle; ?></div>
        </div>
    </div>
</div>
<div class="rightBarBodyAllProf">
    <div id="flipStat"><?php echo strtoupper($firstName) . '\'S STATISTICS' ?>
        <img  id="lessStat" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>"  height="30px" width="30px"/>

        <img id="moreStat" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>"  height="30px" width="30px"/>

    </div>
    <div id="panelStat">

        <div id="statisticsComponent">
            <?php include_component('buzz', 'viewStatistics', array('profileUserId' => $empNum)); ?>
        </div>

    </div>
</div>


