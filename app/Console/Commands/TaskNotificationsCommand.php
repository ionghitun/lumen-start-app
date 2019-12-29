<?php

namespace App\Console\Commands;

use App\Constants\TranslationCode;
use App\Models\UserTask;
use App\Services\LogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class TaskNotificationsCommand
 *
 * Send notifications to assigned user when an uncompleted task has deadline today.
 * Send notifications to users added the task when the task is uncompleted and deadline passed by a day.
 * Should be running once a day.
 *
 * @package App\Console\Commands
 */
class TaskNotificationsCommand extends Command
{
    /** @var string */
    protected $signature = "send:taskNotifications";

    /** @var string */
    protected $description = "Send notifications when a task is expiring or is expired.";

    /**
     * Command handle
     *
     * Identify uncompleted tasks that have deadline today or the deadline passed by a day and send notifications.
     */
    public function handle()
    {
        try {
            $this->info("Command [send:taskNotifications] start: " . Carbon::now()->format('Y-m-d H:i:s'));

            $notificationService = new NotificationService();

            DB::beginTransaction();

            /** @var Collection $expiringUserTasks */
            $expiringUserTasks = UserTask::where('status', UserTask::STATUS_ASSIGNED)
                ->where('deadline', Carbon::now()->format('Y-m-d'))
                ->get();

            $this->info("Found " . $expiringUserTasks->count() . " expiring tasks.");

            foreach ($expiringUserTasks as $expiringUserTask) {
                $userNotification = $notificationService->addNotification($expiringUserTask->assigned_user_id, TranslationCode::USER_TASK_EXPIRING, 'userTask', $expiringUserTask->id);

                $notificationService->sendNotification($userNotification);
            }

            /** @var Collection $expiredUserTasks */
            $expiredUserTasks = UserTask::where('status', UserTask::STATUS_ASSIGNED)
                ->where('deadline', Carbon::now()->subDay()->format('Y-m-d'))
                ->get();

            $this->info("Found " . $expiredUserTasks->count() . " expired tasks.");

            foreach ($expiredUserTasks as $expiredUserTask) {
                $userNotification = $notificationService->addNotification($expiredUserTask->assigned_user_id, TranslationCode::USER_TASK_EXPIRED, 'userTask', $expiredUserTask->id);

                $notificationService->sendNotification($userNotification);
            }

            DB::commit();

            $this->info("Command [send:taskNotifications] end: " . Carbon::now()->format('Y-m-d H:i:s'));
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            $this->error($e->getMessage());
        }
    }
}
