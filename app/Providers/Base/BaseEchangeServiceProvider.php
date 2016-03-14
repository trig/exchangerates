<?php

namespace App\Providers\Base;

use App\Contracts\HttpRequestProvider;
use App\Providers\CBRFExchangeRatesProvider;
use Illuminate\Support\ServiceProvider;

class BaseEchangeServiceProvider extends ServiceProvider {
    
    /**
     *
     * @var HttpRequestProvider;
     */
    protected $requestor;
    
    /**
     * @inheritdoc
     */
    public function boot(){
        
    }
    
    /**
     * @inheritdoc
     */
    public function register() {
        
    }

    /**
     * Set HttpRequestProvider dependency
     * 
     * @param HttpRequestProvider $provider
     * @return CBRFExchangeRatesProvider
     */
    public function setRequestor(HttpRequestProvider $provider) {
        $this->requestor = $provider;

        return $this;
    }
    
    /**
     * Normalizes strings like "eUr,USD, RUB" to array
     * @param array|string $mixedCurrencies
     * @return array
     * [
     *  'EUR', 'USD', 'RUB'
     * ]
     */
    protected function normalizeCurrencies($mixedCurrencies){
         if (!is_array($mixedCurrencies)) {
            $mixedCurrencies = explode(',', $mixedCurrencies);
        }

        return array_map(function($_) {
            return mb_strtoupper(trim($_));
        }, $mixedCurrencies);
    }

}
