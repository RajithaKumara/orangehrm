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
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewBuzzSuccess_1'));
use_stylesheet(plugin_web_path('orangehrmBuzzPlugin', 'css/viewProfileSuccess'));
use_javascript(plugin_web_path('orangehrmBuzzPlugin', 'js/viewprofileSuccess'));
?>
<div class="buzzProfileRightBar">
<?php
include_component('buzz', 'viewProfileDetails', array('employee' => $employee,'logedInUserId'=> $loggedInUser));
?> 
</div>
<script type="text/javascript">
    function isAccess() {
                    $.getJSON(getAccessUrl, {get_param: 'value'}, function (data) {

                        if (data.state === 'loged') {

                        } else {
                            Redirect();
                        }
                    });
                }
    $(document).ready(function () {
        $(".postLike").live("click", function (e) {
            isAccess();
            var idValue = e.target.id;
            var id = "postLikebody_" + trim(idValue.split("_")[1]);
            var div = document.getElementById(id);
            var id2 = "postUnLikebody_" + trim(idValue.split("_")[1]);
            var div2 = document.getElementById(id2);
            var likeLabelId = "#postNoOfLikes_" + idValue.split("_")[1];
            //var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
            var action = "like";
            $.getJSON(shareLikeURL + "?shareId=" + idValue.split("_")[1] + "&likeAction=" + action,
                    {get_param: 'value'}, function (data) {
                if (data.states === 'savedLike') {
                    var likes = trim($('#postLiketext_' + idValue.split("_")[1]).html());
                    likes++;
                    $('#postLiketext_' + idValue.split("_")[1]).html(likes);
                    div.style.backgroundColor = 'orange';
                }if(data.deleted==='yes'){
                var likes= trim($('#postUnLiketext_'+idValue.split("_")[1]).html());
                likes--;
                $('#postUnLiketext_'+idValue.split("_")[1]).html(likes);
                
            }
                div2.style.backgroundColor = 'black';
            });


        });
        $(".postUnlike2").live("click", function (e) {

            isAccess();

            var idValue = e.target.id;

            var id = "postLikebody_" + trim(idValue.split("_")[1]);
            var div = document.getElementById(id);
            var id2 = "postUnLikebody_" + trim(idValue.split("_")[1]);
            var div2 = document.getElementById(id2);
            var likeLabelId = "#postNoOfLikes_" + idValue.split("_")[1];
            //var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
            var action = "unlike";
            $.getJSON(shareLikeURL + "?shareId=" + idValue.split("_")[1] + "&likeAction=" + action,
                    {get_param: 'value'}, function (data) {
                       if(data.deleted==='yes'){
                var likes= trim($('#postLiketext_'+idValue.split("_")[1]).html());
                likes--;
                $('#postLiketext_'+idValue.split("_")[1]).html(likes);
                
            }
            alert(data.states);
            if(data.states==='savedUnLike'){
                var likes= trim($('#postUnLiketext_'+idValue.split("_")[1]).html());
                likes++;
                $('#postUnLiketext_'+idValue.split("_")[1]).html(likes);
                
            }
                div2.style.backgroundColor = 'red';


                div.style.backgroundColor = 'black';
            });
        });

        $(".commentLike").live("click", function (e) {
            isAccess();
            var idValue = e.target.id;
            var idValue = e.target.id;
            var id = "commentLikebody_" + trim(idValue.split("_")[1]);
            var div = document.getElementById(id);
            var id2 = "commentUnLikebody_" + trim(idValue.split("_")[1]);
            var div2 = document.getElementById(id2);
            var likeLabelId = "#commentNoOfLikes_" + idValue.split("_")[1];
            //var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
            var action = "like";
            $.getJSON(commentLikeURL + "?commentId=" + idValue.split("_")[1] + "&likeAction=" + action,
                    {get_param: 'value'}, function (data) {
                if (data.states === 'savedLike') {
                    var likes = trim($('#commentLiketext_' + idValue.split("_")[1]).html());
                    likes++;
                    $('#commentLiketext_' + idValue.split("_")[1]).html(likes);
                    div.style.backgroundColor = 'orange';
                }
                if(data.deleted==='yes'){
                var likes= trim($('#commentUnLiketext_'+idValue.split("_")[1]).html());
                likes--;
                $('#commentUnLiketext_'+idValue.split("_")[1]).html(likes);
                
            }
                div2.style.backgroundColor = 'black';
            });

        });
        $(".commentUnlike2").live("click", function (e) {

            isAccess();

            var idValue = e.target.id;

            var id = "commentLikebody_" + trim(idValue.split("_")[1]);
            var div = document.getElementById(id);
            var id2 = "commentUnLikebody_" + trim(idValue.split("_")[1]);
            var div2 = document.getElementById(id2);
            var likeLabelId = "#commentNoOfLikes_" + idValue.split("_")[1];
            //var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
            var action = "unlike";
            $.getJSON(commentLikeURL + "?commentId=" + idValue.split("_")[1] + "&likeAction=" + action,
                    {get_param: 'value'}, function (data) {
                         if(data.deleted==='yes'){
                var likes= trim($('#commentLiketext_'+idValue.split("_")[1]).html());
                likes--;
                $('#commentLiketext_'+idValue.split("_")[1]).html(likes);
                
            }
            if(data.states==='savedUnLike'){
                var likes= trim($('#commentUnLiketext_'+idValue.split("_")[1]).html());
                likes++;
                $('#commentUnLiketext_'+idValue.split("_")[1]).html(likes);
                
            }
                div2.style.backgroundColor = 'red';

                div.style.backgroundColor = 'black';
            });
        });
    });
