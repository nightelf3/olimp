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
                })
                .on('submit', '#checker-settings', function(e) {
                    e.preventDefault();

                    // download config.json
                    const a = document.createElement('a')
                    a.href = $(this).attr("action") + '?' + $(this).serialize();
                    a.download = "config.json"
                    document.body.appendChild(a)
                    a.click()
                    document.body.removeChild(a)
                });
            }
        };

        ClassicEditor
            .create(document.querySelector('.ckeditor'))
            .catch(error => {
                console.error(error);
            });

        App.admin.sysinfo.init();
    }
});
