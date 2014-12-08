$(document).ready(function () {
    /**
     * Submitting a new post
     */
    $("#nfPostSubmitBtn").live("click",function () {
       var x= $("#createPost_content").val();
       if (x == null || trim(x).length <1) {
        alert("First write some thing ");
        
       }else{
        
        var postData = {
            'content' : $("textarea").val(),
            '_csrf_token': $('#createPost__csrf_token').val()
        };
        
        $.ajax({
            url: addBuzzPostURL ,
            type: "POST",
            data: postData,
            
            success: function (data,  textStatus, jqXHR) {
                $('#nfPostList').prepend(data);
                $("textarea").val('');
            }
        });}
    });
    
    $('#frmPublishPost').validate({ // initialize the plugin
        rules: {
           
            content: {
                required: true,
                minlength: 5
            }
        }
    });

    $(".nfPostCommentLink").live("click",function (e) {
        $("#nfPostCommentTextBox" + e.target.id).toggle(300);
    });

    /**
     * share post
     */
    $(".nfPostShareLink").live("click",function (e) {
        var idValue = e.target.id;

        var share = prompt("share this Post", "");

        if (share != null) {
            $.ajax({
            url: shareShareURL + "?postId=" + idValue.split("_")[1] + "&textShare=" + share,
            success: function (data) {
            }
        });
        }
        
    });
    
     $(".nfPostShareLink").live("click",function (e) {
        var idValue = e.target.id;
        var text= $('#'+idValue).html() ;
        var edit = prompt("edit", trim(text));

        if (edit != null) {
            $.ajax({
            url: shareShareURL + "?postId=" + idValue.split("_")[1] + "&textShare=" + share,
            success: function (data) {
            }
        });
        }
        
    });
    
    $(".nfViewOriginalPost").live("click",function (e){
        var idValue = e.target.id;

        var share = prompt("share this Post", "");

        if (share != null) {
            $.ajax({
            url: shareShareURL + "?postId=" + idValue.split("_")[1] + "&textShare=" + share,
            success: function (data) {
            }
        });
        }
        
    });

    /**
     * Liking a share
     */
    $(".nfPostLikeLink").live("click",function (e) {
        var idValue = e.target.id;
        var id = "#" + idValue;
        var likeLabelId = "#nfPostCommentLikeLink_" + idValue.split("_")[1];
        var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
        var action = "";
        if ($(id).html() == "Like") {
            $(id).html("Unlike");
            action = "like";
            $(likeLabelId).html((existingLikes + 1) + " " + "people likes this");
        } else {
            $(id).html("Like");
            action = "unlike";
            $(likeLabelId).html((existingLikes - 1) + " " + "people likes this");
        }
        $.ajax({
            url: shareLikeURL + "?shareId=" + idValue.split("_")[1] + "&likeAction=" + action,
            success: function (data) {
            }
        });
    });

    /**
     * Liking a comment
     */
    $(".nfPostCommentLikeLink").click(function (e) {
        var idValue = e.target.id;
        var id = "#" + idValue;
        var likeLabelId = "#nfPostCommentLikesLink_" + idValue.split("_")[1];
        var existingLikes = parseInt($(likeLabelId).html().split(" ")[0]);
        var action = "";
        if ($(id).html() == "Like") {
            $(id).html("Unlike");
            action = "like";
            $(likeLabelId).html((existingLikes + 1) + " " + "people likes this");
        } else {
            $(id).html("Like");
            action = "unlike";
            $(likeLabelId).html((existingLikes - 1) + " " + "people likes this");
        }
        $.ajax({
            url: commentLikeURL + "?commentId=" + idValue.split("_")[1] + "&likeAction=" + action,
            success: function (data) {
            }
        });
    });

    $("#nfPostCommentLikeLink_1").qtip({
        content: {
            text: 'My common piece of text here'
        }
    });



    $(".nfShowComments").live("click",function (e) {
        var idValue = e.target.id;
        if ($("#" + idValue).html() == "Show Comments") {
            $("#" + idValue).html("Hide Comments");
        } else {
            $("#" + idValue).html("Show Comments");
        }
        idValue = idValue.split("_")[1];
        $("#commentList" + idValue).toggle(300);

    });

    /**
     * Commenting on a share.
     */
    $(".commentBox").keydown(function (e) {
        if (e.keyCode == 13 && !e.shiftKey) {
            var elementId = "#" + e.target.id;
            var value = $(elementId).val();
            var commentListId = "#commentList" + elementId.split("_")[1];
            $.ajax({
                url: addBuzzCommentURL + "?commentText=" + value +
                        "&shareId=" + elementId.split("_")[1],
                success: function (data) {
                    $(commentListId).append(data);
                    $(elementId).val('');
                }
            });
        }
    });

    /**
     * Option widget
     */

    $(".account").live("click",function ()
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


//Document Click
    $(document).mouseup(function ()
    {
        $(".submenu").hide();
        //$(".account").attr('id', '');
    });

    $(".editShare").click(function (e) {
        var shareId = e.target.id;

    });

    $(".deleteShare").click(function (e) {
        var shareId = e.target.id.split("_")[1];
        $.ajax({
            url: shareDeleteURL + "?shareId=" + shareId,
            success: function (data) {
                $("#singlePost" + shareId).hide();
                $("#nfShowComments_" + shareId).hide();
            }
        });
    });

//    $(".deleteComment").click(function (e) {
//        var commentId = e.target.id.split("_")[1];
//        $.ajax({
//            url: commentDeleteURL + "?commentId=" + commentId,
//            success: function (data) {
//                $("#nfCommentMainContainer" + commentId).hide();
//            }
//        });
//    });

    /**
     * share post
     */
    $(".nfPostShareLink").click(function (e) {
        var idValue = e.target.id;

        var share = prompt("share this Post", "");

        if (share != null) {
            $.ajax({
                url: shareShareURL + "?postId=" + idValue.split("_")[1] + "&textShare=" + share,
                success: function (data) {
                    $('#nfPostList').prepend(data);
                }
            });
        }

    });

}
);

