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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/transitions'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/buzzNew'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/jquerycollagePlus'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/jqueryremoveWhitespace'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/buzzNew'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/getSharedEmployeeListSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewPostComponent'));
?>

<div id="dashBoardBuzz">
    <div class="buzzRightBar">
        <!--Start Birthdays Component-->
        <div id="birthdayComponent">
            <?php include_component('buzz', 'viewBirthdays', array()); ?>
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
        <div id="spinner" class="spinner">
            <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="70"   />
        </div>
        <div id="postTextBox">
            <ul id="tabLinks">
                <li id="tabLink1" onclick="activateTab('page1');" class="tabButton tb_one tabSelected">
                    <div>
                        <img id="status_icon" src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/status2.png"); ?>" height="25"   />
                        <span><a class="tabLabel" href="javascript:void(0)"/><?php echo __('Update Status'); ?></a></span>
                    </div>
                </li>
                <li id="tabLink2" onclick="activateTab('page2');" class="tabButton tb_two">
                    <img id="img_upld_icon"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/img.png"); ?>" height="25"   />
                    <!--This line was previously commented. This is the new button to activate the image uploading tab which is created below-->
                    <span><a class="tabLabel" href="javascript:void(0)"/><?php echo __('Upload Images'); ?></a></span>
                </li>
                <li id="tabLink3" onclick="activateTab('page3');" class="tabButton">
                    <img id="vid_upld_icon"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/vid.png"); ?>" height="25"   />
                    <!--This line was previously commented. This is the new button to activate the image uploading tab which is created below-->
                    <span><a class="tabLabel" href="javascript:void(0)"/><?php echo __('Share Video'); ?></a></span>
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
                    <div id="postLinkData">
                        <div id="postBodySecondRow"  >
                            <a class="closeFeed">x</a>
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
                <div id="page2">
                    <!--Image uploading tab-->
                    <form id="frmUploadImage" method="POST" action="" 
                          enctype="multipart/form-data">
                        <fieldset>
                            <ol>
                                <?php echo $uploadImageForm->render(); ?>            
                            </ol>
                        </fieldset>
                        <div id="imageThumbnails">
                            <span class="img_del" id="img_del_1" hidden="true">X</span>
                            <img height="70px" hidden="true" class="imgThumbnailView" id="thumb1" src="#" alt="your image" />
                            <span class="img_del" id="img_del_2" hidden="true">X</span>
                            <img height="70px" hidden="true" class="imgThumbnailView" id="thumb2" src="#" alt="your image" />
                            <span class="img_del" id="img_del_3" hidden="true">X</span>
                            <img height="70px" hidden="true" class="imgThumbnailView" id="thumb3" src="#" alt="your image" />
                            <span class="img_del" id="img_del_4" hidden="true">X</span>
                            <img height="70px" hidden="true" class="imgThumbnailView" id="thumb4" src="#" alt="your image" />
                            <span class="img_del" id="img_del_5" hidden="true">X</span>
                            <img height="70px" hidden="true" class="imgThumbnailView" id="thumb5" src="#" alt="your image" />
                        </div>
                        <p id="imgUpBtnPara">
                            <button type="submit" id="imageUploadBtn" class="submitBtn">
                                <?php echo __("Post"); ?>
                            </button>
                            <a href="#" id="clearLink">CLEAR</a>
                        </p>
                    </form>
                </div>
                <div id="page3">
                    <!--Image uploading tab-->
                    <div hidden="true" id="loadVideo">
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

                <div class="modal-body originalPostModal-body" >
                    <div class="hideModalPopUp" id='<?php echo 'postViewOriginal_' . $postId ?>'
                         ><img 
                            class="hideModalPopUp" id='<?php echo 'postViewOriginal_' . $postId ?>' 
                            src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/close.png"); ?>" height="20" width="20"
                            />
                    </div>
                    <div class="modal-body">
                        <div id="maxImageErrorHeading">
                            <?php echo __("Sorry!"); ?>
                        </div>
                        <br>
                        <div id="maxImageErrorBody">
                            <?php echo __("Only five images are allowed in a single post!"); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="postLoadingBox">
            <div id="postBody">
                <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="70" />
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

        <!--start loading window popup window-->
        <div class="modal hide" id="loadingDataModal" >
            <div class="modal-body loadingDataModal-body" >            
                <div id="loadingModalBody" >
                    <img id="img-spinner-loading"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/vload.gif"); ?>" 
                         height="12"  />
                </div>
            </div>
        </div>
        <!--end loading window pop up window-->
        <!--start Success popup window-->
        <div class="modal hide" id="successDataModal" >

            <div class="modal-body successDataModal-body" >

                <!--<div id="successHeader" style="width: 100%;height: 20px;background-color: green;">Success</div>-->
                <div id="successBody" >SuccessFully Saved</div>

            </div>
        </div>
        <!--end loading window pop up window-->
        <div class="loadMoreBox">
            <div id="lodingGif">
                <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="70" />
            </div>
        </div>

        <div id="loggedInUserId" hidden="true"><?php echo $loggedInUser; ?></div>

        <script  type="text/javascript">
            var getAccessUrl = '<?php echo url_for('buzz/getLogedToBuzz'); ?>';
            var getCsrfUrl = '<?php echo url_for('buzz/getFormCsrfToken'); ?>';
            var loginpageURL = '<?php echo url_for('auth/login'); ?>';
            var addNewVideo = '<?php echo url_for('buzz/addNewVideo'); ?>';
            var viewOriginalPost = '<?php echo url_for('buzz/viewPost'); ?>';
            var viewMoreShare = '<?php echo url_for('buzz/viewShare'); ?>';
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
            var refreshStatsURL = '<?php echo url_for('buzz/viewStatistics'); ?>';
            var getSharedEmployeeListURL = '<?php echo url_for('buzz/getSharedEmployeeList'); ?>';
            var imageFolderPath = '<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/"); ?>';
        </script>
        <style type="text/css">
            .homeLink{
                padding: 2px 5px;
                background-color: #6B6B6B;
                border-radius: 5px;
            }
        </style>

    </div>

</div>


