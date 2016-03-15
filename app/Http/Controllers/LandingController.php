<?php

namespace App\Http\Controllers;

use App\Providers\Base\BaseExchangeServiceProvider;
use App\Providers\CBRFExchangeRatesProvider;
use App\Providers\YahooFinanceExchangeRatesProvider;
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
        
        $baseProvider = new BaseExchangeServiceProvider($app);
        
        $allCurrencyCodes = $baseProvider->getAllCurrencyCodes();
        
        $cbrfSupportedCodes = array_keys($baseProvider->getCurrencyCodeRates(CBRFExchangeRatesProvider::NAME));
        $yhooSupportedCodes = array_keys($baseProvider->getCurrencyCodeRates(YahooFinanceExchangeRatesProvider::NAME));
        
        $cbrfCodes = [];
        $yhooCodes = [];
        
        foreach($cbrfSupportedCodes as $code){
            $cbrfCodes[$code] = $allCurrencyCodes[$code]; 
        }
        
        foreach($yhooSupportedCodes as $code){
            $yhooCodes[$code] = $allCurrencyCodes[$code]; 
        }
        
        

        return view('landing', [
            'cbrf_codes' => $cbrfCodes,
            'yhoo_codes' => $yhooCodes 
        ]);
    }

    public function about() {
        return view('about');
    }

    public function contact() {
        return view('contact');
    }
    
}
