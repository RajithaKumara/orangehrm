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
?>
<style type="text/css">
    #profilePicContainer{
        width: 50px;
        height: 50px;
        /*overflow: hidden;*/
    }

    #birthdayPost{
        background-color: #6B6B6B;
        margin-bottom: 10px;
    }

    #profilePicContainer img{
        width: 100%;
        position:absolute;
        top:0;
        bottom:0;
        margin:auto;
         border-radius: 5px;
    }

    #profilePicContainer{
        float: left;
        border: 2px solid white;
        vertical-align: middle;
        position:relative;
        background-color: #EBEBEB;
        margin-right: 8px;
        border-radius: 5px;
        overflow: hidden;
    }

    #birthdayUserName{
        float: left;
        font-size: 30px;
        font-family: SourceSansProRegular;
    }

    #birthdayText{
        color: #EBEBEB;
    }

    #date{
        //color: #EBEBEB;
        //font-style: italic;
        font-family: SourceSansProExtraLight;
    }

    #picAndNameContainer div{
        /*float: left;*/
    }

    #birthdayPost{
        height: 52px;
       padding: 4px;
        border-radius: 5px;
        background-color: #e3e3e3;
    }
    #birthdayPostNull{
        height: 52px;
       padding: 4px;
        
    }
    #birthdayPostNull{
        height: 52px;
       padding: 4px;
        border-radius: 5px;
        
    }

    #upcomingBdaysMonth{
        //background-color: #EBEBEB;
        color: #6B6B6B;
        font-size: 16px;
        padding: 10px;
    }
    #name2{
        /*font-family: SourceSansProRegular;*/
        font-size: 20px;
    }

    #upcomingBdaysList{
       // background-color: #EBEBEB;
        padding: 5px;
        
       // max-height: 100px;
        overflow-y:auto;
        margin-bottom: 20px;
        height: 250px;
        overflow-y: auto;
        overflow-x: hidden;
    }

</style>
<div class ="rightBarBodyAll">
<div class="rightBarHeading" id="rightBarheadingBday"><?php echo 'UPCOMING BIRTHDAYS'?>
    <img  id="lessBirthdays" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px;border: 0px solid #f6f6f6;" height="30px" width="30px"/>

    <img id="moreBirthdays" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px;display: none" height="30px" width="30px"/>
</div>

<div class ="rightBarBody">
<div class="toggling" id="upcomingBdaysMonth"><?php echo date('M Y'); ?></div>
<ul class="toggling" id="upcomingBdaysList">    
    <?php if(count($employeeList)==0){?>
         <li id="birthdayPostNull">
           
                
                <div id="birthdayUserName">
                    <a href="#" class="name" id="name2">
                        <?php echo _("No Birthdays For This Month"); ?>
                    </a>
                </div>        
            
            
            
        </li>
    <?php } ?>
    <?php foreach ($employeeList as $employee) { ?>
        <li id="birthdayPost">
            <div id="picAndNameContainer">
                <div id="profilePicContainer">
                    <img alt="<?php echo __("Employee Photo"); ?>" 
                         src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employee['emp_number']); ?>" border="0" id="empPic"/>
                </div>  
                <div id="birthdayUserName">
                    <a href="#" class="name" id="name2">
                        <?php echo $employee['emp_firstname'] . " " . $employee['emp_lastname']; ?>
                    </a>
                </div>        
            </div>
            <br>
            <br>
            <div id="date">
                <?php
                if (date('Y-m-d') == $employee['emp_birthday']) {
                    echo __("Today is his birthday");
                } else {
                    echo date('F d', strtotime($employee['emp_birthday']));
                }
                ?>
            </div>
        </li>

        <?php
    }
    ?>
</ul>
</div>
</div>
<script type="text/javascript">
    $("#rightBarheadingBday").live('click', function () {
        $("#upcomingBdaysMonth").toggle(300);
        $("#upcomingBdaysList").toggle(300);
        $("#upcomingAnnivMonth").hide(300);
        $("#upcomingAnnivList").hide(300);
        $("#mc_componentContainer").hide(300);
        $("#ml_componentContainer").hide(300);
        $("#moreBirthdays").toggle(300);
        $("#lessBirthdays").toggle(300);
        $("#moreAniversary").show();
        $("#lessAniversary").hide();
         $("#moreCommentLiked").show();
        $("#lessCommentLiked").hide();
         $("#morePostLiked").show();
        $("#lessPostLiked").hide();
    });
</script>