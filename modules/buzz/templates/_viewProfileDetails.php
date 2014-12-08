<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewProfileSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewprofileSuccess'));
?>
<script>
    $(document).ready(function () {
        $("#flipContact").click(function () {
            if ($("#panelContact").filter(":visible").length > 0) {
                $("#panelContact").slideUp("slow");
            } else {
                $("#panelContact").slideDown("slow");
            }

        });
        $("#flipPersonal").click(function () {
            
                $("#panelPersonal").toggle(300);;
                $("#moreDetails").toggle(300);
                $("#lessDetails").toggle(300);
                $("#panelStat").toggle(300);;
                $("#moreStat").toggle(300);
                $("#lessStat").toggle(300);
            

        });
        $("#flipJob").click(function () {
            if ($("#panelJob").filter(":visible").length > 0) {
                $("#panelJob").slideUp("slow");
            } else {
                $("#panelJob").slideDown("slow");
            }

        });
        $("#flipStat").click(function () {
            $("#panelPersonal").toggle(300);;
                $("#moreDetails").toggle(300);
                $("#lessDetails").toggle(300);
                $("#panelStat").toggle(300);;
                $("#moreStat").toggle(300);
                $("#lessStat").toggle(300);

        });
    });
</script>

<style> 
    #panelfirst
    {

        float: left;
        width: 40%;
        text-align: left;
        height: 20px;
        font-size: 16px;
        font-family: SourceSansProExtraLight;
    }
    #panelsecond
    {

        
        width: 58%;
        float: right;
        text-align: left;
        height: 20px;
        font-size: 16px;
        font-family: SourceSansProExtraLight;
    }
    #panelsecondfirst
    {
        margin-top: -10px;
        margin-left: 40%;
        width: 50%;
        text-align: left;
    }
    #panelbody
    {
        width: 100%;
        display: inline;
    }
    #panelContact,#flipContact
    {
        width: 100%;
        
        padding:5px;
        text-align:left;
        background-color: #484343;
    color: #fff;
        border:solid 1px #c3c3c3;
    }
    #flipContact:hover{
        cursor: pointer;
    }
    #panelContact
    {
        background-color: #98a09f;
        color: #fff;
        width: 100%;
        
        padding:4px;
        display: none;
    }

    #flipPersonal
    {
        background: rgb(40, 134, 49);
        background: -moz-linear-gradient(90deg, rgb(40, 134, 49) 20%, rgb(124, 202, 132) 100%);
         background: -webkit-linear-gradient( gray 20%, gray 100%);
        background: -o-linear-gradient(90deg, rgb(40, 134, 49) 20%, rgb(124, 202, 132) 100%);
        background: -ms-linear-gradient(90deg, rgb(40, 134, 49) 20%, rgb(124, 202, 132) 100%);
        background: linear-gradient( #f3f3f3, #ececec);
        color: #5d5d5d;
        font-size: 22px;
        padding: 15px;
        
        border: 2px solid #dedede;
        z-index: 99999;
        
        border-radius: 8px;
        font-family: SourceSansProExtraLight;
        margin-top: 0px;
    }
    #flipPersonal:hover{
        cursor: pointer;
    }
    #panelPersonal
    {
       // background-color: #EBEBEB;
        padding: 5px;
        
       // max-height: 100px;
        overflow-y:auto;
        margin-bottom: 0px;
        height: 130px;
        overflow-y: auto;
        overflow-x: hidden;
        
    }
    
    #panelJob,#flipJob
    {
        width: 100%;
        
        padding:5px;
        text-align:left;
        background-color: #484343;
    color: #fff;
        border:solid 1px #c3c3c3;
    }
    #flipJob:hover{
        cursor: pointer;
    }
    #panelJob
    {
        background-color: #98a09f;
    color: #fff;
        width: 80%;
        
        padding:4px;
        display: none;
    }
    
    #panelStat
    {
       padding: 5px;
        
       // max-height: 100px;
        overflow-y:auto;
        margin-bottom: 20px;
        height: 150px;
        overflow-y: auto;
        overflow-x: hidden;
        display: none;
        
    }
    #flipStat
    {
        background: rgb(40, 134, 49);
       background: -moz-linear-gradient(90deg, rgb(40, 134, 49) 20%, rgb(124, 202, 132) 100%);
       background: -webkit-linear-gradient( gray 20%, gray 100%);
       background: -o-linear-gradient(90deg, rgb(40, 134, 49) 20%, rgb(124, 202, 132) 100%);
        background: -ms-linear-gradient(90deg, rgb(40, 134, 49) 20%, rgb(124, 202, 132) 100%);
     background: linear-gradient( #f3f3f3, #ececec);
        color: #5d5d5d;
        font-size: 22px;
        padding: 15px;
        
        border: 2px solid #dedede;
        z-index: 99999;
        
        border-radius: 8px;
        font-family: SourceSansProExtraLight;
        margin-top: 0px;
    }
    #flipStat:hover{
        cursor: pointer;
    }
    
      


    .rightBarHeading:hover, .rightBarHeading:hover{
        cursor: pointer;
        background-color: #F07C00;
    }
    .rightBarBodyAllProf{
        background-color: #f6f6f6;
        border-radius: 8px;
        margin-bottom: 5px;
    }
    #empPicPof{
        border: 5px solid #FFFFFF;
      
    }
   


</style>
<div class="rightBarBodyAllProf">
    <div style="height: 260px;overflow: hidden">
    <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $empNum); ?>" border="0" id="empPicPof" 
         width="105%"  style=""/>
    </div>
</div>
    <div class="rightBarBodyAllProf">
    <div id="flipPersonal"><?php echo $fullName;?>
        <img  id="lessDetails" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px;border: 0px solid #f6f6f6;" height="30px" width="30px"/>

        <img id="moreDetails" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px;display: none" height="30px" width="30px"/>

    </div>
    <div id="panelPersonal">

        <div id="panelfirst">Gender</div>
        <div id="panelsecond"> <?php
            echo ": ".$gender;
            ?></div>

        <div id="panelfirst">Birth Day</div>
        <div id="panelsecond"> <?php
            echo ": ".$birthDay;
            ?></div>
        <div id="panelfirst">Locations</div>
        <div id="panelsecond"> <?php
            echo ": ".$locations;
            ?></div>
        
        <div id="panelfirst">Work Email</div>
        <div id="panelsecond"> <?php
            echo ": ".$workEmail;
            ?></div>
        <div id="panelfirst">Work Tel</div>
        <div id="panelsecond"> <?php
            echo ": ".$workTel;
            ?></div>
        <div id="panelfirst">Job Title</div>
        <div id="panelsecond"> <?php 
            echo ": ".$jobtitle;
            ?></div>
    </div>
    </div>
    <div class="rightBarBodyAllProf">
    <div id="flipStat"><?php echo 'YOUR STATISTICS'?>
    <img  id="lessStat" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px;border: 0px solid #f6f6f6;display: none" height="30px" width="30px"/>

        <img id="moreStat" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px;" height="30px" width="30px"/>

    </div>
    <div id="panelStat">

         <div id="statisticsComponent">
            <?php include_component('buzz', 'viewStatistics', array('loggedInUserId' => $logedInUser)); ?>
        </div>
        
    </div>
    </div>


