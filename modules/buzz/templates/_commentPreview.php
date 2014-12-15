<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess_1'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccessComment'));
?>

<div id="commentListContainerforpev">
    <ul class="popupCommentList" id='<?php echo 'commentList_' . $postId ?>'>
        <?php
        $count = 0;
        $display = 'block';
        foreach ($commentList as $comment) {
            $commentId = $comment->getId();
            $commentPostId = $comment->getShareId();
            $commentContent = $comment->getCommentText();
            $commentEmployeeName = $comment->getEmployeeFirstLastName();
            $commentEmployeeId = $comment->getEmployeeNumber();
            $commentNoOfLikes = $comment->getNumberOfLikes();
            $commentNoOfUnLikes = $comment->getNumberOfUnlikes();
            if ($comment->isUnLike($loggedInUser)) {
                $isUnlike = 'yes';
            }
            $commentDate = $comment->getDate();
            $commentTime = $comment->getTime();
            $isLikeComment = $comment->isLike($loggedInUser);
            $commentLikeEmployes = $comment->getLikedEmployeeList();
            $peopleLikeArray = $comment->getLikedEmployees();
//                            $peopleLikeArray = array("Aruna Tebel", "Dewmal Anicitus");

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


                        <input type="button" class="btnEditCommentNew" name="btnSaveDependent" id='<?php echo 'btnEditComment_' . $commentId ?>' value="<?php echo __("Save"); ?>"/>

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
                        <div class="cmnt_prev_commentColTwo" id="commentColumnTwo">
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
                                    <div class="dropdown cmnt_prev_drop_down">
                                        <a class="account" id=<?php echo 'c' . $commentId ?>></a>
                                        <div class="submenu" id=<?php echo 'submenuc' . $commentId ?>>
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
                    <div id="commentRowTwo">
                        <div class="commentContent"id='<?php echo 'commentContent_' . $commentId ?>'>
                            <?php echo BuzzTextParserService::parseText($commentContent); ?>
                        </div>
                    </div>

                    <div id="commentColumnTwo">
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
        <?php } ?>
    </ul>
    <div class="commentLoadingBox">
        <div id="commentBody">

            <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="70"/>
        </div>
    </div>
    <div id="postFifthRow" class="postRow">
        <div id=<?php echo "postCommentTextBox" . $postId; ?>>
            <form id="frmCreateComment" method="" action="" 
                  enctype="multipart/form-data">
                      <?php
                      $placeholder = __("Add your comment");
                      echo $commentForm['comment']->render(array('id' => "commentBoxnew_" . $postId,
                          'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '1', 'placeholder' => $placeholder));
                      ?>
            </form>
        </div>
    </div>
</div>