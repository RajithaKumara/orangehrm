$(document).ready(function () {
        $(".postLike").live("click", function (e) {
            isAccess();

            var idValue = e.target.id;
            var id = "postLikebody_" + trim(idValue.split("_")[1]);
            var div = document.getElementById(id);
            var id2 = "postUnLikebody_" + trim(idValue.split("_")[1]);
            var div2 = document.getElementById(id2);
            var likeLabelId = "#noOfLikes_" + idValue.split("_")[1];
            var existingLikes = parseInt($(likeLabelId).html());
            var UnlikeLabelId = "#noOfUnLikes_" + idValue.split("_")[1];
            var existingUnLikes = parseInt($(UnlikeLabelId).html());
            var action = "like";
             $("[id=postUnlikeno_" + idValue.split("_")[1]+']').show();
             $("[id=postUnlikeyes_" + idValue.split("_")[1]+']').hide();
             $("[id=postLikeyes_" + idValue.split("_")[1]+']').show();
             $("[id=postLikeno_" + idValue.split("_")[1]+']').hide();
            
            $.getJSON(shareLikeURL + "?shareId=" + idValue.split("_")[1] + "&likeAction=" + action,
                    {get_param: 'value'}, function (data) {
                if (data.states === 'savedLike') {
                    var likes = trim($('#postLiketext_' + idValue.split("_")[1]).html());
                    likes++;
                    $("[id=noOfLikes_" + idValue.split("_")[1]+']').html(data.likeCount);
                    $('[id=postLiketext_' + idValue.split("_")[1]+']').html(likes);
                    
                    //div.style.backgroundColor = 'orange';
                }
                if (data.deleted === 'yes') {
                    $("[id=noOfUnLikes_" + idValue.split("_")[1]+']').html(data.unlikeCount);
                    var likes = trim($('#postUnLiketext_' + idValue.split("_")[1]).html());
                    likes--;
                    //$('#postUnLiketext_' + idValue.split("_")[1]).html(likes);
                }
                
               
            });
        });
        $(".postUnlike2").live("click", function (e) {

            isAccess();
            isAccess();
            var idValue = e.target.id;
            var id = "postLikebody_" + trim(idValue.split("_")[1]);
            var div = document.getElementById(id);
            var id2 = "postUnLikebody_" + trim(idValue.split("_")[1]);
            var div2 = document.getElementById(id2);
            var likeLabelId = "#noOfLikes_" + idValue.split("_")[1];
            var existingLikes = parseInt($(likeLabelId).html());
            var UnlikeLabelId = "#noOfUnLikes_" + idValue.split("_")[1];
            var existingUnLikes = parseInt($(UnlikeLabelId).html());
            $("[id=postLikeno_" + idValue.split("_")[1]+']').show();
            $("[id=postLikeyes_" + idValue.split("_")[1]+']').hide();
            $("[id=postUnlikeyes_" + idValue.split("_")[1]+']').show();
            $("[id=postUnlikeno_" + idValue.split("_")[1]+']').hide()
            var action = "unlike";
            $.getJSON(shareLikeURL + "?shareId=" + idValue.split("_")[1] + "&likeAction=" + action,
                    {get_param: 'value'}, function (data) {
                if (data.deleted === 'yes') {
                    var likes = trim($("#postLiketext_" + idValue.split("_")[1]).html());
                    likes--;
                    
                    $("[id=noOfLikes_" + idValue.split("_")[1]+']').html(data.likeCount);
                }
                if (data.states === 'savedUnLike') {
                    var likes = trim($('#postUnLiketext_' + idValue.split("_")[1]).html());
                    likes++;
                    $("[id=postUnLiketext_" + idValue.split("_")[1]+']').html(data.unlikeCount);
                    $("[id=noOfUnLikes_" + idValue.split("_")[1]+']').html(data.unlikeCount);
                }
                
                
            });
        });
        $(".btnSaveVideo").live('click', function (e) {
                    var code = trim($("#yuoutubeVideoId").html());
                    var text = $("#shareVideo").val();
                    $('.postLoadingBox').show();
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
                            $('.postLoadingBox').hide();
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
                        $("#loadVideo").show();
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
                                 $("#loadVideo").hide();
                                $('#videoPostArea').replaceWith(data);
                            }
                        });
                    },
                    cut: function () {

                    }

                });
                $("#createPost_content").bind({paste: function (e) {

                        e.preventDefault();
                        var url = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');
                        var text = $("#createPost_content").val() + url;
                        $("#createPost_content").val(text);
                        $("#postLinkState").html('no');
                        $.ajax({
                            url: document.location.protocol + '//ajax.googleapis.com/ajax/services/feed/lookup?v=1.0&num=10&callback=?&q=' + encodeURIComponent(url),
                            dataType: 'json',
                            success: function (data) {
                                if (data.responseData) {
                                    var feedurl = data.responseData.url;
                                    $.ajax({
                                        url: document.location.protocol + '//ajax.googleapis.com/ajax/services/feed/load?v=1.0&num=10&callback=?&q=' + encodeURIComponent(feedurl),
                                        dataType: 'json',
                                        success: function (data) {
                                            if (data.responseData) {
                                                if (data.responseData.feed && data.responseData.feed.entries) {
                                                    var char = 1;
                                                    $.each(data.responseData.feed.entries, function (i, e) {
                                                        if (char === 1) {
                                                            $("#linkTitle").html(e.title);
                                                            $("#linkText").html(e.contentSnippet);
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
                                } else {
                                    $("#postLinkData").hide();
                                    $("#postLinkState").html('no');
                                }
                            }
                        });
                    }
                });
        $(".commentLike").live("click", function (e) {
        isAccess();
        var idValue = e.target.id;

        var id = "commentLikebody_" + trim(idValue.split("_")[1]);

        $("#commentUnLikeno_" + idValue.split("_")[1]).show();
        $("#commentUnLikeyes_" + idValue.split("_")[1]).hide();
        $("#commentLikeyes_" + idValue.split("_")[1]).show();
        $("#commentLikeno_" + idValue.split("_")[1]).hide();

        var action = "like";
        $.getJSON(commentLikeURL + "?commentId=" + idValue.split("_")[1] + "&likeAction=" + action,
                {get_param: 'value'}, function (data) {
            if (data.states === 'savedLike') {
                $('#commentNoOfLiketext_' + idValue.split("_")[1]).html(data.likeCount);
            }
            if (data.deleted === 'yes') {

                $('#commentNoOfUnLiketext_' + idValue.split("_")[1]).html(data.unlikeCount);
            }

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
                if (data.deleted === 'yes') {
                    var likes = trim($('#commentNoOfLiketext_' + idValue.split("_")[1]).html());
                    likes--;
                    $('#commentNoOfLiketext_' + idValue.split("_")[1]).html(data.likeCount);
                    $("#commentLikeno_" + idValue.split("_")[1]).show();
                    $("#commentLikeyes_" + idValue.split("_")[1]).hide();
                }
                if (data.states === 'savedUnLike') {
                    var likes = trim($('#commentNoOfUnLiketext_' + idValue.split("_")[1]).html());
                    likes++;
                    $('#commentNoOfUnLiketext_' + idValue.split("_")[1]).html(data.unlikeCount);
                    $("#commentUnLikeyes_" + idValue.split("_")[1]).show();
                    $("#commentUnLikeno_" + idValue.split("_")[1]).hide();
                }
                //div2.style.backgroundColor = 'red';
                //div.style.backgroundColor = 'black';
            });
        });
        var modalVisible = false;
        $(".closeFeed").live("click", function (e) {
                $("#postLinkData").hide();
                $("#postLinkState").html('no');
            });
//            $(".originalPostView").live("click", function (e) {
//                var idValue = e.target.id;
//                var shareId = idValue.split("_")[1];
//                //var postId = idValue.split("_")[2];
//                modalVisible = true;
//                var data = {
//                    'postId': postId,
//                };
//                $.ajax({
//                    url: viewOriginalPost,
//                    type: "POST",
//                    data: data,
//                    success: function (data) {
//
//                        $('#postViewContent_' + shareId).replaceWith(data);
//                        $('#postViewOriginal_' + shareId).modal();
//                    }
//                });
//            });
            function isAccess() {
                $.getJSON(getAccessUrl, {get_param: 'value'}, function (data) {

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
    });