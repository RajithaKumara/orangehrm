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
    #annivProfilePicContainer{
        width: 53px;
        height: 53px;
        overflow: hidden;
    }

    #anniversaryPost{
        background-color: #6B6B6B;
        margin-bottom: 10px;
    }

    #annivProfilePicContainer img{
        width: 100%;
        position:relative;
        top:0;
        bottom:0;
        margin:auto;
    }

    #annivProfilePicContainer{
        float: left;
        border: 2px solid white;
        vertical-align: middle;
        position:relative;
        background-color: #EBEBEB;
        margin-right: 8px;
        border-radius: 5px;
    }

    #anniversaryUserName{
        float: left;
        font-size: 20px;
    }

    #anniversaryText{
        color: #9c9c9c;
        background-color: #EBEBEB;
        font-size: 11px;
        //font-style: italic;
        //float: left;
        position: relative;
        width: 100%;
        margin-top: -25px;
        //text-align: left;
        //width: 90px;
    }

    #annivDate{
        font-size: 11px;
        font-style: italic;
    }

    #annivPicAndNameContainer div{
        /*float: left;*/
    }

    #anniversaryPost{
        height: 70px;
        //border: 2px solid white;
        border-radius: 5px;
        padding: 4px;
        background-color: #e3e3e3;
    }

    #upcomingAnnivMonth{
        //background-color: #EBEBEB;
        color: #6B6B6B;
        font-size: 16px;
        padding: 10px;
    }

    #upcomingAnnivList{
        padding: 5px;

        // max-height: 100px;
        overflow-y:auto;
        margin-bottom: 20px;
        height: 250px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    #yearsBox{
        background-color: #9c9c9c;
        height: 15px;
        color: white;
        float: left;
        width: 55px;
        padding: 4px;
        border-radius: 0 0 0 8px;
        text-align: center;
        font-size: 13px;
    }

    #joinedDate{
        float:left;
        padding: 6px;
        background-color: #cfcfcf;
        width: 73.6%;
        border-radius: 0 0 8px;
    }

</style>
<div class ="rightBarBodyAll">
    <div class="rightBarHeading" id="rightBarHeadingAnniv"><?php echo 'UPCOMING ANNIVERSARIES' ?> 
        <img id="moreAniversary" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" style="float: right;margin-top: -3px;margin-right: -10px" height="30px" width="30px"/>
        <img id="lessAniversary" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" style="float: right;margin-top: -3px;margin-right: -10px;display: none" height="30px" width="30px"/>

    </div>
    <div class ="rightBarBody">
        <div class="toggling" hidden="true" id="upcomingAnnivMonth"><?php echo date('M Y'); ?></div>
        <ul class="toggling" hidden="true" id="upcomingAnnivList">    
            <?php if(count($employeeList)==0){?>
         <li id="anniversaryPost">
            <div id="annivPicAndNameContainer">
                
                <div id="anniversaryUserName">
                    <a href="#" class="name" id="name2">
                        <?php echo _("No Anniversaries For This Month"); ?>
                    </a>
                </div>        
            </div>
            
            
        </li>
    <?php } ?>
            <?php foreach ($anniversaryEmpList as $employee) { ?>
                <li id="anniversaryPost">
                    <div id="annivPicAndNameContainer">
                        <div id="annivProfilePicContainer">
                            <img alt="<?php echo __("Employee Photo"); ?>" 
                                 src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employee['emp_number']); ?>" border="0" id="empPic"/>
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

                </li>
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

                <?php
            }
            ?>
        </ul>
    </div>
</div>
<script type="text/javascript">
    $("#rightBarHeadingAnniv").live('click', function () {
        $("#upcomingAnnivMonth").toggle(300);
        $("#upcomingAnnivList").toggle(300);
        $("#upcomingBdaysMonth").hide(300);
        $("#upcomingBdaysList").hide(300);
        $("#mc_componentContainer").hide(300);
        $("#ml_componentContainer").hide(300);
        $("#moreAniversary").toggle();
        $("#lessAniversary").toggle();
        $("#moreBirthdays").show();
        $("#lessBirthdays").hide();
        $("#moreCommentLiked").show();
        $("#lessCommentLiked").hide();
        $("#morePostLiked").show();
        $("#lessPostLiked").hide();
    });
</script>