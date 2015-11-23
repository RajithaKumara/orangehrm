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
//use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess'));
////use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess'));
//use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/buzzNew'));
?>

<?php if ($isSuccessfullyAddedComment) { ?>

    <!-- start edit comment popup window-->
    <div id="modalEdit">
        <div class="modal hide"  id='<?php echo 'editcommenthideNew2_' . $commentId ?>'>

            <div class="modal-body editPostModal-body" >
                <div class="hideModalPopUp" id='<?php echo 'editcommenthideNew2_' . $commentId ?>'
                     ><img 
                        class="hideModalPopUp" id='<?php echo 'editcommenthideNew2_' . $commentId ?>'
                        src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                        /></div>
                <div class="popUpContainer">
                    <div id="postBodySecondRow" >
                        <div class="mb-heading">
                                    <?php echo __('Edit your comment'); ?>
                                </div>
                        <form id="frmCreateComment" method="" action="" 
                              enctype="multipart/form-data">
                                  <?php
                                  $editForm->setDefault('comment', $commentContent);
                                  echo $editForm['comment']->render(array('id' => "editcommentBoxNew2_" . $commentId,
                                      'class' => 'commentBox popupEdit', 'style' => 'width: 100%', 'rows' => '3'));
                                  ?>

                        </form>

                        <div class="editCommrntPageButton">
                            <button type="button"  class="btnEditCommentNew" name="btnSaveDependent" id='<?php echo 'btnEditComment_' . $commentId ?>' ><?php echo __attr("Save"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end edit comment pop up window-->

    <li id="<?php echo "commentInPost_" . $commentId; ?>" style="display: <?php echo $display; ?>" class="<?php echo $commentPostId; ?>" >
        <div class="addNewCommentBody" id="commentBody">
            <div id="commentRowOne">
                <div id="commentColumnOne">
                    <a href="<?php echo url_for("buzz/viewProfile?empNumber=" . $employeeID); ?>"><img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $commentEmployeeId); ?>" border="0" id="empPic"/></a>
                </div>
                <div id="commentColumnTwo">
                    <div id="commentEmployeeName">
                        <a class="name" href= '<?php echo url_for("buzz/viewProfile?empNumber=" . $employeeID); ?>' ><?php echo $commentEmployeeName; ?></a>
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
                    <?php if (($commentEmployeeId == $loggedInUser) || ($loggedInEmployeeUserRole == 'Admin')) { ?>
                        <div id="commentOptionWidget">
                            <div class="dropdown commentDropDown">
                                <a class="commentAccount" id='<?php echo 'commentnew' . $commentId; ?>'></a>
                                <div class="submenu" id='<?php echo 'submenucommentnew' . $commentId; ?>'>
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

            <!-- start like window popup window-->
            <div id="modatLikeWindow" >
                <div class="modal hide" id='<?php echo 'postlikehide_' . $commentId ?>'>
                    <div id="modalHeader" >
                        <?php echo __("People who like this comment"); ?>
                    </div>
                    <div class="modal-body originalPostModal-body" >
                        <div class="hideModalPopUp" id='<?php echo 'postlikehide_' . $commentId ?>'><img 
                                class="hideModalPopUp" id='<?php echo 'postlikehide_' . $commentId ?>' 
                                src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                                /></div>
                        <div class=""  id='<?php echo 'postlikehidebody_' . $commentId ?>'></div>
                    </div>
                </div>
            </div>
            <!-- end like window pop up window-->

            <div id="commentBodyThirdRow">
                <div id="noOfLikesLinknew" >
                    <?php
                    if ($commentNoOfLikes > 0) {
                        $tooltipClass = "commentNoofLikesTooltip";
                    } else {
                        $tooltipClass = "commentNoofLikesTooltip disabledLinks";
                    }
                    ?>
                    <a title="" class="<?php echo $tooltipClass; ?>" href="javascript:void(0)" id='<?php echo 'cmntNoOfLikes_' . $commentId ?>' >
                        <span id="<?php echo 'commentNoOfLikes_' . $commentId; ?>"><?php echo $commentNoOfLikes; ?></span>
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like-this.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' 
                              height="16" width="16"/> <?php echo __("this"); ?>
                    </a>
                </div>

                <div id="noOfUnLikesLinknew" >
                    <a class="postNoofUnLikesTooltip disabledLinks" href="javascript:void(0)" id='<?php echo 'cmntNoOfLikes_' . $commentId ?>' >
                        <span id="<?php echo 'commentNoOfUnLikes_' . $commentId; ?>"><?php echo $commentNoOfUnLikes; ?></span>
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' 
                              height="16" width="16"/> <?php echo __("this"); ?>
                    </a>
                </div>
            </div>

            <div  id="commentBodyThirdRowNew">
                <div class="likeCommentnew"  id="<?php echo 'commentLikebody_' . $commentId ?>" >
                    <?php if ($isLikeComment == 'Unlike') { ?>
                        <a style="display:none;" href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?> tiptip" title="<?php echo __('Like')?>" id='<?php echo 'commentLikeno_' . $commentId ?>'> 
                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                  class="<?php echo $isLikeComment . ' commentLike'; ?>" height="20" width="20"/></a>
                        <a href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?> tiptip" title="<?php echo __('Like')?>" id='<?php echo 'commentLikeyes_' . $commentId ?>'> 
                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                  class="<?php echo $isLikeComment . ' commentLike'; ?>" height="20" width="20"/></a>
                        <?php } else { ?>
                        <a href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?> tiptip" title="<?php echo __('Like')?>" id='<?php echo 'commentLikeno_' . $commentId ?>'> 
                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                  class="<?php echo $isLikeComment . ' commentLike'; ?>" height="22" width="22"/></a>
                        <a style="display:none;" href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?> tiptip" title="<?php echo __('Like')?>"  id='<?php echo 'commentLikeyes_' . $commentId ?>'> 
                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                  class="<?php echo $isLikeComment . ' commentLike'; ?>" height="22" width="22"/></a>
                        <?php } ?>

                    <div class="textTopOfImageComment" id='<?php echo 'commentNoOfLiketext_' . $commentId ?>'><?php echo $commentNoOfLikes ?></div>
                </div>
                <div class="unlikeCommentnew" id='<?php echo 'commentUnLikebody_' . $commentId ?>' >
                    <?php if ($isUnlikeComment == 'yes') { ?>
                        <a style="display:none;" href="javascript:void(0)" class="commentUnlike2 tiptip" title="<?php echo __('Unlike')?>"  id=<?php echo 'commentUnLikeno_' . $commentId ?>>
                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="20" width="20"/></a>
                        <a  href="javascript:void(0)" class="commentUnlike2 tiptip" title="<?php echo __('Unlike')?>" id=<?php echo 'commentUnLikeyes_' . $commentId ?>>
                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="20" width="20"/></a>
                    <?php } else { ?>
                        <a  href="javascript:void(0)" class="commentUnlike2 tiptip" title="<?php echo __('Unlike')?>" id=<?php echo 'commentUnLikeno_' . $commentId ?>>
                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="22" width="22"/></a>
                        <a style="display:none;" href="javascript:void(0)" class="commentUnlike2 tiptip" title="<?php echo __('Unlike')?>" id=<?php echo 'commentUnLikeyes_' . $commentId ?>>
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

            <div  id="commentColumnTwo">

            </div>
        </div>
    </li>
    <div class="commentLoadingBox"  id='<?php echo 'commentLoadingBox' . $postId; ?>' >
        <div id="commentBody">

            <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="20" style="margin-left: 40%; margin-top: 15px" />
        </div>
    </div>
<?php } else if (!$isSuccessfullyAddedComment) { ?>

    <div id ='errorFirstRow'>
        <?php
        include_partial('global/flash_messages');
        ?>
    </div>

<?php } else { ?>
    <div id="redirectState" style="display:none;">no</div>

<?php }
?>

