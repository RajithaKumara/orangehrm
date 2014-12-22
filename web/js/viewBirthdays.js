$("#rightBarheadingBday").live('click', function () {
    $("#upcomingBdaysMonth").toggle(300);
    $("#upcomingBdaysList").toggle(300);
    $("#upcomingAnnivMonth").hide(300);
    $("#upcomingAnnivList").hide(300);
    $("#mc_componentContainer").hide(300);
    $("#ml_componentContainer").hide(300);
    $("#moreBirthdays").toggle();
    $("#lessBirthdays").toggle();
    $("#moreAniversary").show();
    $("#lessAniversary").hide();
    $("#moreCommentLiked").show();
    $("#lessCommentLiked").hide();
    $("#morePostLiked").show();
    $("#lessPostLiked").hide();
    if ($("#moreBirthdays").is(":visible")) {
        $(this).css("border-radius", "10px");
        $(".rightBarBody").css("border","none");
    }else{
        $("#rightBarHeadingAnniv, #rightBarHeadingMl, #rightBarHeadingMc").css("border-radius", "10px");
        $(this).css("border-radius", "10px 10px 0px 0px");
        $(".rightBarBody").css("border","1px solid #dedede");
    }
});