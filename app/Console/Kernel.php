<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule)
    {
        $schedule->command('maintenance:reminder')
            ->dailyAt('18:30');

        $reminderTime = setting('report_period_reminder_time', '08:00');
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $reminderTime)) {
            $reminderTime = '08:00';
        }

        $schedule->command('reports:period-end-reminder')
            ->dailyAt($reminderTime)
            ->timezone(config('app.timezone', 'Asia/Jakarta'))
            ->withoutOverlapping()
            ->onOneServer();
    }



    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
