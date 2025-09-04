<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\UpdateOrderStatus;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // تحدّث قائمة خدمات Dhru كل 5 دقائق
        $schedule->command('dhru:sync-services --type=1')->everyFiveMinutes();

        // تحديث حالة الطلبات من المزود الخارجي كل 3 دقائق
        $schedule->command('orders:update-status')->everyThreeMinutes();

        // أمثلة موجودة مسبقًا (معلّقة)
        // $schedule->command('tweetcell:update')->everyMinute();
        // $schedule->command('tweetcellKontor:update')->everyMinute();
        // $schedule->job(new UpdateOrderStatus())->everyMinute();
    }
}
