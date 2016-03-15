<?php

namespace App\Http\Controllers;

use App\Providers\CBRFExchangeRatesProvider;
use App\Providers\YahooFinanceExchangeRatesProvider;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Traits\JsonControllerTrait;

class AjaxController extends Controller {

    use JsonControllerTrait;

    /**
     * Fetches currency rates from database and outputs as JSON
     * @param Request $request
     */
    function getRateByProvider(Request $request) {
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

        /* cahing */
        try {
            $rates = \Cache::remember($provider, 1, function() use ($provider, $providerNameMap) {
                        /* @var $builder Builder */
                        $builder = \DB::table('currency_rates');

                        $rates = $builder->select('rates')->latest()->limit(1)->get();

                        if (!count($rates)) {
                            throw new \RuntimeException('Dataset empty');
                        }

                        if (!$rates = json_decode($rates[0]->rates, true)) {
                            throw new \RuntimeException('bad data provided from database');
                        }

                        return $rates[$providerNameMap[$provider]];
                    });

            return $this->renderSuccessResponse($rates);
        } catch (\Exception $e) {
            return $this->renderErrorResponse(['unable fetch data from database']);
        }
    }

}
