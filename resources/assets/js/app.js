(function ($) {

    $.cache.progressTasks = {
        'yhoo': 0,
        'cbrf': 0
    };

    $.cache.currencyCode = '';
    $.cache.currencyProvider = '';

    function fetchRate(provider) {
        $('.' + provider + '_progress').parent().hide();
        $.getJSON('/ajax/get_rates', {provider: provider}).done(function (d) {
            if (undefined != d.payload[$.cache.currencyCode]) {
                updateRateValue(provider, d.payload[$.cache.currencyCode]);

            } else {
                $('#' + provider + '_results').text('n/a');
            }
        });
    }

    function updateRateValue(provider, value) {
        $('#' + provider + '_results').fadeOut('fast', function () {
            $(this).text(value)
                    .fadeIn('fast');
            updateAjaxProgress(provider);
        });
    }

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

    $(document).on('change', '.js_currency_code', function () {
        $.cache.currencyProvider = $(this).data('provider');

        $.cache.currencyCode = $(this).val();


        fetchRate($.cache.currencyProvider);
    });

    $(document).on('ready', function () {
        if (undefined != window.WebSocket) {
            var ws = new WebSocket('ws://' + window.location.hostname + ':5555/broadcast');
            ws.onmessage = function (evt) {
                var data = JSON.parse(evt.data),
                        /* map data for web socket keys */
                        providersMap = {
                            cbrf: 'cbrf',
                            yhoo: 'yahoo_finance'
                        },
                /* Bootstrap dialog */
                newDataElem = $('<div class="js_new_data alert alert-info alert-dismissible" role="alert" style="display:none;">'
                        + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
                        + '<strong>New rates available! ' + (new Date).toLocaleString() + '</strong>'
                        + '</div>');
                
                /* show/hide dialog */
                if (!$('.js_new_data').length) {
                    newDataElem.prependTo($('.page_content'));
                } else {
                    $('.page_content .js_new_data').fadeOut('fast', function () {
                        $(this).replaceWith(newDataElem);
                    });
                }
                newDataElem.fadeIn('fast', function () {
                    setTimeout(function () {
                        $('.js_new_data').fadeOut('slow', function () {
                            $(this).remove();
                        });
                    }, 10000);
                });

                /* check if websocket data contain current rate if yes - then update value */
                if ($.cache.currencyCode
                        && $.cache.currencyProvider
                        && undefined !== data[providersMap[$.cache.currencyProvider]]
                        && undefined !== data[providersMap[$.cache.currencyProvider]][$.cache.currencyCode]) {
                    updateRateValue($.cache.currencyProvider, data[providersMap[$.cache.currencyProvider]][data[providersMap[$.cache.currencyProvider]]]);
                }

            }
        }
    });

})(jQuery)