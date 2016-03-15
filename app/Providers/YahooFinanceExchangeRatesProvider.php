<?php

namespace App\Providers;

use App\Contracts\ExchangeRateProvider;
use App\Providers\Base\BaseExchangeServiceProvider;

class YahooFinanceExchangeRatesProvider extends BaseExchangeServiceProvider implements ExchangeRateProvider {

    /**
     * @var string
     */
    const SERVICE_URL = 'https://query.yahooapis.com/v1/public/yql';

    /**
     * @var string
     */
    const NAME = 'yahoo_finance';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function boot() {
        $this->app->bind(self::class, function($app) {
            $service = new YahooFinanceExchangeRatesProvider($app);
            $service->setRequestor($app->make(WebRequestorServiceProvider::class));

            return $service;
        });
    }

    /**
     * @inheritdoc
     */
    public function getRateValues($currencies = 'USD, EUR') {
        $currencies = $this->normalizeCurrencies($currencies);

        /* append RUB to currencies */
        $currencies = array_map(function($_) {
            return $_ . 'RUB';
        }, $currencies);


        /* ?q=select+*+from+yahoo.finance.xchange+where+pair+=+"USDRUB,EURRUB"&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback= */
        $url = str_replace('USDRUB,EURRUB', implode(',', $currencies), self::SERVICE_URL . '?q=select+*+from+yahoo.finance.xchange+where+pair+=+"USDRUB,EURRUB"&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=');

        $response = $this->requestor->request('GET', $url, null, [
            'Content-Type' => 'application/json;charset=utf8'
        ]);

        $now = time();

        $result = [
            'date' => gmdate('Y-m-d H.i.s', $now),
            'rates' => []
        ];

        if (!$response->isOk()) {
            \Log::error(sprintf('[%s]Can not retun rates due to API failure. Status: %s', self::class, $response->getStatusCode()));
            return $result;
        }

        /* {"query":{"count":2,"created":"2016-03-14T09:16:59Z","lang":"en-US","results":{"rate":[{"id":"AZNRUB","Name":"AZN/RUB","Rate":"42.3034","Date":"3/14/2016","Time":"9:17am","Ask":"43.4815","Bid":"42.3034"},{"id":"AMDRUB","Name":"AMD/RUB","Rate":"0.1433","Date":"3/14/2016","Time":"9:17am","Ask":"0.1433","Bid":"0.1433"}]}}} */
        $jsonResponse = json_decode($response->getContent(), true);
        if (!is_array($jsonResponse) || empty($jsonResponse['query']['results']['rate'])) {
            \Log::error(sprintf('[%s]Not a JSON (or not processable format) from: %s', self::class, $response->getContent()));
            return $result;
        }

        foreach ($jsonResponse['query']['results']['rate'] as $yahooRate) {
            $cNormalized = preg_replace('/RUB$/', '', $yahooRate['id']);
            if (in_array($yahooRate['id'], $currencies) && 'N/A' != $yahooRate['Rate']) {
                $result['rates'][$cNormalized] = $yahooRate['Rate'];
            }
        }


        return $result;
    }

}
