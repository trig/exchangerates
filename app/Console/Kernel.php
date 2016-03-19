<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\FetchCurrencyRates::class,
        Commands\WebSocketServer::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->command('currency_rates:fetch')
                ->timezone('UTC')
                ->everyMinute()
                ->then(function(){
                    \Log::info(sprintf("[sheduler] performed ['currency_rates:fetch'] sheduled task"));
                });
                // @todo schedule aware intaerface and pass schedule to command
    }

}
