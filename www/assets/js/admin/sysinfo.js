/**
 * Created by Night on 08.01.2018.
 */
$(function() {
    if ($('#admin-sysinfo').length) {
        App = typeof App == 'undefined' ? {} : App;
        App.admin = typeof App.admin == 'undefined' ? {} : App.page;
        App.admin.sysinfo = {
            init: function () {
                $(document).on('click', '.confirm-click', function(e) {
                    if (confirm(sprintf('Confirm %1$s?', this.value))) {
                        return true;
                    }

                    e.preventDefault();
                    return false;
                });
            }
        };

        App.admin.sysinfo.init();
    }
});
