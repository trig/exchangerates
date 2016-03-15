$.cache.progressTasks = {
    'yhoo': 0,
    'cbrf': 0
};

$(document).on('change', '.js_currency_code', function () {
    var provider = $(this).data('provider'),
            code = $(this).val();


    fetchRate(provider);



    function updateAjaxProgress(provider) {
        var currentPercent = 100,
                secondsElapsed = 0,
                progress = $('.' + provider + '_progress');

        if ($.cache.progressTasks[provider]) {
            clearInterval($.cache.progressTasks[provider]);
        }

        $.cache.progressTasks[provider] = setInterval(function () {
            secondsElapsed += 1;
            if (60 == secondsElapsed) {
                progress.parent().hide();
                clearInterval($.cache.progressTasks[provider]);
                fetchRate(provider);
                return;
            }

            if (1 == secondsElapsed) {
                progress.parent().show();
            }

            currentPercent = Math.round((60 - secondsElapsed) / 60 * 100);
            progress.css('width', Math.round(currentPercent, 3) + '%').show();
        }, 1000);
    }

    function fetchRate(provider) {
        $('.' + provider + '_progress').parent().hide();
        $.getJSON('/ajax/get_rates', {provider: provider}).done(function (d) {
            if (undefined != d.payload[code]) {
                $('#' + provider + '_results').fadeOut('fast', function () {
                    $(this).text(d.payload[code])
                            .fadeIn('fast');
                    updateAjaxProgress(provider);
                });

            } else {
                $('#' + provider + '_results').text('n/a');
            }
        });
    }
})

