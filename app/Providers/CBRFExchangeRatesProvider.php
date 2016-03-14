<?php

namespace App\Providers;

use App\Contracts\ExchangeRateProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class CBRFExchangeRatesProvider extends ServiceProvider implements ExchangeRateProvider {

    const SERVICE_URL = 'http://www.cbr.ru/scripts/XML_daily.asp';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->app->bind(self::class, function($app){
            return new CBRFExchangeRatesProvider($app);
        });
    }

    /**
     * @inheritdoc
     */
    public function getRateValues($currencies = 'USD, EUR') {

        if (!is_array($currencies)) {
            $currencies = explode(',', $currencies);
        }

        $currencies = array_map(function($_) {
            return trim($_);
        }, $currencies);
        
        
        
        $opts = stream_context_create([
            'http' => [
                'header' => 'Content-Type: application/xml;charset=utf-8',
                'user_agent' => sprintf('exchangerates on Laravel(%s)', Application::VERSION)
            ]
        ]);
        
        $now = time();
        
        $url = self::SERVICE_URL . '?' . http_build_query([
            'date_req' => gmdate('d/m/Y', $now)
        ]);

        $response = file_get_contents($url, false, $opts);
        
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        
        $xpath = new \DOMXPath($doc);
        
        $result = [
            'date' => date('Y-m-d H.i.s'),
            'rates' => []
        ];
        
        foreach($currencies as $c){
            /* @var $node \DOMElement */
            $node = $xpath->query("//Valute/CharCode[text()=\"{$c}\"]")->item(0);
            $result['rates'][$c] = $xpath->query($node->parentNode->getNodePath() . '/Value')->item(0)->nodeValue;
        
        
        return $result;
    }

}
