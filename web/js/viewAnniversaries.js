$("#rightBarHeadingAnniv").live('click', function () {
    $("#upcomingAnnivMonth").toggle(300);
    $("#upcomingAnnivList").toggle(300);
    $("#upcomingBdaysMonth").hide(300);
    $("#upcomingBdaysList").hide(300);
    $("#mc_componentContainer").hide(300);
    $("#ml_componentContainer").hide(300);
    $("#moreAniversary").toggle();
    $("#lessAniversary").toggle();
    $("#moreBirthdays").show();
    $("#lessBirthdays").hide();
    $("#moreCommentLiked").show();
    $("#lessCommentLiked").hide();
    $("#morePostLiked").show();
    $("#lessPostLiked").hide();
    if ($("#moreAniversary").is(":visible")) {
        $(this).css("border-radius", "10px");
        $(".rightBarBody").css("border","none");
    }else{
        $("#rightBarheadingBday, #rightBarHeadingMl, #rightBarHeadingMc").css("border-radius", "10px");
        $(this).css("border-radius", "10px 10px 0px 0px");
        $(".rightBarBody").css("border","1px solid #dedede");
    }
});