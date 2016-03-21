<?php

namespace App\Console\Commands;

use App\Contracts\ExchangeRateProvider;
use App\Contracts\ScheduleConfigurable;
use App\Providers\CBRFExchangeRatesProvider;
use App\Providers\YahooFinanceExchangeRatesProvider;
use App\Services\WebSocketClient;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Query\Builder;
use LogicException;
use function config;

class FetchCurrencyRates extends Command implements ScheduleConfigurable {

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

        /* @var $builder Builder */
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
                throw new LogicException("Provider [$pName] must implement ExchangeRateProvider interface");
            }

            $data[$pName] = [];
            $now = time();
            $ts = date('Y-m-d\TH:i:s', $now);

            $this->line(sprintf("[%s][%s] start processing...", $ts, $pName));

            $rates = $provider->getRateValues($currencyCodes)['rates'];
            $data[$pName] = $rates;

            $this->info(sprintf("[%s][%s] resolved [%s/%s] rates", $ts, $pName, count($rates), $currencyCodesCount));
        }

        $data = json_encode($data);

        $builder->insert([
            [
                'rates' => $data,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);

        (new WebSocketClient(config('services.ws_client.url'), '/broadcast'))->send($data);

        $this->info(sprintf("[%s] data persisted", $ts));
    }

    /**
     * @inheritdoc
     */
    public function setUpSchedule(Schedule $schedule) {
        $schedule->command('currency_rates:fetch')
                ->timezone('UTC')
                ->everyFiveMinutes()
                ->then(function() {
                    \Log::info(sprintf("[sheduler] performed ['currency_rates:fetch'] sheduled task"));
                });
    }

}
