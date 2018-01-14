/**
 * Created by Night on 02.12.2017.
 */
$(function () {
    if ($('#timerbox').length) {
        App = typeof App == 'undefined' ? {} : App;
        App.page = typeof App.page == 'undefined' ? {} : App.page;
        App.page.timer = {
            olimpStart: new Date(),
            olimpContinuity: 0,

            timer: function() {
                var dateNow = new Date();
                var amount = App.page.timer.olimpStart.getTime() + App.page.timer.olimpContinuity - dateNow.getTime();
                console.log(App.page.timer.olimpStart, App.page.timer.olimpContinuity, dateNow.getTime(), amount);
                var hours1 = 0,
                    hours2 = 0,
                    mins1 = 0,
                    mins2 = 0,
                    secs1 = 0,
                    secs2 = 0;
                var needTimeout = false;

                amount /= 1000;
                if (amount > 0) {
                    var hours = Math.floor(amount / 3600);
                    hours1 = (hours >= 10) ? hours.toString().charAt(0) : '0';
                    hours2 = (hours >= 10) ? hours.toString().charAt(1) : hours.toString().charAt(0);

                    amount = amount % 3600;
                    var mins = Math.floor(amount / 60);
                    mins1 = (mins >= 10) ? mins.toString().charAt(0) : '0';
                    mins2 = (mins >= 10) ? mins.toString().charAt(1) : mins.toString().charAt(0);

                    amount = amount % 60;
                    var secs = Math.floor(amount);
                    secs1 = (secs >= 10) ? secs.toString().charAt(0) : '0';
                    secs2 = (secs >= 10) ? secs.toString().charAt(1) : secs.toString().charAt(0);

                    needTimeout = true;
                }

                $('#timerbox-hours1').html(hours1);
                $('#timerbox-hours2').html(hours2);
                $('#timerbox-mins1').html(mins1);
                $('#timerbox-mins2').html(mins2);
                $('#timerbox-secs1').html(secs1);
                $('#timerbox-secs2').html(secs2);

                if (needTimeout) {
                    setTimeout("App.page.timer.timer()", 1000);
                }
            },

            init: function (olimpStart, olimpContinuity) {
                App.page.timer.olimpStart = new Date(olimpStart);
                App.page.timer.olimpContinuity = olimpContinuity * 1000;

                App.page.timer.timer();
            }

        };
    }
});
