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
                    $('.loadMoreBox').hide();
                }
            });
        }

    }
});
