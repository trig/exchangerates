<?php

namespace App\Console;

use App\Console\Commands\FetchCurrencyRates;
use App\Console\Commands\WebSocketServer;
use App\Contracts\ScheduleConfigurable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FetchCurrencyRates::class,
        WebSocketServer::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        foreach ($this->commands as $commandClass) {
            $command = $this->app->make($commandClass);
            if ($command instanceof ScheduleConfigurable) {
                $command->setUpSchedule($schedule);
            }
        }
    }

}
