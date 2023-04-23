/**
 * Created by Night on 06.01.2018.
 */

$(function () {
    if ($('#admin-task-box').length) {
        App = typeof App == 'undefined' ? {} : App;
        App.admin = typeof App.admin == 'undefined' ? {} : App.page;
        App.admin.task = {
            formTest: function(e) {
                var count = {
                    input: $('#tests-input').val().match(/\n\n/g).length,
                    output: $('#tests-output').val().match(/\n\n/g).length
                };

                if (count.input != count.output) {
                    alert(sprintf("Кількість тестів в полях не співпадає (%(input)d, %(output)d)", count));
                } else if (confirm(sprintf("Кількість тестів %1$d?", count.input + 1))) {
                    $('#tests-count').val(count.input + 1);
                    return true;
                }

                e.preventDefault();
                return false;
            },

            testsResize: function () {
                var resizeInt = null;
                var $this = null;

                // the handler function
                var resizeEvent = function() {
                    if (null != $this) {
                        $('#tests-input, #tests-output').height($this.height());
                    }
                };

                $('#tests-input, #tests-output').on('mousedown', function(e) {
                    $this = $(this);
                    resizeInt = setInterval(resizeEvent, 100);
                });

                $(window).on('mouseup', function(e) {
                    if (resizeInt !== null) {
                        clearInterval(resizeInt);
                    }
                    resizeEvent();
                });
            },

            refreshFilesState: function ()
            {
                let value = $('#is-task-custom-file').is(':checked');
                $('.custom-file').prop('disabled', (i, v) => !value);
                $('.custom-file').closest('tr').toggleClass('disabled', !value);
            },

            init: function (id) {
                $('#task-' + id).css({
                    backgroundColor: '#c6c6c6',
                    color: '#606060'
                });

                $(window).keydown(function(event) {
                        // If Control or Command key is pressed and the S key is pressed
                        // run save function. 83 is the key code for S.
                        if((event.ctrlKey || event.metaKey) && event.which == 83) {

                            $('#save-task').click();

                            event.preventDefault();
                            return false;
                        }
                    }
                );

                ClassicEditor
                    .create(document.querySelector('.ckeditor'))
                    .catch(error => {
                        console.error(error);
                    });

                $(document).on('change', '#is-task-custom-file', App.admin.task.refreshFilesState)
                App.admin.task.refreshFilesState();

                $(document).on('submit', '#tests-form', App.admin.task.formTest);
                App.admin.task.testsResize();
            }
        };
    }
});
