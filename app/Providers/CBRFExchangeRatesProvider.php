<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CBRFExchangeRatesProvider extends ServiceProvider {

   const SERVICE_URL = 'http://www.cbr.ru/scripts/Root.asp?PrtId=SXML';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->app->register($this);
    }

}
