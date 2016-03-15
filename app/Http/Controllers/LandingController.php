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
        $currencyCodes = (new \App\Providers\Base\BaseEchangeServiceProvider($app))
                ->getCurrencyCodes();
        
        

        return view('landing', [
            'currency_codes' => $currencyCodes
        ]);
    }

    public function about() {
        return view('about');
    }

    public function contact() {
        return view('contact');
    }
    
}
