$("#rightBarHeadingMc").live('click', function () {
    $("#upcomingAnnivMonth").hide(300);
    $("#upcomingAnnivList").hide(300);
    $("#upcomingBdaysMonth").hide(300);
    $("#upcomingBdaysList").hide(300);
    $("#mc_componentContainer").toggle(300);
    $("#ml_componentContainer").hide(300);

    $("#morePostLiked").show();
    $("#lessPostLiked").hide();
    $("#moreBirthdays").show();
    $("#lessBirthdays").hide();
    $("#moreCommentLiked").toggle();
    $("#lessCommentLiked").toggle();
    $("#moreAniversary").show();
    $("#lessAniversary").hide();
});

$("#rightBarHeadingMl").live('click', function () {
    $("#upcomingAnnivMonth").hide(300);
    $("#upcomingAnnivList").hide(300);
    $("#upcomingBdaysMonth").hide(300);
    $("#upcomingBdaysList").hide(300);
    $("#mc_componentContainer").hide(300);
    $("#ml_componentContainer").toggle(300);
    $("#morePostLiked").toggle();
    $("#lessPostLiked").toggle();
    $("#moreBirthdays").show();
    $("#lessBirthdays").hide();
    $("#moreCommentLiked").show();
    $("#lessCommentLiked").hide();
    $("#moreAniversary").show();
    $("#lessAniversary").hide();
});