# exchangerates

demo project that simply fetches two currency exchange providers and display
 that info on web page.
I've picked Laravel PHP framework for backend and Twitter Bootstrap for frontend

1. All currency exchange providers implementing [ExchangeRateProvider](https://github.com/trig/exchangerates/blob/master/app/Contracts/ExchangeRateProvider.php) interface
2. Implemented [CBRFExchangeRatesProvider](https://github.com/trig/exchangerates/blob/master/app/Providers/CBRFExchangeRatesProvider.php) of [Central Bank of Russia](http://www.cbr.ru/)
3. Implemented [YahooFinanceExchangeRatesProvider](https://github.com/trig/exchangerates/blob/master/app/Providers/YahooFinanceExchangeRatesProvider.php) of [Yahoo Finance](http://finance.yahoo.com/)
4. Currency rates fetched from server via AJAX calls with autoupdate each 60 seconds
5. UI logic was buit using jQuery
6. All assets (css and js) are built using Gulp

You can see live demo [here](http://178.62.176.120:8000/)
