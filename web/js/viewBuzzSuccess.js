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
                    $("#createPost_content").val('');
                }
            });

        }
    });

    function getResizedImage(image) {       
        var sourceWidth = image.naturalWidth;
        var sourceHeight = image.naturalHeight;
        var destImageWidth;
        var destImageHeight;
            
        var sourceAspectRatio = sourceWidth / sourceHeight;
        var destAspectRatio = imageMaxWidth / imageMaxHeight;
        if (sourceWidth <= imageMaxWidth && sourceHeight <= imageMaxHeight) {
            destImageWidth = sourceWidth;
            destImageHeight = sourceHeight;
        } else if (destAspectRatio > sourceAspectRatio) {
            destImageWidth = Math.floor(imageMaxHeight * sourceAspectRatio);
            destImageHeight = imageMaxHeight;
        } else {
            destImageWidth = imageMaxWidth;
            destImageHeight = Math.floor(imageMaxWidth / sourceAspectRatio);
        }
        

        var canvas = document.createElement("canvas");
        canvas.width = destImageWidth;
        canvas.height = destImageHeight;

        var ctx = canvas.getContext("2d");
        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);  
        
        return canvas.toDataURL("image/jpeg");
    }
    
    function convertDataURI2Blob(uri) {
        var byteString = atob(uri.split(',')[1]);

        var mimeString = uri.split(',')[0].split(':')[1].split(';')[0]

        var ab = new ArrayBuffer(byteString.length);
        var ia = new Uint8Array(ab);
        for (var i = 0; i < byteString.length; i++)
        {
            ia[i] = byteString.charCodeAt(i);
        }

        var bb = new Blob([ab], {"type": mimeString});
        return bb;
    }
    
    var formData = new FormData();
    var imageList = {};
    function readURL(file, thumbnailDivId) {

        if (file) {

            var reader = new FileReader();
            reader.readAsDataURL(file);
            imageList[thumbnailDivId] = file;
            reader.onload = function (e) {  
                
                var image = new Image();
                image.onload = function() {
                    var x = '<li><span class="img_del" id="img_del_' + thumbnailDivId + '">X</span> ' + 
                            '<img height="70px" class="imgThumbnailView" id="thumb' + thumbnailDivId + '" src="' + 
                            getResizedImage(image) + '" alt="your image" /></li>';
                    $("#imageThumbnails").append(x);                    
                };
                image.src = e.target.result;                
            };
        }
    }

    $("#image-upload-button").live("click", function () {
        if (noOfPhotosStacked > 5) {
            $("#imageUploadError").modal();
            $("#maxImageErrorBody").show();
            $("#invalidTypeImageErrorBody").hide();
            return;
        }
        $("#photofile").click();
    });

    var noOfPhotosPreviewed = 1;
    var noOfPhotosStacked = 1;
    $("#photofile").change(function () {

        var files = $("#photofile")[0].files;
        var imagesChoosed = $("#photofile")[0].files.length;
        if (imagesChoosed > 5) {
            $("#imageUploadError").modal();
            $("#maxImageErrorBody").show();
            $("#invalidTypeImageErrorBody").hide();
            $("#phototext").val('');
            $("#photofile").replaceWith($("#photofile").val('').clone(true));
            return;
        }
        for (var i = 1; i <= imagesChoosed; i++) {
            var ext = files[i - 1].name.split(".").pop().toLowerCase();
            if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
                $("#imageUploadError").modal();
                $("#invalidTypeImageErrorBody").show();
                $("#maxImageErrorBody").hide();
            } else {
                readURL(files[i - 1], noOfPhotosPreviewed);
                noOfPhotosPreviewed++;
                noOfPhotosStacked++;
            }
        }

    });

    $(".img_del").live('click', function () {
        var id = $(this).attr('id').split("_")[2];
        $('#thumb' + id).attr('hidden', true);
        $('#thumb' + id).hide();
        $('#thumb' + id).attr('src', null);
        delete imageList[id];
//        $(this).attr('hidden', 'true');
        $(this).hide();
        noOfPhotosStacked--;
    });

    $(".hidePhotoPopUp").click(function (e) {
        var id = e.target.id;
        $("#showPhotos" + id.split("_")[1]).modal('hide');
    });

    $("#frmUploadImage").on("submit", function (e) {
        isAccess();


        noOfPhotosPreviewed = 1;
        noOfPhotosStacked = 1;
        e.preventDefault();
//        var imageFiles = 2;
        var photoText = $("#phototext").val();
        if (true) {
            activateTab('page1');
            $("#tabLink1").attr("class", "tabButton tb_one tabSelected");
            $("#tabLink2").removeClass("tabSelected");
            var str = "";

            $('.postLoadingBox').show();

            for (var key in imageList) {
                // Get thumbnail src and file name
                var blob = convertDataURI2Blob($("#thumb" + key).attr('src'));
                formData.append(key, blob, imageList[key].name);
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
                    clearImageUpload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    clearImageUpload();
                } 
            });
        }
    });
    
    function clearImageUpload() {
        $('imageThumbnails').html('');
        $('.postLoadingBox').hide();
        $(".imgThumbnailView").hide();
        $("#phototext").val('');
        $("#photofile").replaceWith($("#photofile").val('').clone(true));
        $(".img_del").hide();
        imageList = {};
        formData = new FormData();        
    }

    $("#gotoProfile").click(function () {
        var id = $('#searchChatter_emp_name_empId').val();
        if (id.length <= 0) {

        } else {
            window.location = profilePage + id;
        }
    });

    var windowTitle = "Orange Buzz";
    var newlyAddedPostCount = 0;
    var noOfPostsWhenLeavingWindow = 0;
    var isWindowFocussed = true;

    $(window).focus(function () {
        isWindowFocussed = true;
    });

    $(window).blur(function () {
        isWindowFocussed = false;
        noOfPostsWhenLeavingWindow = $('.singlePost').length;
    });



    function reload() {
        isAccess();
        var params = {
            'timestamp': $('#buzzLastTimestamp').text()
        };
        $.ajax({
            url: refreshPageURL,
            type: "GET",
            data: params,
            dataType: 'html',
            success: function (data) {
                var result = $(data);
                var newTimestamp = result.find('#new_timestamp').text();
                $('#buzzLastTimestamp').text(newTimestamp);
                
                result.find('#changed_shares li.singlePost').each(function() {
                    var id = $(this).prop('id');
                    var existing = $('#'+id);
                    if (existing.length) {
                        existing.replaceWith($(this).html());
                    } else {
                        $('#buzz').prepend($(this).html());
                    }
                });
                
                var noOfPostsNow = $('.singlePost').length;
                if (!isWindowFocussed) {
                    newlyAddedPostCount = noOfPostsNow - noOfPostsWhenLeavingWindow;
                    if (newlyAddedPostCount > 0) {
                        $(document).prop('title', windowTitle + "(" + newlyAddedPostCount + ")");
                    }
                }
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
        $("#" + id).modal('hide');
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
        if (e.type == 'mouseenter') {
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
        }
    });
    $(".postNoofSharesTooltip").live("hover", function (e) {
        if (e.type == 'mouseenter') {
            isAccess();
            var idValue = e.target.id;
            var shareId = idValue.split("_")[1];
//        alert(shareId);
//        return;
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
        }
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
        $("#postsharehidebody").html("");
        $.ajax({
            url: getSharedEmployeeListURL,
            type: "POST",
            data: data,
            success: function (data) {
                $("#postsharehidebody").html(data);
            }
        });
        $("#postsharehide").modal();
    });

    $(".commentNoofLikesTooltip").live("hover", function (e) {
        if (e.type == 'mouseenter') {
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
        }
    });


    $(".commentSubmitBtn").live("click", function (e) {
        isAccess();

        var elementId = "#" + e.target.id;
        var value = $(elementId).val();

        $("#commentListContainer_" + elementId.split("Id")[1]).css("display", "block");
        if (trim(value).length > 0) {
            $('#commentLoadingBox' + elementId.split("_")[1]).show();

            $(elementId).attr('placeholder', 'Write your comment...');
            var commentId = elementId.split("Id")[1];
            $.ajax({
                url: addBuzzCommentURL,
                type: 'POST',
                data: $('#formCreateComment_' + elementId.split("_")[1]).serialize(),
                success: function (data) {
                    $("#comment-text-width-analyzer").html("");
                    commentMaxLineLength = 510;

                    $("#commentListNew_popPostId" + commentId).append(data);
                    $("#commentListNew_popPostId" + commentId + " " + "#modalEdit").replaceWith(' ');
                    $("#commentListNew_popPostId" + commentId + " li .addNewCommentBody " + "#modatLikeWindow").replaceWith(' ');

                    $("#commentListNew_popShareId" + commentId).append(data);
                    $("#commentListNew_popShareId" + commentId + " " + "#modalEdit").replaceWith(' ');
                    $("#commentListNew_popShareId" + commentId + " li .addNewCommentBody " + "#modatLikeWindow").replaceWith(' ');

                    $("#commentListNew_popPhotoId" + commentId).append(data);
                    $("#commentListNew_popPhotoId" + commentId + " " + "#modalEdit").replaceWith(' ');
                    $("#commentListNew_popPhotoId" + commentId + " li .addNewCommentBody " + "#modatLikeWindow").replaceWith(' ');

                    $("#commentListNew_listId" + commentId).append(data);
                    $('.commentLoadingBox').hide();
                    $(".commentBox").val('');
//                    $(".commentSubmitBtn").val()
                }
            });
        }
    });

    function getCaret(el) {
        if (el.selectionStart) {
            return el.selectionStart;
        } else if (document.selection) {
            el.focus();

            var r = document.selection.createRange();
            if (r == null) {
                return 0;
            }

            var re = el.createTextRange(),
                    rc = re.duplicate();
            re.moveToBookmark(r.getBookmark());
            rc.setEndPoint('EndToStart', re);

            return rc.text.length;
        }
        return 0;
    }

    var commentMaxLineLength = 510;
    var oldText = "";
    $("#comment-text-width-analyzer").hide();
    /**
     * Commenting on a share.
     */
    $(".commentBox").live("keyup", function (e) {
        //isAccess();
        var elementId = "#" + e.target.id;
        var value = $(elementId).val();
        var txt = $("#comment-text-width-analyzer").html();
//        var str = txt + "" + String.fromCharCode(e.which);
//        str = str.replace(/[\t\n]+/g,' ');
        $("#comment-text-width-analyzer").html(value.replace(/[\t\n]+/g, ''));
//        $(String.fromCharCode(e.which)).appendTo("#comment-text-width-analyzer");
//        console.log(getCaret(this));
//        alert(getCaret(this));
        $("#debug").html(commentMaxLineLength + " : " + $("#comment-text-width-analyzer").width());
        if (e.keyCode == 8) {
            $("#comment-text-width-analyzer").html(value.replace(/[\t\n]+/g, ''));
            if ($("#comment-text-width-analyzer").width() < (commentMaxLineLength - 510)
                    || oldText == $("#comment-text-width-analyzer").html() && $("#comment-text-width-analyzer").width() != 0) {
                commentMaxLineLength -= 510;
            }
            $("#debug").html(commentMaxLineLength + " : " + $("#comment-text-width-analyzer").width());
//            $("#comment-text-width-analyzer").html(txt.slice(0, -1));
//            if (commentMaxLineLength > 530) {
//                commentMaxLineLength -= 530;
//            }else{
//                commentMaxLineLength = 0;
//            }
        } else {
            if (value.length === 0) {
                $(elementId).val(null);
                $(elementId).css('height', '');
            }
            else if ($("#comment-text-width-analyzer").width() > commentMaxLineLength) {
                commentMaxLineLength += 510;
//                $("#comment-text-width-analyzer").html("");
//                $("#comment-text-width-analyzer").width(0);

//            var ev = jQuery.Event("keydown");
//            ev.which = 13; // # about:startpageSome key code value
//            $(elementId).trigger(ev);
//            $(elementId).autosize();
//            $(elementId).val(value + "\n");
//                alert("nes");
                $("#debug").html(commentMaxLineLength + " : " + $("#comment-text-width-analyzer").width());
                var content = this.value;
                var caret = getCaret(this);
                this.value = content.substring(0, caret) +
                        "\n" + content.substring(caret, content.length);
                e.stopPropagation();
            }
        }

        oldText = $("#comment-text-width-analyzer").html();


        if ((e.keyCode === 13) && !e.shiftKey) {
            $("#comment-text-width-analyzer").html("");
            commentMaxLineLength = 510;
            $("#commentListContainer_" + elementId.split("Id")[1]).css("display", "block");
            $(elementId).css('height', '');
            if (trim(value).length > 0) {
                $('#commentLoadingBox' + elementId.split("_")[1]).show();

                $(elementId).attr('placeholder', 'Write your comment...');
                var commentId = elementId.split("Id")[1];
                $.ajax({
                    url: addBuzzCommentURL,
                    type: 'POST',
                    data: $('#formCreateComment_' + elementId.split("_")[1]).serialize(),
                    success: function (data) {

                        $("#commentListNew_popPostId" + commentId).append(data);
                        $("#commentListNew_popPostId" + commentId + " " + "#modalEdit").replaceWith(' ');
                        $("#commentListNew_popPostId" + commentId + " li .addNewCommentBody " + "#modatLikeWindow").replaceWith(' ');

                        $("#commentListNew_popShareId" + commentId).append(data);
                        $("#commentListNew_popShareId" + commentId + " " + "#modalEdit").replaceWith(' ');
                        $("#commentListNew_popShareId" + commentId + " li .addNewCommentBody " + "#modatLikeWindow").replaceWith(' ');

                        $("#commentListNew_popPhotoId" + commentId).append(data);
                        $("#commentListNew_popPhotoId" + commentId + " " + "#modalEdit").replaceWith(' ');
                        $("#commentListNew_popPhotoId" + commentId + " li .addNewCommentBody " + "#modatLikeWindow").replaceWith(' ');

                        $("#commentListNew_listId" + commentId).append(data);
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
        var elementId = "submenu" + e.target.id;
        $('[id=' + elementId + "]").toggle(100);
    });

//Mouse click on sub menu
    $(".submenu").live("mouseup", function ()
    {
        return false;
    });

//Mouse click on my account link
    $(".account").live("mouseup", function ()
    {
        return false;
    });
    $(".commentAccount").live("mouseup", function ()
    {
        return false;
    });


//Document Click
    $(document).live("mouseup", function ()
    {
        $(".submenu").hide();
        //$(".account").attr('id', '');
    });


    var idOfThePostToDelete = -1;
    $(".deleteShare").live("click", function (e) {
        $(".delete-share-message-box").modal();
        idOfThePostToDelete = e.target.id.split("_")[1];
    });

    $("#delete_confirm").live("click", function () {
        $(".delete-share-message-box").modal('hide');
        $("#loadingDataModal").modal();
        var data = {
            'shareId': idOfThePostToDelete,
        };
        $.ajax({
            url: shareDeleteURL,
            type: "POST",
            data: data,
            success: function (data) {
                $("#postInList" + idOfThePostToDelete).hide(1000);
                $("#loadingDataModal").modal('hide');
                idOfThePostToDelete = -1;
                $("#successBodyDelete").show();
                $("#successBodyShare").hide();
                $("#successBodyEdit").hide();
                $("#successDataModal").modal();
                setTimeout(hideSuccessModal, 3000);
                setTimeout(refresh, 10000);
            }
        });
    });

    $("#delete_discard").live("click", function () {
        $(".delete-share-message-box").modal('hide');
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
        var data = {
            'shareId': shareId,
            'textShare': content
        };

        $.ajax({
            url: shareEditURL,
            type: "POST",
            data: data,
            success: function (data) {

                $("#postContent_" + shareId).replaceWith(data);
                reload();
                $("#loadingDataModal").modal('hide');

                $("#successBodyShare").hide();
                $("#successBodyEdit").show();
                $("#successBodyDelete").hide();
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

        var data = {
            'commentId': commentId,
        };
        $.ajax({
            url: commentDeleteURL,
            type: "POST",
            data: data,
            success: function (data) {
                $("[id=commentInPost_" + commentId + ']').hide(1000);
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
        $(".modal").modal('hide');
        $("#loadingDataModal").modal();
        var shareId = idValue.split("_")[1];
//        var share = $("#shareBox_" + idValue.split("_")[1]).val();
        var share = $("[id=shareBox_" + idValue.split("_")[1] + ']').val();
        var data = {
            'postId': idValue.split("_")[2],
            'textShare': share
        };
        $.ajax({
            url: shareShareURL,
            type: 'POST',
            data: data,
            success: function (data) {
                $("#posthide_" + shareId).modal("hide");
                $("#posthidePopup_" + shareId).modal("hide");
                $('#buzz').prepend(data);
                $("#loadingDataModal").modal('hide');
                $("#successBodyShare").show();
                $("#successBodyEdit").hide();
                $("#successBodyDelete").hide();
                $("#successDataModal").modal();
                setTimeout(hideSuccessModal, 3000);
            }
        });




    });

    /**
     * share post save 
     */
    $(".btnShareOnPreview").live("click", function (e) {
        var idValue = e.target.id;
        $("#posthide_" + idValue.split("_")[1]).hide();
        $(".modal").hide();
        $("#loadingDataModal").modal();
        var shareId = idValue.split("_")[1];
//        var share = $("#shareBox_" + idValue.split("_")[1]).val();
        var share = $("[id=share1Box_" + idValue.split("_")[1] + ']').val();
        var data = {
            'postId': idValue.split("_")[2],
            'textShare': share
        };
        $.ajax({
            url: shareShareURL,
            type: 'POST',
            data: data,
            success: function (data) {
                $("#posthide_" + shareId).modal("hide");
                $("#posthidePopup_" + shareId).modal("hide");
                $('#buzz').prepend(data);
                $("#loadingDataModal").modal('hide');
                $("#successBodyDelete").hide();
                $("#successBodyShare").show();
                $("#successBodyEdit").hide();
                $("#successDataModal").modal();
                setTimeout(hideSuccessModal, 3000);
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

    /**
     * share post popup window in original post popup
     */
    $(".postShareOnOriginalPostPopup").live("click", function (e) {
        var idValue = e.target.id;
        $("#posthidePopupOnOriginalPost_" + idValue.split("_")[1]).modal();

    });

    /**
     * share post popup window view in PopUp
     */
    $(".postSharePopup").live("click", function (e) {
        var idValue = e.target.id;
        $("#posthidePopup_" + idValue.split("_")[1]).modal();

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

        var data = {
            'commentId': commentId,
            'textComment': content
        };
        $.ajax({
            url: commentEditURL,
            type: 'POST',
            data: data,
            success: function (data) {
                $("[id=commentContentNew_" + commentId + ']').replaceWith(data);
                $("#loadingDataModal").modal('hide');

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
        $("#postlikehidebody").html("");
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
                    $("#postlikehidebody").html(data);
                }
            });


            $("#postlikehide").modal();
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
        $("#commentBoxNew_listId" + idValue).focus();
    });
    
    var refreshTime = trim($("#refreshTime").text());    
    var lastActivityTime = new Date().getTime();
    
    $(document.body).bind("mousemove keypress", function (e) {
        lastActivityTime = new Date().getTime();
    });
    
    window.setTimeout(function () {
        refresh(this);
    }, refreshTime);     
    
    function refresh() {
        $(document).prop('title', windowTitle);
        
        if (new Date().getTime() - lastActivityTime >= refreshTime) {
            if (!$('.modal').is(":visible")) {
                reload();
            }
        }  
        
        window.setTimeout(function() {
            refresh(this);
        }, refreshTime);
    }
        
    var loggedInEmpNum = -1;
    function isAccess() {

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

    function viewport() {
        var e = window, a = 'inner';
        if (!('innerWidth' in window)) {
            a = 'client';
            e = document.documentElement || document.body;
        }
        return {width: e[a + 'Width'], height: e[a + 'Height']};
    }

    $(window).scroll(function ()
    {

//        alert($(document).height() + " ----- " + $(window).height());
        if ($(window).scrollTop() + viewport().height >= $(document).height())
        {
//            $(window).unbind('scroll');
            var sharesLoadedCount = parseInt($('#buzzSharesLoadedCount').html());
            var allSharesCount = parseInt($('#buzzAllSharesCount').html());
            var sharesInceasingCount = parseInt($('#buzzSharesInceasingCount').html());

            if ($('.loadMoreBox').css('display') == 'none') {
                if (allSharesCount > sharesLoadedCount) {
                    sharesLoadedCount = sharesLoadedCount + sharesInceasingCount;
                    $('#buzzSharesLoadedCount').html(sharesLoadedCount);

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
                $("#status_icon").attr("src", imageFolderPath + "status2.png");
                $("#status-tab-label").css("color", "#f07c00");
                $("#images-tab-label").css("color", "#5d5d5d");
                $("#video-tab-label").css("color", "#5d5d5d");
                $("#img_upld_icon").attr("src", imageFolderPath + "img.png");
                $("#vid_upld_icon").attr("src", imageFolderPath + "vid.png");
            } else if (pageId === 'page2') {
                $("#status_icon").attr("src", imageFolderPath + "status.png");
                $("#status-tab-label").css("color", "#5d5d5d");
                $("#images-tab-label").css("color", "#f07c00");
                $("#video-tab-label").css("color", "#5d5d5d");
                $("#img_upld_icon").attr("src", imageFolderPath + "img2.png");
                $("#vid_upld_icon").attr("src", imageFolderPath + "vid.png");
            } else {
                $("#status_icon").attr("src", imageFolderPath + "status.png");
                $("#status-tab-label").css("color", "#5d5d5d");
                $("#images-tab-label").css("color", "#5d5d5d");
                $("#video-tab-label").css("color", "#f07c00");
                $("#img_upld_icon").attr("src", imageFolderPath + "img.png");
                $("#vid_upld_icon").attr("src", imageFolderPath + "vid2.png");
            }
        }
    }
}
