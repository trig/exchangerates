<?php

namespace App\Contracts;

interface ExchangeRateProvider {

    /**
     * @param array|string $currencies 3 chars codes delimitted by comma ar an array
     * 
     * values in 'rates' return key must be the same as in provided $currencies parameter
     * 
     * @return array
     * [
     *  'date' => '2016-03-02 10.23.59',
     *  'rates' => ['USD' => 73.0865, 'EUR' => 80,1231]
     * ]
     */
    public function getRateValues($currencies = 'USD, EUR');
}
