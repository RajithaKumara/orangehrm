var modalVisible = false;
$(document).ready(function () {
    /**
     * Submitting a new post
     */
    $("#postSubmitBtn").live("click", function () {
        isAccess();
        var x = $("#createPost_content").val();
        if (x == null || trim(x).length < 1) {
            alert("First write some thing ");

        } else {
            $('.postLoadingBox').show();
            var token;
            $.getJSON(getCsrfUrl, {get_param: 'value'}, function (data) {
                token = data.post;

                var postData = {
                    'content': $("#createPost_content").val(),
                    '_csrf_token': token,
                    'postLinkState': trim($('#postLinkState').html()),
                    'postLinkAddress': trim($('#postLinkAddress').html()),
                    'linkTitle': trim($('#linkTitle').html()),
                    'linkText': trim($('#linkText').html()),
                };

                $("textarea").val('');
                $.ajax({
                    url: addBuzzPostURL,
                    type: "POST",
                    data: postData,
                    success: function (data) {

                        $('#buzz').prepend(data);
                        $('.postLoadingBox').hide();
                        $("#postLinkData").hide();
                        $("#postLinkState").html('no');

                    }
                });


            });


        }
    });

    var formData = new FormData();
    var imageList = {};
    function readURL(file, thumbnailDivId) {

        if (file) {

            var reader = new FileReader();
            reader.readAsDataURL(file);
            imageList[thumbnailDivId] = file;
//            formData.append(thumbnailDivId, file);
            reader.onload = function (e) {
                $('#thumb' + thumbnailDivId).attr('hidden', false);
                $('#img_del_' + thumbnailDivId).attr('hidden', false);
                $('#thumb' + thumbnailDivId).attr('src', e.target.result);
            };
        }
    }
    var noOfPhotosPreviewed = 1;
    $("#photofile").change(function () {
        if (noOfPhotosPreviewed > 5) {
            $("#imageCountError").modal();
            return;
        }
        var files = $("#photofile")[0].files;
        var imagesChoosed = $("#photofile")[0].files.length;
        if (imagesChoosed > 5) {
            $("#imageCountError").modal();
            return;
        }
        for (var i = 1; i <= imagesChoosed; i++) {
            readURL(files[i - 1], noOfPhotosPreviewed);
            noOfPhotosPreviewed++;
        }

    });

    $(".img_del").live('click', function () {
        var id = $(this).attr('id').split("_")[2];
        $('#thumb' + id).attr('hidden', true);
        $('#thumb' + id).attr('src', null);
        delete imageList[id];
        $(this).attr('hidden', 'true');
    });

    $("#frmUploadImage").on("submit", function (e) {
        isAccess();
        activateTab('page1');
        $("#tabLink1").attr("class", "tabButton tb_one tabSelected");
        $("#tabLink2").removeClass("tabSelected");
        noOfPhotosPreviewed = 1;
        e.preventDefault();
        var imageFiles = $("#photofile")[0].files;
        var photoText = $("#phototext").val();

        var str = "";
        if (imageFiles.length > 5) {
            //Handel proper validation here...
            alert("Max");
            return;
        }
        $('.postLoadingBox').show();
        $.each(imageFiles, function (k, v) {
//            formData.append(k, v);
        });
        for (var key in imageList) {
            formData.append(key, imageList[key]);
        }
        formData.append('postContent', photoText);

//        formData.append('photos[]', imageFile, imageFile.name);
//        var postData = {
//            'formData': formData,
//            '_csrf_token': $('#photofile_csrf_token').val()
//        };
//
        var fileInput = $("#photofile");

        $.ajax({
            url: uploadImageURL,
            type: "POST",
            data: formData,
            processData: false, // Don't process the files
            contentType: false,
            success: function (data) {
                $('#buzz').prepend(data);
                $('.postLoadingBox').hide();
                $(".imgThumbnailView").attr("hidden", "true");
                fileInput.replaceWith(fileInput = fileInput.clone(true));
                $(".img_del").attr('hidden', 'true');
                imageList = {};
                formData = new FormData();
            }
        });
    });

    $("#gotoProfile").click(function () {
        var id = $('#searchChatter_emp_name_empId').val();
        if (id.length <= 0) {
            alert('select User');
        } else {
            window.location = profilePage + id;
        }
    });

    function reload() {
        isAccess();
        var lastPostId = {
            'lastPostId': $('#buzz .lastLoadedPost').last().attr('id')
        };
        $.ajax({
            url: refreshPageURL,
            type: "POST",
            data: lastPostId,
            success: function (data) {
                $('#buzz').replaceWith(data);
            }
        });

        refreshStatComponent();
    }

    function refreshStatComponent() {
        isAccess();
        var loggedInUserId = {
            'loggedInUserId': $('#loggedInUserId').val()
        };
        $.ajax({
            url: refreshStatsURL,
            type: "POST",
            data: loggedInUserId,
            success: function (data) {
                $('#statTable').replaceWith(data);
            }
        });
    }

    $(".loadMorePostsLink").live("click", function (e) {
        isAccess();
//        alert($('#buzz .lastLoadedPost').last().attr('id'));
        var lastPostId = {
            'lastPostId': $('#buzz .lastLoadedPost').last().attr('id')
        };
        $.ajax({
            url: loadNextSharesURL,
            type: "POST",
            data: lastPostId,
            success: function (data) {
                $('#buzz').append(data);
            }
        });

    });
    $('.hideModalPopUp').live("click", function (e) {
        var id = e.target.id;
        $("#" + id).modal('hide');
    });
//    $(".postLike").live("click", function (e) {
//        isAccess();
//        var idValue = e.target.id;
//        var id = "#" + idValue;
//        alert(idValue);
//        var likeLabelId = "#postNoOfLikes_" + idValue.split("_")[1];
//        //var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
//        var action = "like";
//        
//        $.ajax({
//            url: shareLikeURL + "?shareId=" + idValue.split("_")[1] + "&likeAction=" + action,
//            success: function (data) {
//                $(id).html(data);
//            }
//        });
//    });

    $(".postViewMoreCommentsLink").live("click", function (e) {
        isAccess();
        var postId = e.target.id.split("_")[1];
        $("#" + e.target.id).hide(100);
//        $("#" + postId).css("display", "block");
        $("." + postId).filter(function () {
            return ($(this).css("display") == "none")
        }).show(80);

    });


    $(".nfPostCommentLink").live("click", function (e) {
        isAccess();
        $("#nfPostCommentTextBox" + e.target.id).toggle(300);
    });

    /**
     * Liking a share
     */


    /**
     * Liking a comment
     */



    $(".nfShowComments").live("click", function (e) {
        isAccess();
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
        isAccess();
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
        isAccess();
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
    $(".commentBox").live("keydown", function (e) {
        isAccess();
        if ((e.keyCode === 13) && !e.shiftKey) {
            var elementId = "#" + e.target.id;

            var value = $(elementId).val();
            $('#commentLoadingBox' + elementId.split("_")[1]).show();
            //value = $.trim(value.replace(/[\t\n]+/g, ' '));

            $(elementId).attr('placeholder', 'Write your comment...');
            var commentListId = "#commentListNew_" + elementId.split("_")[1];
            var data = {
                'commentText': value,
                'shareId': elementId.split("_")[1]
            };
            $.ajax({
                url: addBuzzCommentURL,
                type: 'POST',
                data: data,
                success: function (data) {
                    $(commentListId).append(data);
                    $('.commentLoadingBox').hide();
                    $(elementId).val('');
                }
            });
        }
    });

    /**
     * Option widget
     */

    $(".account").live("click", function (e)
    {
        var elementId = "#submenu" + e.target.id;

        $(elementId).toggle(100);
    });

    $(".commentAccount").live("click", function (e)
    {
        var elementId = "#submenu" + e.target.id;
        $(elementId).toggle(100);
    });

//Mouse click on sub menu
    $(".submenu").mouseup(function ()
    {
        return false;
    });

//Mouse click on my account link
    $(".account").mouseup(function ()
    {
        return false;
    });
    $(".commentAccount").mouseup(function ()
    {
        return false;
    });


//Document Click
    $(document).mouseup(function ()
    {
        $(".submenu").hide();
        //$(".account").attr('id', '');
    });



    $(".deleteShare").live("click", function (e) {
        var shareId = e.target.id.split("_")[1];
        $.ajax({
            url: shareDeleteURL + "?shareId=" + shareId,
            success: function (data) {
                $("#post" + shareId).hide();
            }
        });
    });

    /**
     * Edit share form pop up 
     */
    $(".editShare").live("click", function (e) {
        var shareId = e.target.id.split("_")[1];
        $("#editposthide_" + shareId).modal();
    });

    var nextBtn;
    var prevBtn;
    var shownImgId;
    var shownImgPostId;
    /**
     * Show images in modal..
     */
    $(".postPhoto").live("click", function (e) {
        var shareId = parseInt(e.target.id.split("_")[1]);
        var imageId = parseInt(e.target.id.split("_")[0]);
//        if ($("#btn_1_" + shareId).length <= 0) {
//            nextBtn.attr('id', "btn_" + parseInt(imageId) + "_" + shareId);
//        }
//
//        if ($("#btn_0_" + shareId + ".imagePrevBtn").length <= 0) {
//            prevBtn.attr('id', "btn_" + (parseInt(imageId) - 1) + "_" + shareId);
//        }

        $(".postPhotoPrev").hide();
        $(".imagePrevBtn").attr("disabled", 'true');
        $(".imageNextBtn").attr("disabled", 'true');
        $("#showPhotos" + shareId).modal();
        modalVisible = true;
        shownImgId = imageId;
        shownImgPostId = shareId;
        $("#img_" + shownImgId + "_" + shownImgPostId).show();
        if ($("#img_" + '2' + "_" + shownImgPostId).length > 0) {
            $("#imageNextBtn" + shownImgPostId).removeAttr("disabled");
        }

        if ($("#img_" + '2' + "_" + shownImgPostId).length > 0) {
            $("#imagePrevBtn" + shownImgPostId).removeAttr("disabled");
        }
    });

    $(".imageNextBtn").live("click", function (e) {
//        alert(e.target.id);
        if ($("#img_" + parseInt(shownImgId + 1) + "_" + shownImgPostId).length <= 0) {
            $("#img_" + shownImgId + "_" + shownImgPostId).hide();
            shownImgId = 1;
            $("#img_" + shownImgId + "_" + shownImgPostId).show();
        } else {
            $("#img_" + shownImgId + "_" + shownImgPostId).hide();
            shownImgId = shownImgId + 1;
            $("#img_" + shownImgId + "_" + shownImgPostId).show();
        }

        $("#imagePrevBtn" + shownImgPostId).removeAttr("disabled");

//        var visiblePhotoId = parseInt(e.target.id.split("_")[1]);
//        var postId = parseInt(e.target.id.split("_")[2]);
//        var nextVisiblePhotoId = visiblePhotoId + 1;
//
//        if ($("#img_" + visiblePhotoId + "_" + postId).length > 0) {
//            $("#btn_" + (visiblePhotoId - 1) + "_" + postId + ".imagePrevBtn").removeAttr('disabled');
//        }
//
//        if ($("#img_" + nextVisiblePhotoId + "_" + postId).length > 0) {
//            $("#img_" + visiblePhotoId + "_" + postId).hide();
//            shownImg = "#img_" + nextVisiblePhotoId + "_" + postId;
//            $(shownImg).show();
//            if ($("#img_" + parseInt(nextVisiblePhotoId + 1) + "_" + postId).length <= 0) {
//                $("#btn_" + visiblePhotoId + "_" + postId + ".imageNextBtn").attr("disabled", "true");
////                $(".imagePrevBtn #btn_" + visiblePhotoId + "_" + postId).attr("disabled", "false");
//            }
//        }
//
//        $("#btn_" + visiblePhotoId + "_" + postId).attr('id', "btn_" + nextVisiblePhotoId + "_" + postId);
//        $("#btn_" + (visiblePhotoId - 1) + "_" + postId + ".imagePrevBtn").attr('id', "btn_" + visiblePhotoId + "_" + postId);
//        nextBtn = $("#btn_" + nextVisiblePhotoId + "_" + postId);
//        prevBtn = $("#btn_" + visiblePhotoId + "_" + postId);
    });

    $(".imagePrevBtn").live("click", function (e) {
        if ($("#img_" + parseInt(shownImgId - 1) + "_" + shownImgPostId).length <= 0) {
            if ($("#img_" + '5' + "_" + shownImgPostId).length > 0) {
                $("#img_" + shownImgId + "_" + shownImgPostId).hide();
                shownImgId = 5;
                $("#img_" + shownImgId + "_" + shownImgPostId).show();
            } else if ($("#img_" + '4' + "_" + shownImgPostId).length > 0) {
                $("#img_" + shownImgId + "_" + shownImgPostId).hide();
                shownImgId = 4;
                $("#img_" + shownImgId + "_" + shownImgPostId).show();
            } else if ($("#img_" + '3' + "_" + shownImgPostId).length > 0) {
                $("#img_" + shownImgId + "_" + shownImgPostId).hide();
                shownImgId = 3;
                $("#img_" + shownImgId + "_" + shownImgPostId).show();
            } else if ($("#img_" + '2' + "_" + shownImgPostId).length > 0) {
                $("#img_" + shownImgId + "_" + shownImgPostId).hide();
                shownImgId = 2;
                $("#img_" + shownImgId + "_" + shownImgPostId).show();
            }
        } else {
            $("#img_" + shownImgId + "_" + shownImgPostId).hide();
            shownImgId = shownImgId - 1;
            $("#img_" + shownImgId + "_" + shownImgPostId).show();
        }

        $("#imageNextBtn" + shownImgPostId).removeAttr("disabled");
//        $(".postPhotoPrev").hide();
//        var nextVisiblePhotoId = parseInt(e.target.id.split("_")[1]);
//        var postId = parseInt(e.target.id.split("_")[2]);
//        var visiblePhotoId = nextVisiblePhotoId + 1;
//
//        if ($("#img_" + visiblePhotoId + "_" + postId).length > 0) {
//            $("#btn_" + visiblePhotoId + "_" + postId + ".imageNextBtn").removeAttr('disabled');
//        }
//
//        if ($("#img_" + nextVisiblePhotoId + "_" + postId).length > 0) {
//            $("#img_" + visiblePhotoId + "_" + postId).hide();
//            $("#img_" + nextVisiblePhotoId + "_" + postId).show();
//            if ($("#img_" + parseInt(nextVisiblePhotoId - 1) + "_" + postId).length <= 0) {
//                $("#btn_" + nextVisiblePhotoId + "_" + postId + ".imagePrevBtn").attr("disabled", "true");
//            }
//        }
//
//        $("#btn_" + visiblePhotoId + "_" + postId + ".imageNextBtn").attr('id', "btn_" + parseInt(visiblePhotoId - 1) + "_" + postId);
//        $("#btn_" + nextVisiblePhotoId + "_" + postId + ".imagePrevBtn").attr('id', "btn_" + parseInt(nextVisiblePhotoId - 1) + "_" + postId);
//        nextBtn = $("#btn_" + parseInt(visiblePhotoId - 1) + "_" + postId);
//        prevBtn = $("#btn_" + parseInt(nextVisiblePhotoId - 1) + "_" + postId);
    });

    /**
     * save edited form
     */
    $(".btnEditShare").live("click", function (e) {
        var shareId = e.target.id.split("_")[1];
        var content = $("#editshareBox_" + shareId).val();
        $("#editposthide_" + shareId).modal('hide');


        $.ajax({
            url: shareEditURL + "?shareId=" + shareId + "&textShare=" + content,
            success: function (data) {

                $("#postContent_" + shareId).replaceWith(data);
                reload();
                setTimeout(refresh, 10000);
            }
        });


    });



    $(".deleteComment").live("click", function (e) {
        var commentId = e.target.id.split("_")[1];

        $.ajax({
            url: commentDeleteURL + "?commentId=" + commentId,
            success: function (data) {
                $("#commentNew_" + commentId).remove();
            }
        });
    });

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
    $(".viewMoreShare").live("click", function (e) {
        var idValue = e.target.id;
        var shareId = idValue.split("_")[1];

        //var postId = idValue.split("_")[2];
        modalVisible = true;
        var data = {
            'shareId': shareId,
        };
        $.ajax({
            url: viewMoreShare,
            type: "POST",
            data: data,
            success: function (data) {

                $('#shareViewContent_' + shareId).replaceWith(data);
                $('#shareViewMoreMod_' + shareId).modal();
            }
        });
    });

    /**
     * share post popup window view
     */
    $(".postShare").live("click", function (e) {
        var idValue = e.target.id;
        $("#posthide_" + idValue.split("_")[1]).modal();



    });

    $(".editComment").live("click", function (e) {
        var commentId = e.target.id.split("_")[1];
        $("#editcommenthideNew2_" + commentId).modal();
    });
    $(".btnEditCommentNew").live("click", function (e) {
        var commentId = e.target.id.split("_")[1];
        var content = $("#editcommentBoxNew2_" + commentId).val();

        $("#editcommenthideNew2_" + commentId).modal('hide');


        $.ajax({
            url: commentEditURL + "?commentId=" + commentId + "&textComment=" + content,
            success: function (data) {
                $("#commentContentNew_" + commentId).replaceWith(data);
                reload();
                setTimeout(refresh, 10000);
            }
        });


    });
    /**
     * view liked employee list for comment
     */
    $(".postNoofLikesTooltip").live("click", function (e) {
        var idValue = e.target.id;
        var shareId = idValue.split("_")[1];
        var likeLabelId = "#noOfLikes_" + idValue.split("_")[1];
        var existingLikes = parseInt($(likeLabelId).html());
        if (existingLikes > 0) {
            var action = "post"

            var data = {
                'id': shareId,
                'actions': action
            };
            $.ajax({
                url: viewLikedEmployees,
                type: 'POST',
                data: data,
                success: function (data) {
                    $("#postlikehidebody_" + shareId).replaceWith(data);
                }
            });


            $("#postlikehide_" + shareId).modal();
        }
    });
    /**
     * hide liked employee list
     */
    $(".btnBackHide").live("click", function (e) {
        var idValue = e.target.id;
        var shareId = idValue.split("_")[1];
        $("#postlikehide_" + shareId).modal('hide');
    });

    /**
     * view like employee list for comment
     */
    $(".commentNoofLikesTooltip").live("click", function (e) {
        var idValue = e.target.id;
        var commentId = idValue.split("_")[1];
        var likeLabelId = "#commentNoOfLikes_" + idValue.split("_")[1];
        var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);

        if (existingLikes > 0) {
            var action = "comment"
            var data = {
                'id': commentId,
                'actions': action
            };
            $.ajax({
                url: viewLikedEmployees,
                type: 'POST',
                data: data,
                success: function (data) {
                    $("#commentlikehidebody_" + commentId).replaceWith(data);
                }
            });
            $("#commentlikehide_" + commentId).modal();
        }
    });
    /**
     * hide liked employee list for comment
     */
    $(".btnBackHideComment").live("click", function (e) {
        var idValue = e.target.id;
        var commentId = idValue.split("_")[1];
        $("#commentlikehide_" + commentId).modal('hide');
    });
    $("#spinner").bind("ajaxSend", function () {
        //$(this).show();
    }).bind("ajaxStop", function () {
        $(this).hide();
    }).bind("ajaxError", function () {
        $(this).hide();
    });
    var time = new Date().getTime();
    $(document.body).bind("mousemove keypress", function (e) {
        time = new Date().getTime();
    });

    /**
     * original post view
     */
//    $(".originalPostView").live("click", function (e) {
//        var idValue = e.target.id;
//        var shareId = idValue.split("_")[1];
//        var postId = idValue.split("_")[2];
//        modalVisible = true;
//        //alert("MODAL VISIBLE" + modalVisible);
//                
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

    $(".postCommentBox").live('click', function (e) {
        var idValue = e.target.className;
        $("#commentBoxNew_" + idValue).focus();
    });

    function refresh() {
//        var refreshTime = trim($("#refreshTime").html());
        var refreshTime = 5000;
//        alert(modalVisible);
        if (new Date().getTime() - time >= refreshTime) {
            //$('#spinner').show();
//            $('#buzz').remove();
//            alert("fhhe");
            if (!modalVisible) {
                
                reload();
            }
            setTimeout(refresh, refreshTime);
        } else {

            setTimeout(refresh, refreshTime);
        }

    }
    var loggedInEmpNum = -1;
    function isAccess() {
//        alert(modalVisible);
        $.getJSON(getAccessUrl, {get_param: 'value'}, function (data) {
            if (loggedInEmpNum == -1) {
                loggedInEmpNum = data.empNum;
            } else if (loggedInEmpNum != data.empNum) {
                if (!modalVisible) {
                    location.reload();
                }
            } else if (loggedInEmpNum == null) {
                //location.reload();
            }

            if (data.state === 'loged') {

            } else {
                Redirect();
            }
        });
    }
    function Redirect()
    {
        window.location = loginpageURL;
    }

    setTimeout(refresh, refreshTime);

    // Clicking the tabs make it selected.
    $(".tabButton").live("click", function () {
        $(".tabButton").removeClass('tabSelected');
        $(this).addClass('tabSelected');
    });

    $(".post_prev_content").live("click", function (e) {
        var id = e.target.id;
        var postId = id.split("_")[1];
        var data = {
            'shareId': postId,
        };
        $.ajax({
            url: viewMoreShare,
            type: "POST",
            data: data,
            success: function (data) {

                $('#shareViewContent_' + postId).replaceWith(data);
                $('#shareViewMoreMod_' + postId).modal();
            }
        });
    });

    $(window).scroll(function ()
    {

        if ($(window).scrollTop() >= ($(document).height() - $(window).height()))
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
}
);
/**
 * Activates the clicked tab
 * @param {type} pageId
 * @returns {undefined}
 */
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
