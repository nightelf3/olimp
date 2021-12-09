/**
 * Created by Night on 08.01.2018.
 */
$(function() {
    if ($('#admin-comments').length) {
        App = typeof App == 'undefined' ? {} : App;
        App.admin = typeof App.admin == 'undefined' ? {} : App.page;
        App.admin.comments = {
            init: function () {
                $(".team-members").each(function() {
                    this.scrollTop = this.scrollHeight - this.clientHeight;
                });
            }
        };

        App.admin.comments.init();
    }
});
