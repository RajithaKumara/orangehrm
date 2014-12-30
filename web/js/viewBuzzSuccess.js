var modalVisible = false;
var fileInput = $("#photofile");
$(document).ready(function () {
    /**
     * Submitting a new post
     */
    $("#postSubmitBtn").live("click", function () {
        isAccess();
        var x = $("#createPost_content").val();
        if (x === null || trim(x).length < 1) {

        } else {
            $('.postLoadingBox').show();

            $.ajax({
                url: addBuzzPostURL,
                type: "POST",
                data: $('#frmPublishPost').serialize(),
                success: function (data) {

                    $('#buzz').prepend(data);
                    $('.postLoadingBox').hide();
                    $("#postLinkData").hide();
                    $("#postLinkState").html('no');
                    $("textarea").val('');
                }
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
            fileInput.replaceWith(fileInput.val('').clone(true));
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

    $(".hidePhotoPopUp").click(function (e) {
        var id = e.target.id;
        $("#showPhotos" + id.split("_")[1]).modal('hide');
    });

    $("#frmUploadImage").on("submit", function (e) {
        isAccess();


        noOfPhotosPreviewed = 1;
        e.preventDefault();
        var imageFiles = $("#photofile")[0].files;
        var photoText = $("#phototext").val();
        if (imageFiles.length > 0) {
            activateTab('page1');
            $("#tabLink1").attr("class", "tabButton tb_one tabSelected");
            $("#tabLink2").removeClass("tabSelected");
            var str = "";
            if (imageFiles.length > 5) {
                //Handel proper validation here...

                return;
            }
            $('.postLoadingBox').show();
            $.each(imageFiles, function (k, v) {
            });
            for (var key in imageList) {
                formData.append(key, imageList[key]);
            }
            formData.append('postContent', photoText);



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
                    $("#phototext").val('');
                    $("#photofile").replaceWith($("#photofile").val('').clone(true));
                    $(".img_del").attr('hidden', 'true');
                    imageList = {};
                    formData = new FormData();
                }
            });
        }
    });

    $("#gotoProfile").click(function () {
        var id = $('#searchChatter_emp_name_empId').val();
        if (id.length <= 0) {

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
        var profileUserId = {
            'profileUserId': $('#profileUserId').html()
        };
        $.ajax({
            url: refreshStatsURL,
            type: "POST",
            data: profileUserId,
            success: function (data) {
                $('#statTable').replaceWith(data);
            }
        });
    }

    $(".loadMorePostsLink").live("click", function (e) {
        isAccess();
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
        $(".modal").modal('hide');
    });

    $(".postViewMoreCommentsLink").live("click", function (e) {
        isAccess();
        var postId = e.target.id.split("_")[1];
        $("#" + e.target.id).hide(100);
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
    $(".postNoofSharesTooltip").live("hover", function (e) {
        isAccess();
        var idValue = e.target.id;
        var shareId = idValue.split("_")[1];
        var data = {
            'id': shareId,
            'type': 'post',
            'event': 'hover'
        };
        $.ajax({
            url: getSharedEmployeeListURL,
            type: "POST",
            data: data,
            success: function (data) {
                $('#' + idValue).attr('title', '');
                $('#' + idValue).attr('title', data);
            }
        });
    });

    $(".postNoofSharesTooltip").live("click", function (e) {
        isAccess();
        var idValue = e.target.id;
        var shareId = idValue.split("_")[1];
        var data = {
            'id': shareId,
            'type': 'post',
            'event': 'click'
        };
        $.ajax({
            url: getSharedEmployeeListURL,
            type: "POST",
            data: data,
            success: function (data) {
                $("#postlikehidebody_" + shareId).replaceWith(data);
            }
        });
        $("#postlikehide_" + shareId).modal();
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


    $(".commentSubmitBtn").live("click", function (e) {
        isAccess();

        var elementId = "#" + e.target.id;
        var value = $(elementId).val();

        $("#commentListContainer_" + elementId.split("_")[1]).css("display", "block");
        if (trim(value).length > 1) {
            $('#commentLoadingBox' + elementId.split("_")[1]).show();

            $(elementId).attr('placeholder', 'Write your comment...');
            var commentListId = "#commentListNew_" + elementId.split("_")[1];
            $.ajax({
                url: addBuzzCommentURL,
                type: 'POST',
                data: $('#formCreateComment_' + elementId.split("_")[1]).serialize(),
                success: function (data) {
                    $(commentListId).append(data);
                    $(".popupCommentList").append(data);
                    $('.commentLoadingBox').hide();
                    $(elementId).val('');
                }
            });
        }

    });

    /**
     * Commenting on a share.
     */
    $(".commentBox").live("keydown", function (e) {
        isAccess();
        if ((e.keyCode === 13) && !e.shiftKey) {

            var elementId = "#" + e.target.id;
            var value = $(elementId).val();

            $("#commentListContainer_" + elementId.split("_")[1]).css("display", "block");
            if (trim(value).length > 1) {
                $('#commentLoadingBox' + elementId.split("_")[1]).show();

                $(elementId).attr('placeholder', 'Write your comment...');
                var commentListId = "#commentListNew_" + elementId.split("_")[1];
                alert($('#formCreateComment_' + elementId.split("_")[1]).serialize());
                $.ajax({
                    url: addBuzzCommentURL,
                    type: 'POST',
                    data: $('#formCreateComment_' + elementId.split("_")[1]).serialize(),
                    success: function (data) {
                        $(commentListId).append(data);
                        $(".popupCommentList").append(data);
                        $('.commentLoadingBox').hide();
                        $(elementId).val('');
                    }
                });
            }
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
        //return false;
    });


//Document Click
    $(document).mouseup(function ()
    {
        $(".submenu").hide();
        //$(".account").attr('id', '');
    });


    var idOfThePostToDelete = -1;
    $(".deleteShare").live("click", function (e) {
        $("#deleteConfirmationModal").modal();
        idOfThePostToDelete = e.target.id.split("_")[1];
    });

    $("#delete_confirm").live("click", function () {
        $("#deleteConfirmationModal").modal('hide');
        $("#loadingDataModal").modal();
        $.ajax({
            url: shareDeleteURL + "?shareId=" + idOfThePostToDelete,
            success: function (data) {
                $("#post" + idOfThePostToDelete).hide();
                $("#loadingDataModal").modal('hide');
                idOfThePostToDelete = -1;
                $("#successBody").replaceWith("<div id='successBody' >Successfully Deleted!</div>");
                $("#successDataModal").modal();
                setTimeout(hideSuccessModal, 3000);
                setTimeout(refresh, 10000);
            }
        });
    });

    $("#delete_discard").live("click", function () {
        $("#deleteConfirmationModal").modal('hide');
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
        $(".modal").modal('hide');
        var shareId = parseInt(e.target.id.split("_")[1]);
        var imageId = parseInt(e.target.id.split("_")[0]);


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

    });

    /**
     * save edited form
     */
    $(".btnEditShare").live("click", function (e) {
        var shareId = e.target.id.split("_")[1];
        var content = $("#editshareBox_" + shareId).val();
        $("#editposthide_" + shareId).modal('hide');
        $("#loadingDataModal").modal();

        $.ajax({
            url: shareEditURL + "?shareId=" + shareId + "&textShare=" + content,
            success: function (data) {

                $("#postContent_" + shareId).replaceWith(data);
                reload();
                $("#loadingDataModal").modal('hide');
                $("#successBody").replaceWith("<div id='successBody' >Successfully Saved!</div>");
                $("#successDataModal").modal();
                setTimeout(hideSuccessModal, 3000);
                setTimeout(refresh, 10000);
            }
        });


    });

    function hideSuccessModal() {
        $("#successDataModal").modal('hide');
    }

    $(".deleteComment").live("click", function (e) {
        var commentId = e.target.id.split("_")[1];
        $("#loadingDataModal").modal();

        $.ajax({
            url: commentDeleteURL + "?commentId=" + commentId,
            success: function (data) {
                $("#commentNew_" + commentId).remove();
                $("#loadingDataModal").modal('hide');
            }
        });
    });

    /**
     * share post save 
     */
    $(".btnShare").live("click", function (e) {
        var idValue = e.target.id;
        $("#posthide_" + idValue.split("_")[1]).modal('hide');
        $("#loadingDataModal").modal();
        var share = $("#shareBox_" + idValue.split("_")[1]).val();
        var data = {
            'postId': idValue.split("_")[2],
            'textShare': share
        };
        $.ajax({
            url: shareShareURL,
            type: 'POST',
            data: data,
            success: function (data) {
                $(".modal").modal("hide");
                $('#buzz').prepend(data);
                $("#loadingDataModal").modal('hide');
                $("#successDataModal").modal();
                setTimeout(hideSuccessModal, 3000);
                setTimeout(refresh, 10000);
            }
        });




    });
    $(".viewMoreShare").live("click", function (e) {
        var idValue = e.target.id;
        var shareId = idValue.split("_")[1];
        $("#loadingDataModal").modal();
        var data = {
            'shareId': shareId,
        };
        $.ajax({
            url: viewMoreShare,
            type: "POST",
            data: data,
            success: function (data) {

                $('#shareViewContent1_' + shareId).replaceWith(data);

                $("#loadingDataModal").modal('hide');
                $('#shareViewMoreMod1_' + shareId).modal();
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
        $("#loadingDataModal").modal();

        $.ajax({
            url: commentEditURL + "?commentId=" + commentId + "&textComment=" + content,
            success: function (data) {
                $("#commentContentNew_" + commentId).replaceWith(data);
                reload();
                $("#loadingDataModal").modal('hide');
                setTimeout(refresh, 10000);
            }
        });


    });
    /**
     * view liked employee list for post
     */
    $(".postNoofLikesTooltip").live("click", function (e) {
        var idValue = e.target.id;
        var shareId = idValue.split("_")[1];
        var likeLabelId = "#noOfLikes_" + idValue.split("_")[1];
        var existingLikes = parseInt($(likeLabelId).html());
        if (existingLikes > 0) {
            var action = "post";

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
            var action = "comment";
            var data = {
                'id': commentId,
                'actions': action
            };
            $.ajax({
                url: viewLikedEmployees,
                type: 'POST',
                data: data,
                success: function (data) {
//                    alert(data);
                    $("#postlikehidebody_" + commentId).replaceWith(data);
                }
            });
            $("#postlikehide_" + commentId).modal();
        }
    });
    /**
     * hide liked employee list for comment
     */

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
    $(".originalPostView").live("click", function (e) {
        var idValue = e.target.id;
        var shareId = idValue.split("_")[1];
        var postId = idValue.split("_")[2];

        $("#loadingDataModal").modal();
        var data = {
            'postId': postId,
        };
        $.ajax({
            url: viewOriginalPost,
            type: "POST",
            data: data,
            success: function (data) {

                $('#postViewContent_' + shareId).replaceWith(data);
                $("#loadingDataModal").modal('hide');
                $('#postViewOriginal_' + shareId).modal();
            }
        });
    });

    $(".postCommentBox").live('click', function (e) {
        var idValue = e.target.className;
        $("#commentBoxNew_" + idValue).focus();
    });

    function refresh() {
        var refreshTime = trim($("#refreshTime").html());
//        var refreshTime = 3000;

        if (new Date().getTime() - time >= refreshTime) {

            if (!$('.modal').is(":visible")) {

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
                if (!$('.modal').is(":visible")) {
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

    $(".likeRaw").live("click", function (e) {
        var id = e.target.id;
        var postId = id.split("_")[1];
        var data = {
            'shareId': postId
        };
        $.ajax({
            url: viewMoreShare,
            type: "POST",
            data: data,
            success: function (data) {
//        alert(postId);
                $('#shareViewContent3_').html(data);
                $('#shareViewMoreMod3_').modal();
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
            if (pageId === 'page1') {
                $("#status_icon").attr("src", imageFolderPath + "/status2");
                $("#img_upld_icon").attr("src", imageFolderPath + "/img");
                $("#vid_upld_icon").attr("src", imageFolderPath + "/vid");
            } else if (pageId === 'page2') {
                $("#status_icon").attr("src", imageFolderPath + "/status");
                $("#img_upld_icon").attr("src", imageFolderPath + "/img2");
                $("#vid_upld_icon").attr("src", imageFolderPath + "/vid");
            } else {
                $("#status_icon").attr("src", imageFolderPath + "/status");
                $("#img_upld_icon").attr("src", imageFolderPath + "/img");
                $("#vid_upld_icon").attr("src", imageFolderPath + "/vid2");
            }
        }
    }
}
