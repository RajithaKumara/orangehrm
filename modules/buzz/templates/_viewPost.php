<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewPostComponent'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/photoTiling'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewPostComponent'));
?>
<li id=<?php echo "postInList" . $postId; ?>>

    <div id="postBody">

        <div id="postBodyFirstRow">
            <div id="postFirstRowColumnOne">
                <a href="<?php echo url_for("buzz/viewProfile?empNumber=" . $employeeID); ?>"><img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employeeID); ?>" border="0" id="empPic" /></a>
            </div>
            <div id="postFirstRowColumnTwo">
                <div id="postEmployeeName" >
                    <a class="name" href= '<?php echo url_for("buzz/viewProfile?empNumber=" . $employeeID); ?>' >
                        <?php echo $postEmployeeName; ?>
                    </a>
                </div>
                <div id="postEmloyeeJobTitle">
                    <?php echo $postEmployeeJobTitle; ?>
                </div>
                <div id="postDateTime">
                    <div id="postDate">
                        <?php echo $postDate; ?>
                    </div>
                    <div id="postTime">
                        <?php echo $postTime; ?>
                    </div>
                </div>                        
            </div>

            <div id="postFirstRowColumnThree">
                <?php if (($employeeID == $loggedInUser) || ($loggedInEmployeeUserRole == 'Admin')) { ?>
                    <div id="postOptionWidget">
                        <div class="dropdown">
                            <a class="account" id=<?php echo $postId ?>></a>
                            <div class="submenu" id=<?php echo 'submenu' . $postId ?>>
                                <ul class = "root">
                                    <?php if (($employeeID == $loggedInUser)) { ?>
                                        <li ><a href = "javascript:void(0)" class="editShare" id=<?php echo 'editShare_' . $postId ?> ><?php echo __("Edit"); ?></a></li>
                                    <?php }
                                    ?>
                                    <li ><a href = "javascript:void(0)" class="deleteShare" id=<?php echo 'deleteShare_' . $postId ?>><?php echo __("Delete"); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="viewMorveShare"  id="postBodyViewMore">

            <img  class="viewMoreShare" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/icons/readmore-icon.png"); ?>" border="0" id='<?php echo 'shareViewMore_' . $postId ?>'
                  height="30" width="30"/>
        </div>
        <!-- pop up-->
        <div class="modal hide viewMoreModal"  id='<?php echo 'shareViewMoreMod1_' . $postId ?>'>
            <div class="modal-body viewMoreModal-body" >
                <div class="hideModalPopUp" id='<?php echo 'shareViewMoreMod1_' . $postId ?>'
                     ><img 
                        class="hideModalPopUp" id='<?php echo 'shareViewMoreMod1_' . $postId ?>' 
                        src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                        /></div>

                <div class="shareView" id='<?php echo 'shareViewContent1_' . $postId ?>'>
                </div>
            </div>
        </div>
        <!--new Code of like, unlike and share buttons-->
        <div id="postBodyThirdRowNew">
            <div class="likeLinknew"  id="<?php echo 'postLikebody_' . $postId ?>" > 
                <?php if ($isLike == 'Unlike') { ?>
                    <a href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeyes_' . $postId ?>'> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                              class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                    <a style="display:none;" href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeno_' . $postId ?>'> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                              class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                        <?php
                    } else {
                        ?>
                    <a style="display:none;" href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeyes_' . $postId ?>'> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                              class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                    <a href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeno_' . $postId ?>'> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                              class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                        <?php
                    }
                    ?>
                <img style="display:none;" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" border="0" id='<?php echo 'postLikeLoading_' . $postId ?>'
                     class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/>
            </div>
            <div class="unlikeLinknew" id='<?php echo 'postUnLikebody_' . $postId ?>' >
                <?php if ($isUnlike == 'yes') { ?>
                    <a style="display:none;" href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeno_' . $postId ?>> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" 
                              border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                    <a  href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeyes_' . $postId ?>> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" 
                              border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                    <?php } else {
                        ?>
                    <a href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeno_' . $postId ?>> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" 
                              border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                    <a  style="display:none;" href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeyes_' . $postId ?>> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" 
                              border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                        <?php
                    }
                    ?>
                <img style="display:none;" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" border="0" id='<?php echo 'postUnLikeLoading_' . $postId ?>'
                     class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/>
            </div>

            <div class="shareLinknew" id='<?php echo 'postSharebody_' . $postId ?>' >
                <?php if ($postShareCount > 0) { ?>
                    <a href="javascript:void(0)" class="postShare" id=<?php echo 'postShareyes_' . $postId ?>> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share2.png"); ?>" 
                              border="0" id='<?php echo 'postLike_' . $postId ?>'height="30" width="30"/></a>
                    <a style="display:none;" href="javascript:void(0)" class="postShare" id=<?php echo 'postShareno_' . $postId ?>> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share.png"); ?>" 
                              border="0" id='<?php echo 'postLike_' . $postId ?>'height="30" width="30"/></a>

                <?php } else { ?>
                    <a style="display:none;" href="javascript:void(0)" class="postShare" id=<?php echo 'postShareyes_' . $postId ?>> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share2.png"); ?>" 
                              border="0" id='<?php echo 'postLike_' . $postId ?>'height="30" width="30"/></a>
                    <a href="javascript:void(0)" class="postShare" id=<?php echo 'postShareno_' . $postId ?>> 
                        <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share.png"); ?>" 
                              border="0" id='<?php echo 'postLike_' . $postId ?>'height="30" width="30"/></a>
                        <?php
                    }
                    ?>
            </div>
        </div>

        <div id="postBodySecondRow" >
            <div class="postContent" id='<?php echo 'postContent_' . $postId ?>'>
                <?php
                if (strlen($postContent) > $postLenth) {
                    $subContent = substr($postContent, 0, $postLenth);
                    echo BuzzTextParserService::parseText($subContent . '...');
                    ?><a  class="viewMoreShare"  id='<?php echo 'shareViewMore_' . $postId ?>'
                        ><?php echo _('Read More'); ?></a>

                    <?php
                } else {
                    echo BuzzTextParserService::parseText($postContent);
                }
                ?>
                <?php
                if ($postType == '1') {
                    ?>
                    <!--SUB POST START-->
                    <div id="sharedPostBody">

                        <div id="postBodyFirstRow">
                            <div id="postFirstRowColumnOne">
                                <a href="<?php echo url_for("buzz/viewProfile?empNumber=" . $employeeID); ?>"><img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $originalPostEmpNumber); ?>" border="0" id="empPic" height="40" width="30"/></a>
                            </div>
                            <div id="postFirstRowColumnTwo">
                                <div id="postEmployeeName" >
                                    <a class="originalPostView" href="javascript:void(0);" id='<?php echo 'postView_' . $postId . '_' . $originalPostId ?>' >
                                        <?php echo $originalPostSharerName; ?>
                                    </a>
                                </div>
                                <div id="postDateTime">
                                    <div id="postDate">
                                        <?php echo $originalPostDate; ?>
                                    </div>
                                    <div id="postTime">
                                        <?php echo $originalPostTime; ?>
                                    </div>
                                </div>                        
                            </div>
                        </div>

                        <div id="postBodySecondRow">
                            <div id="postContent">
                                <?php
                                if (strlen($originalPostContent) > $postLenth) {
                                    $subContent = substr($originalPostContent, 0, $postLenth);
                                    echo BuzzTextParserService::parseText($subContent . '...');
                                    ?>
                                    <?php
                                } else {
                                    echo BuzzTextParserService::parseText($originalPostContent);
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <!--SUB POST END-->
                    <div class="modal hide originalPostModal" id='<?php echo 'postViewOriginal_' . $postId ?>'>

                        <div class="modal-body originalPostModal-body" >
                            <div class="hideModalPopUp" id='<?php echo 'postViewOriginal_' . $postId ?>'
                                 ><img 
                                    class="hideModalPopUp" id='<?php echo 'postViewOriginal_' . $postId ?>' 
                                    src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                                    /></div>

                            <div class="postView" id='<?php echo 'postViewContent_' . $postId ?>'>
                            </div>
                        </div>
                    </div>
                    <?php
                } else {

                    if (strlen($originalPostContent) > $postLenth) {
                        $subContent = substr($originalPostContent, 0, $postLenth);
                        echo BuzzTextParserService::parseText($subContent . '...');
                        ?><a  class="viewMoreShare"  id='<?php echo 'shareViewMore_' . $postId ?>'
                            ><?php echo _('Read More'); ?></a>

                        <?php
                    } else {
                        echo BuzzTextParserService::parseText($originalPostContent);
                    }
                }
                ?>
            </div>
        </div>

        <?php if (count($originalPost->getLinks()) > 0) { ?>
            <?php foreach ($originalPost->getLinks() as $link) { ?>
                <?php if ($link->getType() == 1) { ?>
                    <iframe src="<?php echo $link->getLink(); ?>" width="100%" height="250" style="margin-top: 5px " frameborder="0" allowfullscreen></iframe >

                <?php } ?>
                <?php if ($link->getType() == 0) { ?>
                    <div id="postBodySecondRow">
                        <div id="postContent">
                            <p>
                                <a id="linkTitle" href="<?php echo $link->getLink(); ?>">
                                    <?php echo $link->getTitle(); ?></a> 
                            </p>
                            <p>
                            <div id="linkText"><?php echo BuzzTextParserService::parseText($link->getDescription()); ?></div>
                            </p>

                        </div>
                    </div>
                <?php } ?>
            <?php } ?>    
        <?php } ?>  

        <?php
        $photos = $sf_data->getRaw('originalPost')->getPhotos();
        ?>
        <?php include_component('buzz', 'photoTilling', array('photos' => $photos, 'originalPost' => $originalPost, 'postId' => $postId)); ?>

        <!-- photo privies pop up-->
        <div class="modal hide originalPostModal"  id='<?php echo "showPhotos" . $postId; ?>'>
            <div class="modal-body originalPostModal-body"  id="modalnewlook">

                <div class="hideModalPopUp" id='<?php echo "showPhotos" . $postId; ?>'
                     ><img 
                        class="hideModalPopUp" id='<?php echo "showPhotos" . $postId; ?>' 
                        src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                        /></div>
                <div class="photopageCss" id="photoPage" >
                    <!--new Code of like, unlike and share buttons-->
                    <div id="postBodyThirdRowNewPopUP">
                        <div class="likeLinknew"  id="<?php echo 'postLikebody_' . $postId ?>" > 
                            <?php if ($isLike == 'Unlike') { ?>
                                <a  href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeyes_' . $postId ?>'> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                          class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                                <a style="display:none;" href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeno_' . $postId ?>'> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                          class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                                    <?php
                                } else {
                                    ?>
                                <a style="display:none;" href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeyes_' . $postId ?>'> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/icons.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                          class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                                <a  href="javascript:void(0)" class="<?php echo $isLike . ' postLike'; ?>" id='<?php echo 'postLikeno_' . $postId ?>'> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/like.png"); ?>" border="0" id='<?php echo 'postLike_' . $postId ?>'
                                          class="<?php echo $isLike . ' postLike'; ?>" height="30" width="30"/></a>
                                    <?php
                                }
                                ?>

                        </div>
                        <div class="unlikeLinknew" id='<?php echo 'postUnLikebody_' . $postId ?>' >
                            <?php if ($isUnlike == 'yes') { ?>

                                <a  href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeyes_' . $postId ?>> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" 
                                          border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                                <a style="display:none;" href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeno_' . $postId ?>> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" 
                                          border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                                <?php } else {
                                    ?>
                                <a href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeno_' . $postId ?>> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike.png"); ?>" 
                                          border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                                <a  style="display:none;" href="javascript:void(0)" class="postUnlike2" id=<?php echo 'postUnlikeyes_' . $postId ?>> 
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" 
                                          border="0" id='<?php echo 'postLike_' . $postId ?>'  height="30" width="30"/></a>
                                    <?php
                                }
                                ?>

                        </div>
                    </div>
                    <?php
                    $imgCount = 1;
                    foreach ($photos as $photo) {
                        ?>
                        <img class="postPhotoPrev"  id="<?php echo "img_" . $imgCount . "_" . $postId; ?>" 
                             style="display:none;<?php
                             if ($photo->getHeight() / $photo->getWidth() > (400 / 450)) {
                                 echo 'height:99%';
                             } else {
                                 echo 'width:99%';
                             }
                             ?>;" src="data:image/jpeg;base64,<?php echo base64_encode($photo->getPhoto()); ?>"/>

                        <?php
                        $imgCount++;
                    }
                    ?>
                    <?php if (count($photos) > 1) { ?>


                        <button class="imageNextBtn" disabled="true" id="imageNextBtn<?php echo $postId; ?>">
                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/next.png"); ?>" border="0" height="100%" /></button>
                        <button class="imagePrevBtn" disabled="true" id="imagePrevBtn<?php echo $postId; ?>">
                            <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/prev.png"); ?>" border="0" height="100%" /></button>
                    <?php } ?>
                </div>
                <div id="photoPageComment" >
                    <div id="postBodyFirstRow photo" class="photoViewEmp">
                        <div id="postFirstRowColumnOne"  style="width: 50px;height: 50px;overflow: hidden">
                            <img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $employeeID); ?>" border="0" id="empPic" />
                        </div>
                        <div id="postFirstRowColumnTwo">
                            <div id="postEmployeeName" >
                                <a class="name" href= '<?php echo url_for("buzz/viewProfile?empNumber=" . $employeeID); ?>' >
                                    <?php echo $postEmployeeName; ?>
                                </a>
                            </div>
                            <div id="postDateTime">
                                <div id="postDate">
                                    <?php echo $postDate; ?>
                                </div>
                                <div id="postTime">
                                    <?php echo $postTime; ?>
                                </div>
                            </div>                        
                        </div>
                    </div>
                    <?php include_component('buzz', 'commentPreview', array('commentList' => $commentList, 'editForm' => $editForm, 'loggedInUser' => $loggedInUser, 'postId' => $postId, 'commentForm' => $commentForm, 'commentBoxId' => 'popPhotoId')); ?>

                </div>

            </div>

        </div>

        <!-- start share post popup window-->
        <div class="modal hide sharePostPopUpModal"   id='<?php echo 'posthide_' . $postId ?>'>

            <div class="modal-body originalPostModal-body" >
                <div class="hideModalPopUp" id='<?php echo 'posthide_' . $postId ?>'
                     ><img 
                        class="hideModalPopUp" id='<?php echo 'posthide_' . $postId ?>' 
                        src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                        /></div>
                <div class="sharePageForm">
                    <form id="frmCreateComment" method="" action="" style="margin-top: 10px;"
                          enctype="multipart/form-data">
                              <?php
                              $placeholder = 'Whats on your mind';
                              echo $commentForm['comment']->render(array('id' => "shareBox_" . $postId,
                                  'class' => 'shareBox', 'style' => 'width: 95%', 'rows' => '2', 'placeholder' => $placeholder));
                              ?>

                    </form>
                    <div id="sharedPostBody">

                        <div id="postBodyFirstRow">
                            <div id="postFirstRowColumnOne">
                                <a href="<?php echo url_for("buzz/viewProfile?empNumber=" . $employeeID); ?>"><img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $originalPostEmpNumber); ?>" border="0" id="empPic" height="40" width="30"/></a>
                            </div>
                            <div id="postFirstRowColumnTwo">
                                <div id="postEmployeeName" >
                                    <a class="name" href="javascript:void(0);">
                                        <?php echo $originalPostSharerName; ?>
                                    </a>
                                </div>
                                <div id="postDateTime">
                                    <div id="postDate">
                                        <?php echo $originalPostDate; ?>
                                    </div>
                                    <div id="postTime">
                                        <?php echo $postTime; ?>
                                    </div>
                                </div>                        
                            </div>
                        </div>

                        <div id="postBodySecondRow">
                            <div id="postContent">

                                <?php
                                if (strlen($originalPostContent) > 500) {
                                    echo BuzzTextParserService::parseText(substr($originalPostContent, 0, 500) . '...');
                                } else {
                                    echo BuzzTextParserService::parseText($originalPostContent);
                                }
                                ?>
                            </div>
                        </div>
                        <?php if (count($originalPost->getLinks()) > 0) { ?>
                            <?php foreach ($originalPost->getLinks() as $link) { ?>
                                <?php if ($link->getType() == 1) { ?>
                                    <div class="sharePageIframe">
                                        <iframe src="<?php echo $link->getLink(); ?>" width="100%" height="150" style="margin-top: 5px;margin: 0 auto; " frameborder="0" allowfullscreen></iframe >
                                    </div>
                                <?php } ?>
                                <?php if ($link->getType() == 0) { ?>
                                    <div id="postBodySecondRow">
                                        <div id="postContent" >
                                            <p>
                                                <a id="linkTitle" href="<?php echo $link->getLink(); ?>">
                                                    <?php echo $link->getTitle(); ?></a> 
                                            </p>
                                            <p>
                                            <div id="linkText"><?php echo BuzzTextParserService::parseText($link->getDescription()); ?></div>
                                            </p>

                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>    
                        <?php } ?>  

                        <?php
                        $photos = $sf_data->getRaw('originalPost')->getPhotos();
                        $imgCount = 1;
                        if (count($photos) > 0) {
                            ?>
                            <div class="sharePagePhotoComponent">
                                <?php include_component('buzz', 'photoTilling', array('photos' => $photos, 'originalPost' => $originalPost, 'postId' => $postId)); ?>
                            </div>
                        <?php } ?>

                        <button type="button" class="btnShare" name="btnSaveDependent" id='<?php echo 'btnShare_' . $postId . "_" . $originalPostId ?>'><?php echo __("Share"); ?></button>

                    </div>
                </div>
            </div>
        </div>
        <!-- end share post pop up window-->
        <!-- start edit post popup window-->
        <div class="modal hide originalPostModal"  id='<?php echo 'editposthide_' . $postId ?>'>

            <div class="modal-body editPostModal-body" >
                <div class="hideModalPopUp" id='<?php echo 'editposthide_' . $postId ?>'
                     ><img 
                        class="hideModalPopUp" id='<?php echo 'editposthide_' . $postId ?>'
                        src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                        /></div>
                <div class="popUpContainer">
                    <div id="postBodySecondRow" >

                        <h3><?php echo __('Edit your post'); ?></h3>

                        <?php
                        if ($postType == '1') {
                            ?>
                            <form id="frmCreateComment" method="" action="" 
                                  enctype="multipart/form-data">
                                      <?php
                                      $editForm->setDefault('comment', $postContent);
                                      echo $editForm['comment']->render(array('id' => "editshareBox_" . $postId,
                                          'class' => 'shareBox', 'style' => 'width: 95%', 'rows' => '2'));
                                      ?>

                            </form>
                        <?php } else { ?>
                            <form id="frmCreateComment" method="" action="" 
                                  enctype="multipart/form-data">
                                      <?php
                                      $editForm->setDefault('comment', $originalPostContent);
                                      echo $editForm['comment']->render(array('id' => "editshareBox_" . $postId,
                                          'class' => 'shareBox', 'style' => 'width: 95%', 'rows' => '2'));
                                      ?>

                            </form>

                        <?php } ?>
                        <div class="sharePopUpbutton">
                            <button type="button"  class="btnEditShare" name="btnSaveDependent" id='<?php echo 'btnEditShare_' . $postId ?>' ><?php echo __("Save"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end edit post pop up window-->
        <!-- start like window popup window-->
        <div class="modal hide modal-on-preview" id='<?php echo 'postlikehide_' . $postId ?>'>
            <div id="modalHeader" >
                <?php echo __("People who like this post"); ?>
            </div>
            <div class="modal-body originalPostModal-body" >
                <div class="hideModalPopUp" id='<?php echo 'postlikehide_' . $postId ?>'
                     ><img 
                        class="hideModalPopUp" id='<?php echo 'postlikehide_' . $postId ?>' 
                        src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                        /></div>
                <div class=""  id='<?php echo 'postlikehidebody_' . $postId ?>'></div>

            </div>
        </div>
        <!-- end like window pop up window-->
        <!-- start like window popup window-->
        <div class="modal hide modal-on-preview" id='<?php echo 'postsharehide_' . $postId ?>'>
            <div id="modalHeader" >
                <?php echo __("People who shared this post"); ?>
            </div>
            <div class="modal-body originalPostModal-body" >
                <div class="hideModalPopUp" id='<?php echo 'postsharehide_' . $postId ?>'
                     ><img 
                        class="hideModalPopUp" id='<?php echo 'postsharehide_' . $postId ?>' 
                        src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                        /></div>
                <div class=""  id='<?php echo 'postsharehidebody_' . $postId ?>'></div>

            </div>
        </div>
        <!-- end like window pop up window-->
        <div id="postBodyThirdRow">
            <div id="noOfLikesLinknew" >
                <a class="postNoofLikesTooltip" href="javascript:void(0)" id='<?php echo 'postNoOfLikes_' . $postId ?>' 
                   >
                    <span id="<?php echo 'noOfLikes_' . $postId; ?>"><?php echo $postNoOfLikes; ?></span>
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like-this.png"); ?>" border="0" id='<?php echo 'commentLike_' . $postId ?>' 
                          height="16" width="16"/><?php echo __(" this"); ?>
                </a>
            </div>
            <div id="noOfSharesLinknew" >
                <a class="postNoofSharesTooltip" href="javascript:void(0)" id='<?php echo 'postNoOfShares_' . $postId ?>' >
                    <span id="<?php echo 'noOfShares_' . $postId; ?>"><?php echo $postShareCount; ?></span>
                    <img  id='<?php echo 'postNoOfSharesImage_' . $postId ?>' src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/share2.png"); ?>" border="0"  
                          height="16" width="16"/><?php echo __(" this"); ?>
                </a>
            </div>
            <div id="noOfUnLikesLinknew" >
                <a class="postNoofUnLikesTooltip" href="javascript:void(0)" id='<?php echo 'postNoOfLikes_' . $postId ?>' >
                    <span id="<?php echo 'noOfUnLikes_' . $postId; ?>"><?php echo $postUnlike; ?></span>
                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' 
                          height="16" width="16"/><?php echo __(" this"); ?>
                </a>
            </div>
        </div>

        <div id="postBodyFourthRow">
            <a href="javascript:void(0)" class="postViewMoreCommentsLink" id=<?php echo 'postViewMoreCommentsLink_' . $postId ?>>
                <?php
                $commentCount = $post->getComment()->count();
                if ($commentCount > $initialcommentCount) {
                    echo __("View") . " " . ($commentCount - $initialcommentCount) . " ";
                    echo __("more comments");
                    echo __(" out of" . " " . $commentCount . " " . "comments");
                }
                ?>
            </a>
        </div>
        
        <div id="comment-text-width-analyzer">
            
        </div>

        <div id="postFifthRow" class="postRow">
            <div class="postCommentBox" id=<?php echo "postCommentTextBox_" . $postId; ?>>
                <form class="<?php echo $postId; ?>" id='<?php echo 'formCreateComment_listId' . $postId; ?>' method="" action="" 
                      enctype="multipart/form-data">
                          <?php
                          $placeholderd = __("Add your comment");
                          echo $commentForm['comment']->render(array('id' => "commentBoxNew_listId" . $postId,
                              'class' => 'commentBox', 'rows' => '1', 'style' => 'font-size: 16px; font-family: "SourceSansProLight"; border-radius: 5px 5px 5px 5px; min-width: 99.5%; padding: 10px 0 10px 10px;', 'placeholder' => $placeholderd));
                          $commentForm->setDefault('shareId', $postId);
                          ?>
                    <div style="display:none">
                        <?php echo $commentForm['shareId']->render(); ?>
                    </div>
                    <?php
                    echo $commentForm['_csrf_token']->render();
                    ?>
                    <input type="button" value="<?php echo __("Comment"); ?>"  id='<?php echo 'commentBoxNew_listId' . $postId; ?>' class="commentSubmitBtn submitBtn">
                    <button type="button" id='<?php echo 'commentBoxNew_listId' . $postId; ?>' class="commentSubmitBtn submitBtn commentSubmitBtnForIe">
                        <?php echo __("Comment"); ?>
                    </button>
                </form>
            </div>
        </div>

    </div>
    <?php
    if (count($commentList) > 0) {
        $displayCommentList = 'block';
    } else {
        $displayCommentList = 'none';
    }
    ?>

    <div class="commentListContainer"  id='<?php echo 'commentListContainer_' . $postId ?>' style="display: <?php echo $displayCommentList; ?>">
        <ul class="commentList" id='<?php echo 'commentListNew_listId' . $postId ?>'>
            <?php
            $count = 0;
            $display = 'block';
            foreach ($commentList as $comment) {
                $commentId = $comment->getId();
                $commentEmployeeJobTitle = $comment->getEmployeeComment()->getJobTitleName();
                $commentPostId = $comment->getShareId();
                $commentContent = $comment->getCommentText();
                $commentEmployeeName = $comment->getEmployeeFirstLastName();
                $commentEmployeeId = $comment->getEmployeeNumber();
                $commentNoOfLikes = $comment->getNumberOfLikes();
                $commentNoOfUnLikes = $comment->getNumberOfUnlikes();
                $isUnlikeComment = 'no';
                if ($comment->isUnLike($loggedInUser) == 'yes') {
                    $isUnlikeComment = 'yes';
                }
                $commentDate = $comment->getDate();
                $commentTime = $comment->getTime();
                $isLikeComment = $comment->isLike($loggedInUser);
                $commentLikeEmployes = $comment->getLikedEmployeeList();
                $peopleLikeArray = $comment->getLikedEmployees();
//                            $peopleLikeArray = array("Aruna Tebel", "Dewmal Anicitus");
                if ($count >= $initialcommentCount) {
                    $display = 'none';
                }
                $count++;
                ?>

                <!-- start edit comment popup window-->
                <div class="modal hide originalPostModal"  id='<?php echo 'editcommenthideNew2_' . $commentId ?>'>

                    <div class="modal-body editPostModal-body" >
                        <div class="hideModalPopUp" id='<?php echo 'editcommenthideNew2_' . $commentId ?>'
                             ><img 
                                class="hideModalPopUp" id='<?php echo 'editcommenthideNew2_' . $commentId ?>'
                                src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                                /></div>
                        <div class="popUpContainer">
                            <div id="postBodySecondRow" >
                                <h3><?php echo __('Edit your comment'); ?></h3>
                                <form id="frmCreateComment" method="" action="" 
                                      enctype="multipart/form-data">
                                          <?php
                                          $editForm->setDefault('comment', $commentContent);
                                          echo $editForm['comment']->render(array('id' => "editcommentBoxNew2_" . $commentId,
                                              'class' => 'commentBox', 'style' => 'width: 95%', 'rows' => '2'));
                                          ?>

                                </form>

                                <div class="editCommrntPageButton">
                                    <button type="button"  class="btnEditCommentNew" name="btnSaveDependent" id='<?php echo 'btnEditComment_' . $commentId ?>' ><?php echo __("Save"); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end edit comment pop up window-->

                <li id="<?php echo "commentInPost_" . $commentId; ?>" style="display: <?php echo $display; ?>" class="<?php echo $commentPostId; ?>" >
                    <div id="commentBody">
                        <div id="commentRowOne">
                            <div id="commentColumnOne">
                                <a href="<?php echo url_for("buzz/viewProfile?empNumber=" . $commentEmployeeId); ?>"><img alt="<?php echo __("Employee Photo"); ?>" src="<?php echo url_for("buzz/viewPhoto?empNumber=" . $commentEmployeeId); ?>" border="0" id="empPic"/></a>
                            </div>
                            <div id="commentColumnTwo">
                                <div id="commentEmployeeName">
                                    <a class="name" href= '<?php echo url_for("buzz/viewProfile?empNumber=" . $commentEmployeeId); ?>' ><?php echo $commentEmployeeName; ?></a>
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
                                <div id="commentColumnThree">
                                    <?php if (($commentEmployeeId == $loggedInUser) || ($loggedInEmployeeUserRole == 'Admin')) { ?>
                                        <div id="commentOptionWidget">
                                            <div class="dropdown commentDropDown">
                                                <a class="commentAccount" id=<?php echo 'cnew' . $commentId ?>></a>
                                                <div class="submenu" id=<?php echo 'submenucnew' . $commentId ?>>
                                                    <ul class = "root"><?php if (($commentEmployeeId == $loggedInUser)) { ?>
                                                            <li ><a href = "javascript:void(0)" class="editComment" id=<?php echo 'editComment_' . $commentId ?> ><?php echo __("Edit"); ?></a></li>
                                                        <?php }
                                                        ?>
                                                        <li ><a href = "javascript:void(0)" class="deleteComment" id=<?php echo 'deleteComment_' . $commentId ?>><?php echo __("Delete"); ?></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <!-- start like window popup window-->
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
                        <!-- end like window pop up window-->

                        <div id="commentBodyThirdRow">
                            <div id="noOfLikesLinknew" >
                                <a class="commentNoofLikesTooltip" href="javascript:void(0)" id='<?php echo 'postNoOfLikes_' . $commentId ?>' >
                                    <span id="<?php echo 'commentNoOfLikes_' . $commentId; ?>"><?php echo $commentNoOfLikes; ?></span>
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like-this.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' 
                                          height="16" width="16"/><?php echo __(" this"); ?>
                                </a>
                            </div>

                            <div id="noOfUnLikesLinknew" >
                                <a class="postNoofUnLikesTooltip" href="javascript:void(0)" id='<?php echo 'postNoOfLikes_' . $commentId ?>' >
                                    <span id="<?php echo 'commentNoOfUnLikes_' . $commentId; ?>"><?php echo $commentNoOfUnLikes; ?></span>
                                    <img  src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/like/unlike2.png"); ?>" border="0" id='<?php echo 'commentLike_' . $commentId ?>' 
                                          height="16" width="16"/><?php echo __(" this"); ?>
                                </a>
                            </div>
                        </div>

                        <div  id="commentBodyThirdRowNew">
                            <div class="likeCommentnew"  id="<?php echo 'commentLikebody_' . $commentId ?>" >
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

                                <div class="textTopOfImageComment" id='<?php echo 'commentNoOfLiketext_' . $commentId ?>'><?php echo $commentNoOfLikes ?></div>
                            </div>
                            <div class="unlikeCommentnew" id='<?php echo 'commentUnLikebody_' . $commentId ?>' >
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

                                <div class="textTopOfImageComment" id='<?php echo 'commentNoOfUnLiketext_' . $commentId ?>'><?php echo $commentNoOfUnLikes ?></div>
                            </div>
                        </div>
                        <div id="commentRowTwo">
                            <div  id="commentColumnTwo">
                                <div class="commentContent"id='<?php echo 'commentContentNew_' . $commentId ?>'>
                                    <?php echo BuzzTextParserService::parseText($commentContent); ?>
                                </div>
                            </div>
                        </div>


                    </div>
                </li>
            <?php } ?>
        </ul>
        <div class="commentLoadingBox"  id='<?php echo 'commentLoadingBoxlistId' . $postId; ?>' >
            <div id="commentBody">

                <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="20" style="margin-left: 40%; margin-top: 15px" />
            </div>
        </div>
    </div>
    <div class="lastLoadedPost" id=<?php echo $postId; ?>></div>
</li>
