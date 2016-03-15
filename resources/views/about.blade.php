@extends('layouts.main')

@section('title', 'About')

@section('content')
<article>
    <h1>
        <a href="{{ route('landing') }}" aria-hidden="true">
            <svg aria-hidden="true" class="octicon octicon-link" height="16" role="img" version="1.1" viewBox="0 0 16 16" width="16"><path d="M4 9h1v1h-1c-1.5 0-3-1.69-3-3.5s1.55-3.5 3-3.5h4c1.45 0 3 1.69 3 3.5 0 1.41-0.91 2.72-2 3.25v-1.16c0.58-0.45 1-1.27 1-2.09 0-1.28-1.02-2.5-2-2.5H4c-0.98 0-2 1.22-2 2.5s1 2.5 2 2.5z m9-3h-1v1h1c1 0 2 1.22 2 2.5s-1.02 2.5-2 2.5H9c-0.98 0-2-1.22-2-2.5 0-0.83 0.42-1.64 1-2.09v-1.16c-1.09 0.53-2 1.84-2 3.25 0 1.81 1.55 3.5 3 3.5h4c1.45 0 3-1.69 3-3.5s-1.5-3.5-3-3.5z"></path></svg>
        </a>
        exchangerates
    </h1>

    <p>demo project that simply fetches two currency exchange providers and display
        that info on web page </p> 

    <p>I've picked Laravel PHP framework for backend and Twitter Bootstrap for frontend</p>

    <ol>
        <li>All currency exchange providers implementing <a href="https://github.com/trig/exchangerates/blob/master/app/Contracts/ExchangeRateProvider.php">ExchangeRateProvider</a> interface</li>
        <li>Implemented <a href="https://github.com/trig/exchangerates/blob/master/app/Providers/CBRFExchangeRatesProvider.php">CBRFExchangeRatesProvider</a> of <a href="http://www.cbr.ru/">Central Bank of Russia</a></li>
        <li>Implemented <a href="https://github.com/trig/exchangerates/blob/master/app/Providers/YahooFinanceExchangeRatesProvider.php">YahooFinanceExchangeRatesProvider</a> of <a href="http://finance.yahoo.com/">Yahoo Finance</a></li>
        <li>Currency rates fetched from server via AJAX calls with autoupdate each 60 seconds</li>
        <li>UI logic was built using jQuery</li>
        <li>All assets (css and js) are built using Gulp</li>
        <li>Each <a href="https://github.com/trig/exchangerates/blob/master/app/Console/Kernel.php#L28">5 minutes</a> there is <a href="https://github.com/trig/exchangerates/blob/master/app/Console/Commands/FetchCurrencyRates.php">task</a> running on server which fetches new data from above providers and saves it in mysql db</li>
    </ol>

    <p>You can see code on GitHub <a href="https://github.com/trig/exchangerates">here</a></p>
</article>
@endsection