<?php

namespace App\Contracts;

use Illuminate\Console\Scheduling\Schedule;

/**
 * This contract is for artisan commands that can be launched on cron based configurations
 */
interface ScheduleConfigurable {

    /**
     * Configure schedule for cron based launches
     * @param Schedule $schedule
     */
    public function setUpSchedule(Schedule $schedule);
}