</script>
<style type="text/css">
    .buzzLeftBar{
        width: 26%;
        float: left;
        position: fixed;
        left: 2%;
        top: 8%;
    }
    #gotoProfile{
        font-size: 11.6px;
    }
   
    #dashBoardProfile{
        /*border: 2px solid black;*/
        width: 75%;
        margin: 60px auto;
    }
    .buzzLeftBar{
        /*        width: 26%;
                float: left;
                margin-top: 2%;*/
        width: 20%;
        margin-left: 76%;
        float: left;
        margin-top: 1%;
        z-index: 999;
        position: fixed;
    }

    .buzzProfileRightBar{
        width: 23.75%;
        float: right;
        position: fixed;
        right: 11.5%;
        top: 16%;
        /*        width: 26%;*/
        /*float: left;*/
        /*        position: fixed;
                left: 2%;
                top: 16%;*/
        /*clear: both;*/
    }

    #profileContainer{
        width: 65.25%;
        float: left;
    }
    
    li{
        list-style: none;
    }
    #profileFirstRow{
    width: 80%;
    
    background-color: #484343;
    color: #fff;
    text-align: left;
    padding: 5px;
    -webkit-box-shadow: 1px 1px 1px 1px rgba(140,140,140,1);
    -moz-box-shadow: 1px 1px 1px 1px rgba(140,140,140,1);
    font-size: 18px;
}
</style>
<style type="text/css">
    .searchformupper{
        margin-top: -30px;
        margin-left: 60%;
        
    }
