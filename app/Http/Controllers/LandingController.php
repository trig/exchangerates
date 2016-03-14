<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use function view;

class LandingController extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    public function landing(Application $app) {
        /* @var $provider \App\Contracts\ExchangeRateProvider */
        $provider = $app[\App\Providers\YahooFinanceExchangeRatesProvider::class];
        $rates = $provider->getRateValues('AFA , AMD');
        return print_r($rates, true);
        return view('landing');
    }
    
    public function about() {
        return view('about');
    }
    
     public function contact() {
        return view('contact');
    }

}
