<?php

namespace App\Http\Controllers;

use App\Providers\Base\BaseExchangeServiceProvider;
use App\Providers\CBRFExchangeRatesProvider;
use App\Providers\YahooFinanceExchangeRatesProvider;
use App\Traits\JsonControllerTrait;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AjaxController extends Controller {

    use JsonControllerTrait;

    /**
     * Fetches currency rates from database and outputs as JSON
     * @param Request $request
     */
    function getRateByProvider(Request $request, Application $app) {
        if (!$request->has('provider')) {
            return $this->renderErrorResponse(['currency provider not detected']);
        }

        $provider = $request->get('provider', 'cbrf');

        $providerNameMap = [
            'cbrf' => CBRFExchangeRatesProvider::NAME,
            'yhoo' => YahooFinanceExchangeRatesProvider::NAME
        ];

        if (!array_key_exists($provider, $providerNameMap)) {
            return $this->renderErrorResponse(['bad provider name']);
        }
        
        (new \App\Services\WebSocketClient(config('services.ws_client.url'), '/broadcast'))->send('test');
        
        return $this->renderSuccessResponse((new BaseExchangeServiceProvider($app))
                ->getCurrencyCodeRates($providerNameMap[$provider]));

        
    }

}
