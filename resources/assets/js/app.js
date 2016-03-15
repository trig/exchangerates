$(document).on('change', '.js_currency_code', function () {
    var provider = $(this).data('provider'),
            code = $(this).val();
    $.getJSON('/ajax/get_rates', {provider: provider}).done(function (d) {
        if (undefined != d.payload[code]) {
            console.log(code);
            $('#' + provider + '_results').text(d.payload[code]);
        } else {
            console.warn('n/a');
            $('#' + provider + '_results').text('n/a');
        }
    });
})

