/**
 * Created by Night on 07.01.2019.
 */
$(function () {
    if ($('#rating-page').length) {
        App = typeof App == 'undefined' ? {} : App;
        App.page = typeof App.page == 'undefined' ? {} : App.page;
        App.page.rating = {
            init: function () {
                if (App.page.liveUpdate) {
                    setInterval(function () {
                        $.getJSON(location.href + "/update", function (data) {
                            $('#rating-table').replaceWith($(data.rating));
                        });
                    }, 5000);
                }
            }
        };

        App.page.rating.init();
    }
});
