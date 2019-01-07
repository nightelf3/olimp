/**
 * Created by Night on 07.01.2019.
 */
$(function () {
    if ($('#user-card').length) {
        App = typeof App == 'undefined' ? {} : App;
        App.page = typeof App.page == 'undefined' ? {} : App.page;
        App.page.userCard = {
            init: function () {
                if (App.page.liveUpdate) {
                    setInterval(function () {
                        $.getJSON(location.origin + "/user/update", function (data) {
                            $('#user-card').replaceWith($(data.userCard));
                        });
                    }, 2500);
                }
            }
        };

        App.page.userCard.init();
    }
});
