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
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewShareSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewBuzzSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewProfileSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewprofileSuccess'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/buzzNew'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/buzzNew'));
?>
<div id="profileFullContainer">
    <div class="buzzProfileRightBar">
        <?php
        include_component('buzz', 'viewProfileDetails', array('employee' => $employee, 'logedInUserId' => $loggedInUser));
        ?> 
    </div>

    <div id="dashBoardProfile">

        <div id="refreshTime" hidden="true" ><?php echo $refeshTime ?></div>
        <div id="profileContainer">
            <ul id="profileBuzz">
                <div class="jason"></div>
                <?php
                foreach ($postListAsEmployee as $post) {
                    include_component('buzz', 'viewPost', array('post' => $post, 'loggedInUser' => $loggedInUser));
                }
                foreach ($postListAsAdmin as $post) {
                    include_component('buzz', 'viewPost', array('post' => $post, 'loggedInUser' => $loggedInUser));
                }
                ?> 
            </ul>
            <div class="loadMoreBox">
                <div id="lodingGif">
                    <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="70"/>
                </div>
            </div>

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
                    <div id="successBody" >SuccessFully Saved</div>
                </div>
            </div>
            <!--end loading window pop up window-->

            <div hidden="true" id="loadMorePosts">
                <a href="javascript:void(0)" class="loadMorePostsLink" id=<?php echo $postId ?>><?php echo __("Load more posts"); ?></a>
            </div> 
            <div id="profileUserId" hidden="true"><?php echo $profileUserId; ?></div>
            <form id="actionValidateForm" method="POST" action="" 
                  enctype="multipart/form-data">
                <fieldset>
                    <ol>
                        <?php echo $actionValidateForm->render(); ?>            
                    </ol>
                </fieldset>

            </form>

            <script  type="text/javascript">

                var viewMoreShare = '<?php echo url_for('buzz/viewShare'); ?>';
                var profilePage = '<?php echo url_for('buzz/viewProfile?empNumber='); ?>';
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
                var loadNextSharesURL = '<?php echo url_for('buzz/loadMoreProfile'); ?>';
                var getLikedEmployeeListURL = '<?php echo url_for('buzz/getLikedEmployeeList'); ?>';
                var refreshPageURL = '<?php echo url_for('buzz/refreshProfile'); ?>';
                var uploadImageURL = '<?php echo url_for('buzz/uploadImage'); ?>';
                var getAccessUrl = '<?php echo url_for('buzz/getLogedToBuzz'); ?>';
                var refreshStatsURL = '<?php echo url_for('buzz/viewStatistics'); ?>';
                var viewMoreShare = '<?php echo url_for('buzz/viewShare'); ?>';
                var getSharedEmployeeListURL = '<?php echo url_for('buzz/getSharedEmployeeList'); ?>';
            </script>

        </div>
    </div>
</div>
