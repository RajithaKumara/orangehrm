$("#rightBarheadingBday").live('click', function () {
    $("#upcomingBdaysMonth").toggle(300);
    $("#upcomingBdaysList").toggle(300);
    $("#upcomingAnnivMonth").hide(300);
    $("#upcomingAnnivList").hide(300);
    $("#mc_componentContainer").hide(300);
    $("#ml_componentContainer").hide(300);
    $("#moreBirthdays").toggle(300);
    $("#lessBirthdays").toggle(300);
    $("#moreAniversary").show(300);
    $("#lessAniversary").hide(300);
    $("#moreCommentLiked").show(300);
    $("#lessCommentLiked").hide(300);
    $("#morePostLiked").show(300);
    $("#lessPostLiked").hide(300);
});