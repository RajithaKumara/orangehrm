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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess_1'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess_1'));
?>
<style>
    .likeCommentnew{
        background-color: black;
        opacity: 0.8;

        width: 30px;
        margin-left: 0px;
        z-index: 999;
        position: absolute;

    } 
    .unlikeCommentnew{
        background-color: black;
        opacity: 0.8;

        width: 30px;
        margin-left: 40px;
        z-index: 999;
        position: absolute;

    }

    #commentBodyThirdRowNew{
        width: 100%;
        height: 30px;
        margin-left: 4%;
        margin-top: 0px;
        margin-bottom: -0px;
    }
    .textTopOfImageComment{
        color: white;
        font-size: 15px;
        margin-left: 17px;
        margin-top: -21px;
        z-index: 9999;
        position: absolute;

    }
</style>
<?php if ($error == 'no') { ?>
    

                <!-- start edit comment popup window-->
                <div class="modal hide" style="width: 800px;height: 700px;left: 40%;top:50%;overflow-x: hidden" id='<?php echo 'editcommenthideNew2_' . $commentId ?>'>
                    
                    <div class="modal-body" style="background-color: gray;overflow-x: hidden;overflow-y: auto">
                        <div class="hideModalPopUp" id='<?php echo 'editcommenthideNew2_' . $commentId ?>' style="top: 2px;right: 2px;position: absolute;z-index: 99999;border: 2px solid;border-radius: 250px;background-color: white">x</div>
                
                        
                        <div id="postBodySecondRow" style="border-radius: 5px;padding: 20px;">
                            <h3><?php echo __('Edit your comment'); ?></h3>
                            <form id="frmCreateComment" method="" action="" 
                                  enctype="multipart/form-data">
                                      <?php
                                      $editForm->setDefault('comment', $commentContent);
                                      echo $editForm['comment']->render(array('id' => "editcommentBoxNew2_" . $commentId,
                                          'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '2'));
                                      ?>

                            </form>

                            <div style="padding: 20px;background-color: white;margin-bottom: 20px;">
                                <input type="button" style="float: right" class="btnEditCommentNew" name="btnSaveDependent" id='<?php echo 'btnEditComment_' . $commentId ?>' value="<?php echo __("Save"); ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end edit comment pop up window-->
                <!-- start like window popup window-->
                <div class="modal hide" id='<?php echo 'commentlikehide_' . $commentId ?>'>
                    <div class="modal-header" >
                        <a class="close" data-dismiss="modal">×</a>
                        <h3><?php echo __('People who like this post'); ?></h3>
                    </div>
                    <div class="modal-body" >
                        <div class="" id='<?php echo 'commentlikehidebody_' . $commentId ?>'></div>


                    </div>
                </div>
                <!-- end like window pop up window-->
                <li id="<?php echo "commentNew_" . $commentId; ?>" style="display: <?php echo $display; ?>" class="<?php echo $commentPostId; ?>" >
                    <div id="commentBody">
                        <div id="commentRowOne">
                            <div id="commentColumnOne">
                                <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $commentEmployeeId); ?>" border="0" id="empPic"/>
                            </div>
                            <div id="commentColumnTwo">
                                <div id="commentEmployeeName">
                                    <a class="name" href="javascript:void(0);"><?php echo $commentEmployeeName; ?></a>
                                </div>
                                <div id="commentEmployeeJobTitle">
                                    <?php echo $commentEmployeeJobTitle; ?>
                                </div>
                                <div id="commentColumnTwoRowThree">
                                    <div id="commentDate">
                                        <?php echo $commentDate; ?>
                                    </div>
                                    <div id="commentTime">
                                        <?php echo $commentTime; ?>
                                    </div>
                                </div>
                            </div>
                            <div id="commentColumnThree">
                                <?php if ($commentEmployeeId == $loggedInUser) { ?>
                                    <div id="commentOptionWidget">
                                        <div class="dropdown">
                                            <a class="commentAccount" id=<?php echo 'cnew'.$commentId ?>></a>
                                            <div class="submenu" id=<?php echo 'submenucnew' . $commentId ?>>
                                                <ul class = "root">
                                                    <li ><a href = "javascript:void(0)" class="editComment" id=<?php echo 'editComment_' . $commentId ?> ><?php echo __("Edit"); ?></a></li>
                                                    <li ><a href = "javascript:void(0)" class="deleteComment" id=<?php echo 'deleteComment_' . $commentId ?>><?php echo __("Delete"); ?></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div  id="commentBodyThirdRowNew">
                                <div class="likeCommentnew"  id="<?php echo 'commentLikebody_' . $commentId ?>" style="background-color: ">
                                    <?php if ($isLikeComment == 'Unlike') {?>
                                    <a hidden="true" href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeno_' . $commentId ?>'> 
                                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                                    class="<?php echo $isLikeComment . ' commentLike'; ?>" height="20" width="20"/></a>
                                        <a href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeyes_' . $commentId ?>'> 
                                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                                    class="<?php echo $isLikeComment . ' commentLike'; ?>" height="20" width="20"/></a>
                                    <?php } else {?>
                                    <a href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeno_' . $commentId ?>'> 
                                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                                    class="<?php echo $isLikeComment . ' commentLike'; ?>" height="22" width="22"/></a>
                                        <a hidden="true" href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeyes_' . $commentId ?>'> 
                                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                                    class="<?php echo $isLikeComment . ' commentLike'; ?>" height="22" width="22"/></a>
                                    <?php } ?>
                                    
                                    <div class="textTopOfImageComment" id='<?php echo 'commentNoOfLiketext_' . $commentId ?>'><?php echo $commentNoOfLikes ?></div>
                                </div>
                                <div class="unlikeCommentnew" id='<?php echo 'commentUnLikebody_' . $commentId ?>' >
                                    <?php if ($isUnlikeComment == 'yes') {?>
                                    <a hidden="true" href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeno_' . $commentId ?>>
                                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="20" width="20"/></a>
                                    <a  href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeyes_' . $commentId ?>>
                                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="20" width="20"/></a>
                                    <?php } else {?>
                                    <a  href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeno_' . $commentId ?>>
                                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="22" width="22"/></a>
                                    <a hidden="true" href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeyes_' . $commentId ?>>
                                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="22" width="22"/></a>
                                    <?php } ?>
                                    
                                                                                                                                              
                                    <div class="textTopOfImageComment" id='<?php echo 'commentNoOfUnLiketext_' . $commentId ?>'><?php echo $commentNoOfUnLikes ?></div>
                                </div>
                            </div>
                        <div id="commentRowTwo">
                            <div class="commentContent"id='<?php echo 'commentContentNew_' . $commentId ?>'>
                                <?php echo BuzzTextParserService::parseText($commentContent); ?>
                            </div>
                        </div>

                        <div hidden="true" id="commentColumnTwo">
                            <div hidden="true" id="commentBodyThirdRowNew">
                                <div class="likeCommentnew"  id="<?php echo 'commentLikebody_' . $commentId ?>" style="background-color: <?php
                                if ($isLikeComment == 'Unlike') {
                                    echo 'orange';
                                }
                                ?>">
                                    <a href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLike_' . $commentId ?>'> <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                                                                                                                                                              class="<?php echo $isLikeComment . ' commentLike'; ?>" height="30" width="30"/></a>
                                    <div class="textTopOfImageComment" id='<?php echo 'commentLiketext_' . $commentId ?>'><?php echo $commentNoOfLikes ?></div>
                                </div>
                                <div class="unlikeCommentnew" id='<?php echo 'commentUnLikebody_' . $commentId ?>' style="background-color: <?php
                                if ($isUnlike == 'yes') {
                                    echo 'red';
                                }
                                ?>">
                                    <a href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnlike_' . $commentId ?>> <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/un-like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' 
                                                                                                                                              height="30" width="30"/></a>
                                    <div class="textTopOfImageComment" id='<?php echo 'commentUnLiketext_' . $commentId ?>'><?php echo $commentNoOfUnLikes ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
<?php } else if ($error == 'yes') { ?>

    <div id ='errorFirstRow'>
        <?php
        include_partial('global/flash_messages');
//    echo __("This share has been deleted or you do not have permission to perform this action"); 
        ?>
    </div>


<?php } else { ?>
    <div id="redirectState" hidden="true">no</div>

    <?php }
?>