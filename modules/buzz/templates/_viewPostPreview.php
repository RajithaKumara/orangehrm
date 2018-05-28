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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewPostPreviewComponent'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess'));
?>

<!-- pop up-->
<div class="modal hide modalPopUP"  id='<?php echo 'shareViewMoreMod3_' . $postId ?>'>
    <div class="modal-body modalPopUP-body" >
        <div class="hideModalPopUp" id='<?php echo 'shareViewMoreMod3_' . $postId ?>'
             ><img class="hideModalPopUp" id='<?php echo 'shareViewMoreMod3_' . $postId ?>' 
              src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
              /></div>

        <div class="shareView" id='<?php echo 'shareViewContent3_' . $postId ?>'>
        </div>
    </div>
</div>
<div class="likeRaw" id="likeRaw_<?php echo $postId; ?>">
    <li class="previewPost" id=<?php echo "post_" . $postId; ?>>
        <div class="picAndNameContainer" id="picAndNameContainer_<?php echo $postId; ?>">
            <div id="profilePicContainer">
                <img class="profPic" id="profPic_<?php echo $postId; ?>" alt="<?php echo __("Employee Photo"); ?>"src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employeeID); ?>" border="0" id="empPic"/></a>
            </div>  
            <div class="birthdayUserName" id="birthdayUserName_<?php echo $postId; ?>">
                <?php if ($postSharerDeleted) { ?>
                    <?php echo $postEmployeeName; ?>
                <?php } else { ?>
                    <?php echo $employee['emp_firstname'] . " " . $postEmployeeName; ?>
                <?php } ?>
            </div>  <br> 
            <?php
            $photos = $sf_data->getRaw('originalPost')->getPhotos();
            $imgCount = 1;
            if (count($photos) == 1) {
                ?>
                <div class="photoPreviewOne">
                    <img id="<?php echo $imgCount . "_" . $postId; ?>" class="" src="data:image/jpeg;base64,<?php echo base64_encode($photos[0]->getPhoto()); ?>"/>
                </div>
                <?php
            } else if (count($photos) > 1) {

                foreach ($photos as $photo) {
                    ?>
                    <img id="<?php echo $imgCount . "_" . $postId; ?>" class="" width="100px" src="data:image/jpeg;base64,<?php echo base64_encode($photo->getPhoto()); ?>"/>
                    <?php
                    break;
                }
            }
            ?>
            <div class="post_prev_content" id="post_prev_content_<?php echo $postId; ?>">
                <div id="postBodySecondRow_<?php echo $postId; ?>" class="previewSecondRow">
                    <div class="postContent" id='<?php echo 'postContent_' . $postId ?>'>
                        <?php echo BuzzTextParserService::parseText($postContent); ?>
                        <?php
                        if ($postType == '1') {
                            ?>
                            <!--SUB POST START-->
                            <div class="sharedPostBodyPreview" id="sharedPostBodyPreview_<?php echo $postId; ?>">

                                <div id="postBodyFirstRow">
                                    <div id="postFirstRowColumnOne">
                                        <a href="<?php echo url_for("buzz/viewProfile?empNumber=" . $originalPostEmpNumber); ?>"><img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $originalPostEmpNumber); ?>" border="0" id="empPic" height="40" width="30"/>
                                    </div>
                                    <div id="postFirstRowColumnTwo">
                                        <div id="postEmployeeName" >
                                            <?php if ($originalPostSharerDeleted) { ?>
                                                <?php echo $originalPostSharerName; ?>
                                            <?php } else { ?>
                                                <a class="originalPostView" href="<?php echo url_for("buzz/viewProfile?empNumber=" . $originalPostEmpNumber); ?>" id="<?php echo 'postView_' . $postId . '_' . $originalPostId ?>">
                                                    <?php echo $originalPostSharerName; ?>
                                                </a>
                                            <?php } ?>
                                        </div>                       
                                    </div>
                                </div>

                                <div id="postBodySecondRow">
                                    <div id="postContent">
                                        <?php echo BuzzTextParserService::parseText($originalPostContent); ?>
                                    </div>
                                </div>
                            </div>
                            <!--SUB POST END-->

                            <?php
                        } else {
                            echo BuzzTextParserService::parseText($originalPostContent);
                        }
                        ?>

                    </div>
                </div>
            </div>

        </div>
    </li>
</div>