</style>

        
<div id="dashBoardProfile">

    <div id="refreshTime" hidden="true" ><?php echo $refeshTime ?></div>

    

    <div id="profileContainer">
        
        <div id="spinner" class="spinner" style="display:none;">
            <!--<img id="img-spinner"   src="<?php // echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif");  ?>" height="70"   />-->
        </div>

        <ul id="profileBuzz">
            <div class="jason"></div>
            <?php
            foreach ($postList as $post) {

                include_component('buzz', 'viewPost', array('post' => $post));
            }
            ?> 

        </ul>
        <div class="loadMoreBox" style="display: none">
            <div id="postBody">

                <img id="img-spinner"   src="<?php echo plugin_web_path("orangehrmBuzzPlugin", "images/loading2.gif"); ?>" height="70" style="margin-left: 40%; margin-top: 5px" />
            </div>
        </div>
        <div id="loadMorePosts">
            <a href="javascript:void(0)" class="loadMorePostsLink" id=<?php echo $postId ?>><?php echo __("Load more posts"); ?></a>
        </div> 
        <div id="profileUserId" hidden="true"><?php echo $profileUserId; ?></div>
        <script type="text/javascript">
            $(document).ready(function () {
                

                $("#gotoProfile").click(function () {
                    var id = $('#searchChatter_emp_name_empId').val();
                    if (id.length <= 0) {
                        alert('select User');
                    } else {
                        window.location = profilePage + id;
                    }
                });
                $(".btnSaveVideo").live('click', function (e) {
                    var code = trim($("#yuoutubeVideoId").html());

                    var text = $("#shareVideo").val();
                    var data = {
                        'url': code,
                        'actions': 'save',
                        'text': text
                    };
                    $.ajax({
                        url: addNewVideo,
                        type: "POST",
                        data: data,
                        success: function (data) {
                            $('#tempVideoBlock').remove();
                            $('#buzz').prepend(data);
                            $("#frmUploadVideo").show();
                        }
                    });
                });
                $("#createVideo_content").bind({
                    copy: function () {

                    },
                    paste: function (e) {
                        e.preventDefault();
                        var url = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');

                        var data = {
                            'url': url,
                            'actions': 'paste',
                            'text': 'no'
                        };
                        $.ajax({
                            url: addNewVideo,
                            type: "POST",
                            data: data,
                            success: function (data) {
                                $("#frmUploadVideo").hide();
                                $('#videoPostArea').replaceWith(data);
                            }
                        });
                    },
                    cut: function () {

                    }

                });
                $("#createPost_content").bind({
                    paste: function (e) {

                        e.preventDefault();
                        var url = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');

                        var text = $("#createPost_content").val() + url;
                        $("#createPost_content").val(text);
                        $("#postLinkState").html('no');
                        Redirect();
                        $.ajax({
                            url: document.location.protocol + '//ajax.googleapis.com/ajax/services/feed/load?v=1.0&num=10&callback=?&q=' + encodeURIComponent(url),
                            dataType: 'json',
                            success: function (data) {
                                if (data.responseData) {
                                    if (data.responseData.feed && data.responseData.feed.entries) {
                                        var char = 1;
                                        $.each(data.responseData.feed.entries, function (i, e) {
                                            if (char === 1) {
                                                $("#linkTitle").html(e.title);
                                                console.log("description: " + e.description);
                                                $("#linkText").html(e.description);
                                                $("#postLinkData").show();
                                                $("#postLinkState").html('yes');
                                                char = 2;
                                            }
                                        });
                                    }
                                } else {
                                    $("#postLinkData").hide();
                                    $("#postLinkState").html('no');
                                }
                            }
                        });



                    }
                });
            });

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
        <script  type="text/javascript">
            $(".closeFeed").live("click", function (e) {
                $("#postLinkData").hide();
                $("#postLinkState").html('no');
            });
            $(".originalPostView").live("click", function (e) {
                var idValue = e.target.id;
                var shareId = idValue.split("_")[1];
                var postId = idValue.split("_")[2];
                var data = {
                    'postId': postId,
                };
                $.ajax({
                    url: viewOriginalPost,
                    type: "POST",
                    data: data,
                    success: function (data) {

                        $('#postViewContent_' + shareId).replaceWith(data);
                        $('#postViewOriginal_' + shareId).modal();
                    }
                });
            });


            function Redirect()
            {
                var state = trim($('#redirectState').html());
                if (state === 'yes') {
                    window.location = loginpageURL;
                } else {
                    $('#redirectState').remove();
                }
            }
            
            $(window).scroll(function()
{
    
    if($(window).scrollTop() >= ($(document).height() - $(window).height()))
    {
        if ($('.loadMoreBox').css('display') == 'none') {
        $('.loadMoreBox').show();
         var lastPostId = {
            'lastPostId': $('#profileBuzz .lastLoadedPost').last().attr('id'),
            'profileUserId': trim($('#profileUserId').html())
        };
        alert($('#profileBuzz .lastLoadedPost').last().attr('id'));
        alert($('#profileUserId').html());
        alert(loadNextSharesURL);
        $.ajax({
            url: loadNextSharesURL,
            type: "POST",
            data: lastPostId,
            success: function (data) {
                $('#profileBuzz').append(data);
            }
        });
}
        
    }
});

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
        </script>

    </div>
</div>
