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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/mostLikedShares'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/mostLikedShares'));
?>
<div class ="rightBarBodyAll">
    <div class="rightBarHeading" id="rightBarHeadingMl"><?php echo __("MOST LIKED POSTS"); ?>
        <img id="morePostLiked" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" 
            height="30px" width="30px"/>
        <img id="lessPostLiked" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" 
            height="30px" width="30px"/>
    </div>
    <div id="ml_componentContainer" hidden="true">
        <?php foreach ($result_ml_shares as $result) { ?>

            <?php include_component('buzz', 'viewPostPreview', array('post' => $result)); ?>

        <?php } ?>
    </div>
</div>
<div class ="rightBarBodyAll">
    <div class="rightBarHeading" id="rightBarHeadingMc"><?php echo __("MOST SHARED POSTS"); ?>
        <img id="moreCommentLiked" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/more2.png"); ?>" 
            height="30px" width="30px"/>
        <img id="lessCommentLiked" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/less2.png"); ?>" 
            height="30px" width="30px"/>
    </div>
    <div id="mc_componentContainer" hidden="true">
        <?php foreach ($result_mc_shares as $resultMc) { ?>
        
            <?php include_component('buzz', 'viewPostPreview', array('post' => $resultMc)); ?>
        
        <?php } ?>
    </div>
</div>
