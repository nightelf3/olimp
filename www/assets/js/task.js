/**
 * Created by Night on 07.01.2019.
 */
$(function () {
    if ($('#task-page').length) {
        App = typeof App == 'undefined' ? {} : App;
        App.page = typeof App.page == 'undefined' ? {} : App.page;
        App.page.task = {
            init: function () {
                if (App.page.liveUpdate) {
                    setInterval(function () {
                        $.getJSON(location.href + "/update", function (data) {
                            $('#queue-card').replaceWith($(data.queue));
                            $('#task-tabs').replaceWith($(data.taskTabs));
                        });
                    }, 2500);
                }
                
                var div = document.getElementById("comments");
                div.scrollTop = div.scrollHeight - div.clientHeight;
            }
        };

        App.page.task.init();
    }
});
