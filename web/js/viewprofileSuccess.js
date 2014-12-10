$(document).ready(function () {


    var noOfPhotosPreviewed = 1;
    $("#photofile").change(function () {
        if (noOfPhotosPreviewed > 5) {
            alert("No more!");
        }
        var files = $("#photofile")[0].files;
        var imagesChoosed = $("#photofile")[0].files.length;
        for (var i = 1; i <= imagesChoosed; i++) {
            readURL(files[i - 1], noOfPhotosPreviewed);
            noOfPhotosPreviewed++;
        }

    });

    $("#frmUploadImage").on("submit", function (e) {
        e.preventDefault();
        var imageFiles = $("#photofile")[0].files;
        var photoText = $("#phototext").val();
        var formData = new FormData();
        var str = "";
        if (imageFiles.length > 5) {
            //Handel proper validation here...
            alert("Max");
            return;
        }
        $.each(imageFiles, function (k, v) {
            formData.append(k, v);
        });
        formData.append('postContent', photoText);

//        formData.append('photos[]', imageFile, imageFile.name);
//        var postData = {
//            'formData': formData,
//            '_csrf_token': $('#photofile_csrf_token').val()
//        };
//
        $.ajax({
            url: uploadImageURL,
            type: "POST",
            data: formData,
            processData: false, // Don't process the files
            contentType: false,
            success: function (data) {
                $('#buzz').prepend(data);
            }
        });
    });

    function reload() {

        var lastPostId = {
            'lastPostId': $('#profileBuzz .lastLoadedPost').last().attr('id'),
            'profileUserId': trim($('#profileUserId').html())
        };
        $.ajax({
            url: refreshPageURL,
            type: "POST",
            data: lastPostId,
            success: function (data) {
                $('#profileBuzz').replaceWith(data);
            }
        });
    }

    $(".loadMorePostsLink").live("click", function (e) {

        var lastPostId = {
            'lastPostId': $('#profileBuzz .lastLoadedPost').last().attr('id'),
            'profileUserId': trim($('#profileUserId').html())
        };
        $.ajax({
            url: loadNextSharesURL,
            type: "POST",
            data: lastPostId,
            success: function (data) {
                $('#profileBuzz').append(data);
            }
        });

    });

    $(".postViewMoreCommentsLink").live("click", function (e) {
        var postId = e.target.id.split("_")[1];
        $("#" + e.target.id).hide(100);
//        $("#" + postId).css("display", "block");
        $("." + postId).filter(function () {
            return ($(this).css("display") == "none")
        }).show(80);

    });


    $(".nfPostCommentLink").live("click", function (e) {
        $("#nfPostCommentTextBox" + e.target.id).toggle(300);
    });

    /**
     * Liking a share
     */
//    $(".postLike").live("click", function (e) {
//        var idValue = e.target.id;
//        var id = "#" + idValue;
//        var likeLabelId = "#postNoOfLikes_" + idValue.split("_")[1];
//        var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
//        var action = "";
//        if ($(id).attr('class') == "Like postLike") {
////            $(id).html("Unlike");
//            $(id).attr('class', "Unlike postLike");
//            action = "like";
//            $(likeLabelId).html((existingLikes + 1) + " " + "people likes this");
//        } else {
////            $(id).html("Like");
//            $(id).attr('class', "Like postLike");
//            action = "unlike";
//            $(likeLabelId).html((existingLikes - 1) + " " + "people likes this");
//        }
//        $.ajax({
//            url: shareLikeURL + "?shareId=" + idValue.split("_")[1] + "&likeAction=" + action,
//            success: function (data) {
//                $(id).html(data);
//            }
//        });
//    });

    /**
     * Liking a comment
     */
//    $(".commentLike").live("click", function (e) {
//        var idValue = e.target.id;
//        var id = "#" + idValue;
//        var likeLabelId = "#commentNoOfLikes_" + idValue.split("_")[1];
//        var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
//        var action = "";
//        if ($(id).html() == "Like") {
//            $(id).html("Unlike");
//            action = "like";
//            $(likeLabelId).html((existingLikes + 1) + " " + "likes");
//        } else {
//            $(id).html("Like");
//            action = "unlike";
//            $(likeLabelId).html((existingLikes - 1) + " " + "likes");
//        }
//        $.ajax({
//            url: commentLikeURL + "?commentId=" + idValue.split("_")[1] + "&likeAction=" + action,
//            success: function (data) {
//                $(id).html(data);
//            }
//        });
//    });


    $(".nfShowComments").live("click", function (e) {
        var idValue = e.target.id;
        if ($("#" + idValue).html() == "Show Comments") {
            $("#" + idValue).html("Hide Comments");
        } else {
            $("#" + idValue).html("Show Comments");
        }
        idValue = idValue.split("_")[1];
        $("#commentList" + idValue).toggle(300);

    });

    $(".postNoofLikesTooltip").live("hover", function (e) {
        var idValue = e.target.id;
        var shareId = idValue.split("_")[1];
        var data = {
            'id': shareId,
            'type': 'post'
        };
        $.ajax({
            url: getLikedEmployeeListURL,
            type: "POST",
            data: data,
            success: function (data) {
                $('#' + idValue).attr('title', '');
                $('#' + idValue).attr('title', data);
            }
        });
    });

    $(".commentNoofLikesTooltip").live("hover", function (e) {
        var idValue = e.target.id;
        var commentId = idValue.split("_")[1];
        var data = {
            'id': commentId,
            'type': 'comment'
        };
        $.ajax({
            url: getLikedEmployeeListURL,
            type: "POST",
            data: data,
            success: function (data) {
                $('#' + idValue).attr('title', '');
                $('#' + idValue).attr('title', data);
            }
        });
    });


    /**
     * Commenting on a share.
     */
//    $(".commentBox").live("keydown", function (e) {
//        if (e.keyCode == 13 && !e.shiftKey) {
//            var elementId = "#" + e.target.id;
//            var value = $(elementId).val();
//            value = $.trim(value.replace(/[\t\n]+/g, ' '));
//            $(elementId).val('');
//            $(elementId).attr('placeholder', 'Write your comment...');
//            var commentListId = "#commentList_" + elementId.split("_")[1];
//            var data = {
//                'commentText': value,
//                'shareId': elementId.split("_")[1]
//            };
//            $.ajax({
//                url: addBuzzCommentURL,
//                type: 'POST',
//                data: data,
//                success: function (data) {
//                    $(commentListId).append(data);
//                }
//            });
//        }
//    });

    /**
     * Option widget
     */

//    $(".account").live("click", function (e)
//    {
//        var elementId = "#submenu" + e.target.id;
//        $(elementId).toggle(100);
//    });
//
//    $(".commentAccount").live("click", function (e)
//    {
//        var elementId = "#submenu" + e.target.id;
//        $(elementId).toggle(100);
//    });
//
////Mouse click on sub menu
//    $(".submenu").mouseup(function ()
//    {
//        return false;
//    });
//
////Mouse click on my account link
//    $(".account").mouseup(function ()
//    {
//        return false;
//    });
//
//
////Document Click
//    $(document).mouseup(function ()
//    {
//        $(".submenu").hide();
//        //$(".account").attr('id', '');
//    });
//



    /**
     * Edit share form pop up 
     */

    /**
     * save edited form
     */




//    $(".deleteComment").live("click", function (e) {
//        var commentId = e.target.id.split("_")[1];
//        $.ajax({
//            url: commentDeleteURL + "?commentId=" + commentId,
//            success: function (data) {
//                $("#comment_" + commentId).remove();
//            }
//        });
//    });

    /**
     * share post save 
     */
    $(".btnShare").live("click", function (e) {
        var idValue = e.target.id;
        $("#posthide_" + idValue.split("_")[1]).modal('hide');

        var share = $("#shareBox_" + idValue.split("_")[1]).val();
        $.ajax({
            url: shareShareURL + "?postId=" + idValue.split("_")[2] + "&textShare=" + share,
            success: function (data) {
                $('#buzz').prepend(data);
                reload();
                setTimeout(refresh, 10000);
            }
        });




    });
    /**
     * share post popup window view
     */
//    $(".postShare").live("click", function (e) {
//        var idValue = e.target.id;
//        $("#posthide_" + idValue.split("_")[1]).modal();
//
//
//
//    });
//
//    $(".editComment").live("click", function (e) {
//        var commentId = e.target.id.split("_")[1];
//        $("#editcommenthide_" + commentId).modal();
//    });
//    $(".btnEditComment").live("click", function (e) {
//        var commentId = e.target.id.split("_")[1];
//        var content = $("#editcommentBox_" + commentId).val();
//        $("#editcommenthide_" + commentId).modal('hide');
//
//
//        $.ajax({
//            url: commentEditURL + "?commentId=" + commentId + "&textComment=" + content,
//            success: function (data) {
//                $("#commentContent_" + commentId).replaceWith(data);
//                reload();
//                setTimeout(refresh, 10000);
//            }
//        });
//
//
//    });
//    /**
//     * view liked employee list for comment
//     */
//    $(".postNoofLikesTooltip").live("click", function (e) {
//        var idValue = e.target.id;
//        var shareId = idValue.split("_")[1];
//        var likeLabelId = "#postNoOfLikes_" + idValue.split("_")[1];
//        var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
//        if (existingLikes > 0) {
//            var action = "post"
//
//            var data = {
//                'id': shareId,
//                'actions': action
//            };
//            $.ajax({
//                url: viewLikedEmployees,
//                type: 'POST',
//                data: data,
//                success: function (data) {
//                    $("#postlikehidebody_" + shareId).replaceWith(data);
//                }
//            });
//
//
//            $("#postlikehide_" + shareId).modal();
//        }
//    });
//    /**
//     * hide liked employee list
//     */
//    $(".btnBackHide").live("click", function (e) {
//        var idValue = e.target.id;
//        var shareId = idValue.split("_")[1];
//        $("#postlikehide_" + shareId).modal('hide');
//    });
//
//    /**
//     * view like employee list for comment
//     */
//    $(".commentNoofLikesTooltip").live("click", function (e) {
//        var idValue = e.target.id;
//        var commentId = idValue.split("_")[1];
//        var likeLabelId = "#commentNoOfLikes_" + idValue.split("_")[1];
//        var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
//
//        if (existingLikes > 0) {
//            var action = "comment"
//            var data = {
//                'id': commentId,
//                'actions': action
//            };
//            $.ajax({
//                url: viewLikedEmployees,
//                type: 'POST',
//                data: data,
//                success: function (data) {
//                    $("#commentlikehidebody_" + commentId).replaceWith(data);
//                }
//            });
//            $("#commentlikehide_" + commentId).modal();
//        }
//    });
//    /**
//     * hide liked employee list for comment
//     */
//    $(".btnBackHideComment").live("click", function (e) {
//        var idValue = e.target.id;
//        var commentId = idValue.split("_")[1];
//        $("#commentlikehide_" + commentId).modal('hide');
//    });
//    $("#spinner").bind("ajaxSend", function () {
//        $(this).show();
//    }).bind("ajaxStop", function () {
//        $(this).hide();
//    }).bind("ajaxError", function () {
//        $(this).hide();
//    });
//    var time = new Date().getTime();
//    $(document.body).bind("mousemove keypress", function (e) {
//        time = new Date().getTime();
//    });

    /**
     * original post view
     */
//    $(".originalPostView").live("click", function (e) {
//        var idValue = e.target.id;
//        var shareId = idValue.split("_")[1];
//        var postId = idValue.split("_")[2];
//        var data = {
//            'postId': postId,
//        };
//        $.ajax({
//            url: viewOriginalPost,
//            type: "POST",
//            data: data,
//            success: function (data) {
//
//                $('#postViewContent_' + shareId).replaceWith(data);
//                $('#postViewOriginal_' + shareId).modal();
//            }
//        });
//    });

//    function refresh() {
//        var refreshTime = trim($("#refreshTime").html());
//
//        if (new Date().getTime() - time >= refreshTime) {
//            //$('#spinner').show();
////            $('#buzz').remove();
////            alert("fhhe");
//            reload();
//            setTimeout(refresh, refreshTime);
//        } else {
//
//            setTimeout(refresh, refreshTime);
//        }
//
//    }
//
//    setTimeout(refresh, refreshTime);

}
);

$(window).scroll(function ()
{

    if ($(window).scrollTop() >= ($(document).height() - $(window).height()))
    {
        if ($('.loadMoreBox').css('display') == 'none') {
            $('.loadMoreBox').show();
            var lastPostId = {
                'lastPostId': $('#profileBuzz .lastLoadedPost').last().attr('id'),
                'profileUserId': trim($('#profileUserId').html())
            };
            
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