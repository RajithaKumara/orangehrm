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
 * Please refer http://www.orangehrm.com/Files/OrangeHRM_Commercial_License.pdf for the license which includes terms and
 *  conditions on using this software.
 *
 */
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess_1'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/transitions'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/buzzNew'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/jquerycollagePlus'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/jqueryremoveWhitespace'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess_1'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/buzzNew'));
?>

<style type="text/css">
    .homeLink{
        padding: 2px 5px;
        background-color: #6B6B6B;
        border-radius: 5px;
    }
</style>
<script type="text/javascript">
    function activateTab(pageId) {
        var tabCtrl = document.getElementById('tabCtrl');
        var pageToActivate = document.getElementById(pageId);
        for (var i = 0; i < tabCtrl.childNodes.length; i++) {
            var node = tabCtrl.childNodes[i];
            if (node.nodeType == 1) { /* Element */
                node.style.display = (node == pageToActivate) ? 'block' : 'none';
            }
        }
    }
</script>
<div id="dashBoardBuzz">
    <div class="buzzRightBar">
        <!--Start Birthdays Component-->
        <div id="birthdayComponent">
            <?php include_component('buzz', 'viewBirthdays', array('employeeList' => $employeeList)); ?>
        </div>
        <!--End Birthdays Component-->

        <!--Start anniversary Component-->
        <div id="anniversaryComponent">
            <?php include_component('buzz', 'viewAnniversaries', array('anniversaryEmpList' => $anniversaryEmpList)); ?>
        </div>
        <!--End anniversary Component-->
        <!--Start Stat Component-->
        <div id="statisticsComponent">
            <?php // include_component('buzz', 'viewStatistics', array('loggedInUserId' => $loggedInUser));  ?>
        </div>
        <!--End Stat Component-->
        <!--Start Most Liked Shares Component-->
        <div id="statisticsComponent">
            <?php include_component('buzz', 'mostLikedShares', array()); ?>
        </div>
        <!--End Most Liked Shares Component-->
    </div>

    <div id="refreshTime" hidden="true" ><?php echo $refeshTime; ?></div>
    <div id="buzzContainer">
        <div id="spinner" class="spinner" style="display:none;">
            <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="70"   />
        </div>
        <div id="postTextBox">

            <ul id="tabLinks"style="overflow: hidden;border-top-right-radius: 5px;">
                <li id="tabLink1" onclick="activateTab('page1');" class="tabButton tb_one tabSelected">
                    <div>
                        <img id="status_icon"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/status.png"); ?>" height="25"   />
                        <span><a class="tabLabel" href="javascript:void(0)" style="font-size: 12px; font-weight: normal"/><?php echo __('Update Status'); ?></a></span>
                    </div>
                </li>
                <li id="tabLink2" onclick="activateTab('page2');" class="tabButton tb_two">
                    <img id="img_upld_icon"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/img.png"); ?>" height="25"   />
                    <!--This line was previously commented. This is the new button to activate the image uploading tab which is created below-->
                    <span><a class="tabLabel" href="javascript:void(0)" style="font-size: 12px; font-weight: normal"/><?php echo __('Upload Images'); ?></a></span>
                </li>
                <li id="tabLink3" onclick="activateTab('page3');" class="tabButton">
                    <img id="vid_upld_icon"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/vid.png"); ?>" height="25"   />
                    <!--This line was previously commented. This is the new button to activate the image uploading tab which is created below-->
                    <span><a class="tabLabel" href="javascript:void(0)" style="font-size: 12px; font-weight: normal"/><?php echo __('Share Video'); ?></a></span>
                </li>
            </ul>
            <div id="tabCtrl">
                <div id="page1">
                    <form id="frmPublishPost" method="" action="" 
                          enctype="multipart/form-data">
                        <fieldset>
                            <ol>
                                <?php echo $postForm->render(); ?>            
                            </ol>
                        </fieldset>
                    </form>
                    <div id="postLinkData" style="display:none;"  >
                        <div id="postBodySecondRow"  >
                            <a class="closeFeed" style="margin-left: 95%;margin-bottom: 5px">x</a>
                            <div id="postLinkState" hidden="true">not</div>
                            <div id="postLinkAddress" hidden="true"></div>
                            <p>
                                <a id="linkTitle" >titles</a> 
                            </p>
                            <p>
                            <div id="linkText">details</div>
                            </p>
                        </div>
                    </div>
                    <p>
                        <button id="postSubmitBtn" class="submitBtn">
                            <?php echo __("Post"); ?>
                        </button>
                    </p>
                </div>
                <div id="page2" style="display: none;">
                    <!--Image uploading tab-->
                    <form id="frmUploadImage" method="POST" action="" 
                          enctype="multipart/form-data">
                        <fieldset>
                            <ol>
                                <?php echo $uploadImageForm->render(); ?>            
                            </ol>
                        </fieldset>
                        <div id="imageThumbnails" style="display:inline-block; margin-top: 10px;">
                <span class="img_del" id="img_del_1" hidden="true" style="color: red; font-size: 9px; font-weight: 900; padding: 3px; background-color: black; position: relative; left: 17px; top: -5px;">X</span>
                <img height="70px" hidden="true" class="imgThumbnailView" id="thumb1" src="#" alt="your image" />
                <span class="img_del" id="img_del_2" hidden="true" style="color: red; font-size: 9px; font-weight: 900; padding: 3px; background-color: black; position: relative; left: 17px; top: -5px;">X</span>
                <img height="70px" hidden="true" class="imgThumbnailView" id="thumb2" src="#" alt="your image" />
                <span class="img_del" id="img_del_3" hidden="true" style="color: red; font-size: 9px; font-weight: 900; padding: 3px; background-color: black; position: relative; left: 17px; top: -5px;">X</span>
                <img height="70px" hidden="true" class="imgThumbnailView" id="thumb3" src="#" alt="your image" />
                <span class="img_del" id="img_del_4" hidden="true" style="color: red; font-size: 9px; font-weight: 900; padding: 3px; background-color: black; position: relative; left: 17px; top: -5px;">X</span>
                <img height="70px" hidden="true" class="imgThumbnailView" id="thumb4" src="#" alt="your image" />
                <span class="img_del" id="img_del_5" hidden="true" style="color: red; font-size: 9px; font-weight: 900; padding: 3px; background-color: black; position: relative; left: 17px; top: -5px;">X</span>
                <img height="70px" hidden="true" class="imgThumbnailView" id="thumb5" src="#" alt="your image" />
            </div>
                        
                        <p style="text-align: right">
                            <button type="submit" id="imageUploadBtn" class="submitBtn">
                                <?php echo __("Post"); ?>
                            </button>
                        </p>
                    </form>
                    

                </div>
                <div id="page3" style="display: none;">
                    <!--Image uploading tab-->
                    <div hidden="true" id="loadVideo" style="float: right;margin-right: 50px;margin-bottom: -20px;z-index: 200;">
                        <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/vload.gif"); ?>" 
                             height="20"  />
            
                    </div>
                    <form id="frmUploadVideo" method="POST" action="" 
                          enctype="multipart/form-data">
                        
                        <fieldset>
                            <ol>
                                <?php echo $videoForm->render(); ?>            
                            </ol>
                        </fieldset>
                        <p>

                        </p>
                    </form>
                    <div id="videoPostArea">
                        
                    </div>

                </div>
            </div>
            

            <div class="modal hide" id='imageCountError'>
                <div class="modal-body">
                    <?php echo __("Only five images are allowed to upload."); ?>
                </div>
            </div>

        </div>
        <div class="postLoadingBox" style="display: none">
            <div id="postBody">

                <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="70" style="margin-left: 40%; margin-top: 5px" />
            </div>
        </div>
        <ul id="buzz">
            <div class="jason"></div>

            <?php
            foreach ($postList as $post) {
                include_component('buzz', 'viewPost', array('post' => $post));
            }
            ?> 
        </ul>
        <div class="loadMoreBox" style="display: none">
            <div id="lodingGif" style="background: transparent">

                <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="70" style="margin-left: 40%; margin-top: 5px" />
            </div>
        </div>
        <div hidden="true" id="loadMorePosts">
            <!--<a href="javascript:void(0)" class="loadMorePostsLink" id=<?php echo $postId ?>><?php echo __("Load more posts"); ?></a>-->
        </div> 
        <div id="loggedInUserId" hidden="true"><?php echo $loggedInUser; ?></div>

        <script  type="text/javascript">
            $(window).scroll(function()
{
    
    if($(window).scrollTop() >= ($(document).height() - $(window).height()))
    {
        if ($('.loadMoreBox').css('display') == 'none') {
   $('.loadMoreBox').show();
        var lastPostId = {
            'lastPostId': $('#buzz .lastLoadedPost').last().attr('id')
        };
        $.ajax({
            url: loadNextSharesURL,
            type: "POST",
            data: lastPostId,
            success: function (data) {
                $('.loadMoreBox').hide();
                $('#buzz').append(data);
            }
        });
}
        
    }
});

            var getAccessUrl = '<?php echo url_for('buzz/getLogedToBuzz'); ?>';
            var getCsrfUrl = '<?php echo url_for('buzz/getFormCsrfToken'); ?>';
            var loginpageURL = '<?php echo url_for('auth/login'); ?>';
            var addNewVideo = '<?php echo url_for('buzz/addNewVideo'); ?>';
            var viewOriginalPost = '<?php echo url_for('buzz/viewPost'); ?>';
            var viewLikedEmployees = '<?php echo url_for('buzz/viewLikedEmployees'); ?>';
            var addBuzzPostURL = '<?php echo url_for('buzz/addNewPost'); ?>';
            var addBuzzCommentURL = '<?php echo url_for('buzz/addNewComment'); ?>';
            var shareLikeURL = '<?php echo url_for('buzz/likeOnShare'); ?>';
            var shareCommentURL = '<?php echo url_for('buzz/commentOnShare'); ?>';
            var shareShareURL = '<?php echo url_for('buzz/shareAPost'); ?>';
            var commentLikeURL = '<?php echo url_for('buzz/likeOnComment'); ?>';
            var shareDeleteURL = '<?php echo url_for('buzz/deleteShare'); ?>';
            var shareEditURL = '<?php echo url_for('buzz/editShare'); ?>';
            var commentDeleteURL = '<?php echo url_for('buzz/deleteComment'); ?>';
            var commentEditURL = '<?php echo url_for('buzz/editComment'); ?>';
            var loadNextSharesURL = '<?php echo url_for('buzz/loadNextShares'); ?>';
            var getLikedEmployeeListURL = '<?php echo url_for('buzz/getLikedEmployeeList'); ?>';
            var refreshPageURL = '<?php echo url_for('buzz/refreshPage'); ?>';
            var uploadImageURL = '<?php echo url_for('buzz/uploadImage'); ?>';
            var refreshStatsURL = '<?php echo url_for('buzz/viewStatistics'); ?>';</script>

        <script type="text/javascript">


            $(".tabButton").live("click", function () {
                $(".tabButton").removeClass('tabSelected');
                $(this).addClass('tabSelected');
            });

        </script>

    </div>

</div>


