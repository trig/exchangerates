<?php

namespace App\Providers;

use App\Contracts\ExchangeRateProvider;
use App\Providers\Base\BaseEchangeServiceProvider;
use DOMDocument;
use DOMElement;
use DOMXPath;

class CBRFExchangeRatesProvider extends BaseEchangeServiceProvider implements ExchangeRateProvider {

    const SERVICE_URL = 'http://www.cbr.ru/scripts/XML_daily.asp';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function boot() {
        $this->app->bind(self::class, function($app) {
            $service = new CBRFExchangeRatesProvider($app);
            $service->setRequestor($app->make(WebRequestorServiceProvider::class));

            return $service;
        });
    }

    /**
     * @inheritdoc
     */
    public function getRateValues($currencies = 'USD, EUR') {
        $currencies = $this->normalizeCurrencies($currencies);

        $now = time();

        $url = self::SERVICE_URL . '?' . http_build_query([
                    'date_req' => gmdate('d/m/Y', $now)
        ]);

        $result = [
            'date' => gmdate('Y-m-d H.i.s', $now),
            'rates' => []
        ];

        $response = $this->requestor->request('GET', $url, null, [
            'Content-Type' => 'application/xml;charset=utf-8'
        ]);

        if (!$response->isOk()) {
            \Log::error(sprintf('[%s]Can not retun rates due to API failure. Status: %s', self::class, $response->getStatusCode()));
            return $result;
        }

        $doc = new DOMDocument();
        $doc->loadXML($response->getContent());

        $xpath = new DOMXPath($doc);

        foreach ($currencies as $c) {
            /* @var $node DOMElement */
            $node = $xpath->query("//Valute/CharCode[text()=\"{$c}\"]")->item(0);
            $result['rates'][$c] = $xpath->query($node->parentNode->getNodePath() . '/Value')->item(0)->nodeValue;
        }


        return $result;
    }

}
