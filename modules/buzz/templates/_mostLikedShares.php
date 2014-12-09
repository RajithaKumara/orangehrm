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
    #rowOne{

    }
    #componentTitleImage{
        width: 40%;
        float: left;
    }

    #componentTitleImage img{
        min-width: 70%;
    }
    #firstShare{
        width: 58%;
        float: left;
    }
    #rowTwo{

    }
    #secondShare{
        width: 49%;
        float: left;
    }
    #thirdShare{
        width: 49%;
        float: left;
    }
    #fourthShare{
        width: 49%;
        float: left;
    }

    #fifthShare{
        width: 49%;
        float: left;
    }

    #ml_componentContainer div{
        overflow: hidden;
        max-height: 130px;
        font-size: 95%;
        margin: 1px;
    }
    
    #mc_componentContainer div{
        overflow: hidden;
        max-height: 130px;
        font-size: 95%;
        margin: 1px;
    }

    #ml_componentContainer{
        padding: 5px;
        
       // max-height: 100px;
        overflow-y:auto;
        margin-bottom: 20px;
        height: 250px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    #mc_componentContainer{
        padding: 5px;
        
       // max-height: 100px;
        overflow-y:auto;
        margin-bottom: 20px;
        height: 250px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    #likeRaw{
        width: 100%;
        border-radius: 5px;
        background-color: #e3e3e3;
        height: 60px;
        overflow: hidden;
    }

    
</style>
<div class ="rightBarBodyAll">
<div class="rightBarHeading" id="rightBarHeadingMl">MOST LIKED POSTS
<img id="morePostLiked" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px" height="30px" width="30px"/>
    <img id="lessPostLiked" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px;display: none" height="30px" width="30px"/>
</div>
<div id="ml_componentContainer" hidden="true">
    <?php    foreach ($result_ml_shares as $result){ ?>
    
        <?php  include_component('buzz', 'viewPostPreview', array('post' => $result)); ?>
    
    <?php } ?>
    <div hidden="true" id="rowOne">
        <div id="componentTitleImage">
            <img height="100px" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/most-liked-post.png"); ?>">
        </div>
        <div id="firstShare" id="ml_0">
            <?php // include_component('buzz', 'viewPostPreview', array('post' => $result_ml_shares[0])); ?>
        </div>        
    </div>
    <div id="rowTwo">
        <div id="secondShare" id="ml_1">
            <?php //include_component('buzz', 'viewPostPreview', array('post' => $result_ml_shares[1])); ?>
        </div>
        <div id="thirdShare" id="ml_2">
            <?php //include_component('buzz', 'viewPostPreview', array('post' => $result_ml_shares[2])); ?>
        </div>
    </div>
    <div id="rowThree">

        <div id="fourthShare" id="ml_3">
            <?php //include_component('buzz', 'viewPostPreview', array('post' => $result_ml_shares[3])); ?>
        </div>
        <div id="fifthShare" id="ml_4">
            <?php //include_component('buzz', 'viewPostPreview', array('post' => $result_ml_shares[4])); ?>
        </div>
    </div>
</div>
</div>
<div class ="rightBarBodyAll">
<div class="rightBarHeading" id="rightBarHeadingMc">MOST SHARED POSTS
<img id="moreCommentLiked" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px" height="30px" width="30px"/>
    <img id="lessCommentLiked" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" style="float: right;margin-top: -7px;margin-right: -10px;display: none" height="30px" width="30px"/>
</div>
<div id="mc_componentContainer" hidden="true">
    <?php    foreach ($result_mc_shares as $resultMc){ ?>
    <div id="likeRaw">
        <?php  include_component('buzz', 'viewPostPreview', array('post' => $resultMc)); ?>
        
    </div>
    <?php } ?>
    <div hidden="true" id="rowOne">
        <div id="componentTitleImage">
            <img height="100px"  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/most-shared-post.png"); ?>">
        </div>
        <div id="firstShare" id="mc_0">
            <?php // include_component('buzz', 'viewPostPreview', array('post' => $result_mc_shares[0])); ?>
        </div>        
    </div>
    <div hidden="true" id="rowTwo">
        <div id="secondShare" id="mc_1">
            <?php// include_component('buzz', 'viewPostPreview', array('post' => $result_mc_shares[1])); ?>
        </div>
        <div id="thirdShare" id="mc_2">
            <?php //include_component('buzz', 'viewPostPreview', array('post' => $result_mc_shares[2])); ?>
        </div>
    </div>
    <div hidden="true" id="rowThree">

        <div id="fourthShare" id="mc_3">
            <?php //include_component('buzz', 'viewPostPreview', array('post' => $result_mc_shares[3])); ?>
        </div>
        <div id="fifthShare" id="mc_4">
            <?php// include_component('buzz', 'viewPostPreview', array('post' => $result_mc_shares[4])); ?>
        </div>
    </div>
</div>
</div>
<script type="text/javascript">
    $("#rightBarHeadingMc").live('click', function () {
        $("#upcomingAnnivMonth").hide(300);
        $("#upcomingAnnivList").hide(300);
        $("#upcomingBdaysMonth").hide(300);
        $("#upcomingBdaysList").hide(300);
        $("#mc_componentContainer").toggle(300);
        $("#ml_componentContainer").hide(300);
        
         $("#morePostLiked").show();
        $("#lessPostLiked").hide();
        $("#moreBirthdays").show();
        $("#lessBirthdays").hide();
        $("#moreCommentLiked").toggle();
        $("#lessCommentLiked").toggle();
         $("#moreAniversary").show();
        $("#lessAniversary").hide();
    });
    
    $("#rightBarHeadingMl").live('click', function () {
        $("#upcomingAnnivMonth").hide(300);
        $("#upcomingAnnivList").hide(300);
        $("#upcomingBdaysMonth").hide(300);
        $("#upcomingBdaysList").hide(300);
        $("#mc_componentContainer").hide(300);
        $("#ml_componentContainer").toggle(300);
        $("#morePostLiked").toggle();
        $("#lessPostLiked").toggle();
        $("#moreBirthdays").show();
        $("#lessBirthdays").hide();
        $("#moreCommentLiked").show();
        $("#lessCommentLiked").hide();
         $("#moreAniversary").show();
        $("#lessAniversary").hide();
    });
</script>
