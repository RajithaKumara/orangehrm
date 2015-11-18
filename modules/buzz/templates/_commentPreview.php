<?php
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccessComment'));
?>

<div id="commentListContainerforpev">
    <ul class="popupCommentList" id='<?php echo 'commentListNew_' . $commentBoxId . $postId ?>'>
        <?php
        $count = 0;
        $display = 'block';
        foreach ($commentList as $comment) {
            $commentId = $comment->getId();
            $commentPostId = $comment->getShareId();
            $commentContent = $comment->getCommentText();
            $commentEmployeeName = $comment->getEmployeeFirstLastName();
            if ($commentEmployeeName == ' ') {
                $commentEmployeeName = $comment->getEmployeeName() . ' (' . __('Deleted') . ')';
                $commenterDeleted = true;
            }
            $commentEmployeeJobTitle = $comment->getEmployeeComment()->getJobTitleName();
            $commentEmployeeId = $comment->getEmployeeNumber();
            $commentNoOfLikes = $comment->getNumberOfLikes();
            $commentNoOfUnLikes = $comment->getNumberOfUnlikes();
            $isUnlikeComment = $comment->isUnLike($loggedInUser);
            $commentDate = $comment->getDate();
            $commentTime = $comment->getTime();
            $isLikeComment = $comment->isLike($loggedInUser);
            $commentLikeEmployes = $comment->getLikedEmployeeList();
            $peopleLikeArray = $comment->getLikedEmployees();
            $count++;
            ?>
            <!-- start edit comment popup window-->
            <div class="modal hide" id='<?php echo 'editcommenthideNew_' . $commentId ?>'>
                <div class="modal-header">
                    <a class="close" data-dismiss="modal">×</a>
                    <h3><?php echo __('Edit your comment'); ?></h3>
                </div>
                <div class="modal-body">
                    <div id="postBodySecondRow">

                        <form id="frmCreateComment" method="" action="" 
                              enctype="multipart/form-data">
                                  <?php
                                  $editForm->setDefault('comment', $commentContent);
                                  echo $editForm['comment']->render(array('id' => "editcommentBoxNew_" . $commentId,
                                      'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '2'));
                                  ?>

                        </form>


                        <input type="button" class="btnEditCommentNew" name="btnSaveDependent" 
                               id='<?php echo 'btnEditComment_' . $commentId ?>' value="<?php echo __attr("Save"); ?>"/>

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
            <li id="<?php echo "commentNew_" . $commentId; ?>" style="display: <?php echo $display; ?>" 
                class="<?php echo $commentPostId; ?>" >
                <div id="commentBody">
                    <div id="commentRowOne">
                        <div id="commentColumnOne">
                            <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $commentEmployeeId); ?>" border="0" id="empPic"/>
                        </div>
                        <div class="cmnt_prev_commentColTwo" id="commentColumnTwo">
                            <div id="commentEmployeeName">
                                <?php if ($commenterDeleted) { ?>
                                    <label class="name">
                                        <?php
                                        if (strlen($commentEmployeeName) > 26) {
                                            echo substr($commentEmployeeName, 0, 26) . '...';
                                        } else {
                                            echo $commentEmployeeName;
                                        }
                                        ?>
                                    </label>
                                <?php } else { ?>
                                    <a class="name" href="javascript:void(0);" title="<?php echo $commentEmployeeName; ?>">
                                        <?php
                                        if (strlen($commentEmployeeName) > 26) {
                                            echo substr($commentEmployeeName, 0, 26) . '...';
                                        } else {
                                            echo $commentEmployeeName;
                                        }
                                        ?>
                                    </a> 
                                <?php } ?>
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
                        <!--                        <div id="commentColumnThree">
                        <?php // if ($commentEmployeeId == $loggedInUser) { ?>
                                                        <div id="commentOptionWidget">
                                                            <div class="dropdown cmnt_prev_drop_down">
                                                                <a class="account" id=<?php // echo 'c' . $commentId     ?>></a>
                                                                <div class="submenu" id=<?php // echo 'submenuc' . $commentId     ?>>
                                                                    <ul class = "root">
                                                                        <li ><a href = "javascript:void(0)" class="editComment" id=<?php // echo 'editComment_' . $commentId     ?> ><?php // echo __("Edit");     ?></a></li>
                                                                        <li ><a href = "javascript:void(0)" class="deleteComment" id=<?php // echo 'deleteComment_' . $commentId     ?>><?php // echo __("Delete");     ?></a></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                        <?php // } ?>
                                                </div>-->
                    </div>
                    <div  id="commentBodyThirdRowNew">
                        <div class="likeCommentnewPop"  id="<?php echo 'commentLikebody_' . $commentId ?>" >
                            <?php if ($isLikeComment == 'Unlike') { ?>
                                <a style="display:none;" href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeno_' . $commentId ?>'> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                          class="<?php echo $isLikeComment . ' commentLike'; ?>" height="20" width="20"/></a>
                                <a href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeyes_' . $commentId ?>'> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                          class="<?php echo $isLikeComment . ' commentLike'; ?>" height="20" width="20"/></a>
                                <?php } else { ?>
                                <a href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeno_' . $commentId ?>'> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                          class="<?php echo $isLikeComment . ' commentLike'; ?>" height="22" width="22"/></a>
                                <a style="display:none;" href="javascript:void(0)" class="<?php echo $isLikeComment . ' commentLike'; ?>" id='<?php echo 'commentLikeyes_' . $commentId ?>'> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>'
                                          class="<?php echo $isLikeComment . ' commentLike'; ?>" height="22" width="22"/></a>
                                <?php } ?>

                            <div class="textTopOfImageCommentPop" id='<?php echo 'commentNoOfLiketext_' . $commentId ?>'><?php echo $commentNoOfLikes ?></div>
                        </div>
                        <div class="unlikeCommentnewPop" id='<?php echo 'commentUnLikebody_' . $commentId ?>' >
                            <?php if ($isUnlikeComment == 'yes') { ?>
                                <a style="display:none;" href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeno_' . $commentId ?>>
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="20" width="20"/></a>
                                <a  href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeyes_' . $commentId ?>>
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="20" width="20"/></a>
                            <?php } else { ?>
                                <a  href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeno_' . $commentId ?>>
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="22" width="22"/></a>
                                <a style="display:none;" href="javascript:void(0)" class="commentUnlike2" id=<?php echo 'commentUnLikeyes_' . $commentId ?>>
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' height="22" width="22"/></a>
                            <?php } ?>

                            <div class="textTopOfImageCommentPop" id='<?php echo 'commentNoOfUnLiketext_' . $commentId ?>'><?php echo $commentNoOfUnLikes ?></div>
                        </div>
                    </div>
                    <div id="commentRowTwo">
                        <div class="commentContent"id='<?php echo 'commentContent_' . $commentId ?>'>
                            <?php echo BuzzTextParserService::parseText($commentContent); ?>
                        </div>
                    </div>

                </div>
            </li>
        <?php } ?>
    </ul>
    <div class="commentLoadingBox"  id='<?php echo 'commentLoadingBox' . $commentBoxId . $postId; ?>' >
        <div id="commentBody">
            <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="20" style="margin-left: 40%; margin-top: 15px" />
        </div>
    </div>

</div>
<div id="postFifthRow" class="postRow">
    <div id=<?php echo "postCommentTextBox" . $postId; ?>>
        <form class="frmCreateComment" id='<?php echo 'formCreateComment_' . $commentBoxId . $postId; ?>' method="" action="" 
              enctype="multipart/form-data">
                  <?php
                  $placeholder = __("Add your comment");
                  echo $commentForm['comment']->render(array('id' => "commentBoxnew_" . $commentBoxId . $postId,
                      'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '1', 'placeholder' => $placeholder));
                  $commentForm->setDefault('shareId', $postId);
                  ?>
            <div style="display:none">
                <?php echo $commentForm['shareId']->render(); ?>
            </div>
            <?php
            echo $commentForm['_csrf_token']->render();
            ?>
            <div id="commentSubmitBtnInModal">
                <input type="button" value="<?php echo __attr("Comment"); ?>"  id='<?php echo 'commentBoxNew_' . $commentBoxId . $postId; ?>' class="commentSubmitBtn submitBtn">
            </div>
            <button type="button" id='<?php echo 'commentBoxNew_' . $commentBoxId . $postId; ?>' class="commentSubmitBtn submitBtn commentSubmitBtnForIe">
                <?php echo __("Comment"); ?>
            </button>
        </form>
    </div>
</div>