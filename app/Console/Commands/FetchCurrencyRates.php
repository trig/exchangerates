<?php

namespace App\Console\Commands;

use App\Contracts\ExchangeRateProvider;
use App\Providers\CBRFExchangeRatesProvider;
use App\Providers\YahooFinanceExchangeRatesProvider;
use Illuminate\Console\Command;

class FetchCurrencyRates extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency_rates:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches and persists currency rates from cbr.ru and yahoo to database';

    /**
     *
     * @var ExchangeRateProvider[]
     */
    protected $providers = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CBRFExchangeRatesProvider $cbrf, YahooFinanceExchangeRatesProvider $yahoo) {
        parent::__construct();

        $this->providers[CBRFExchangeRatesProvider::NAME] = $cbrf;
        $this->providers[YahooFinanceExchangeRatesProvider::NAME] = $yahoo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        date_default_timezone_set('UTC');

        $currencyCodes = array_keys(current($this->providers)->getAllCurrencyCodes());
        $currencyCodesCount = count($currencyCodes);

        /* @var $builder \Illuminate\Database\Query\Builder */
        $builder = \DB::table('currency_rates');

        /* storing max 1000 entries */
        $totalEntries = $builder->count('id');
        if (1000 < $totalEntries) {
            $this->warn('removing old entries...');
            $builder->delete($builder->newQuery()
                            ->select('id')
                            ->from('currency_rates')
                            ->orderBy('id')
                            ->limit(1)->first('id')->id);
        }

        $data = [];
        foreach ($this->providers as $pName => $provider) {

            if (!($provider instanceof ExchangeRateProvider)) {
                throw new \LogicException("Provider [$pName] must implement ExchangeRateProvider interface");
            }
            
            $data[$pName] = [];
            $now = time();
            $ts = date('Y-m-d\TH:i:s', $now);

            $this->line(sprintf("[%s][%s] start processing...", $ts, $pName));

            $rates = $provider->getRateValues($currencyCodes)['rates'];
            $data[$pName] = $rates;

            $this->info(sprintf("[%s][%s] resolved [%s/%s] rates", $ts, $pName, count($rates), $currencyCodesCount));
        }



        $builder->insert([
            [
                'rates' => json_encode($data),
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);

        $this->info(sprintf("[%s] data persisted", $ts));
    }

}
