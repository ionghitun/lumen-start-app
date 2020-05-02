<?php

namespace App\Console;

use App\Console\Commands\DeleteExpiredTokensCommand;
use App\Console\Commands\TaskNotificationsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel
 *
 * @package App\Console
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DeleteExpiredTokensCommand::class,
        TaskNotificationsCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('delete:expiredTokens')
                 ->daily()->at('4:00')
                 ->appendOutputTo(storage_path('logs/cron_delete_expired_tokens.log'));

        $schedule->command('send:taskNotifications')
                 ->daily()->at('8:00')
                 ->appendOutputTo(storage_path('logs/cron_send_task_notifications.log'));
    }
}
