<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('queue:restart')->dailyAt('04:00');
        $schedule->command('queue:checkup')->everyFiveMinutes();

        // $schedule->command('product:datasheet_download')->everyThirtyMinutes();
        // $schedule->command('product:image_download')->everyThirtyMinutes();

        $schedule->command('category:product_count')->dailyAt('03:00'); // Run the task daily at 3:00
        $schedule->command('manufacturer:product_count')->dailyAt('04:15'); // Run the task daily at 3:15

        $schedule->command('finish:export')->everyFifteenMinutes(); // Run the task daily at 3:15
        $schedule->command('finish:import')->dailyAt('08:00'); // Run the task daily at 3:15
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        include base_path('routes/console.php');
    }
}
