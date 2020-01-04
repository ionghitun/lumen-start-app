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
    protected $signature = 'send:taskNotifications';

    /** @var string */
    protected $description = 'Send notifications when a task is expiring or is expired.';

    /** @var NotificationService */
    protected $notificationService;

    /**
     * TaskNotificationsCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->notificationService = new NotificationService();
    }

    /**
     * Command handle
     */
    public function handle()
    {
        try {
            $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . ']: Command [send:taskNotifications] started.');

            DB::beginTransaction();

            $this->checkExpiringTasks();

            $this->checkExpiredTasks();

            DB::commit();

            $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . ']: Command [send:taskNotifications] ended.');
        } catch (Exception $e) {
            Log::error(LogService::getExceptionTraceAsString($e));

            $this->error($e->getMessage());
        }
    }

    /**
     * Identify uncompleted tasks that have deadline today and send notifications.
     */
    private function checkExpiringTasks()
    {
        /** @var Collection $expiringUserTasks */
        $expiringUserTasks = UserTask::where('status', UserTask::STATUS_ASSIGNED)
            ->where('deadline', Carbon::now()->format('Y-m-d'))
            ->get();

        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . ']: Found ' . $expiringUserTasks->count() . ' expiring tasks.');

        foreach ($expiringUserTasks as $expiringUserTask) {
            $userNotification = $this->notificationService->addNotification(
                $expiringUserTask->assigned_user_id,
                TranslationCode::USER_TASK_EXPIRING,
                'userTask',
                $expiringUserTask->id
            );

            $this->notificationService->sendNotification($userNotification);
        }
    }

    /**
     * Identify uncompleted tasks that have deadline passed by a day and send notifications.
     */
    private function checkExpiredTasks()
    {
        /** @var Collection $expiredUserTasks */
        $expiredUserTasks = UserTask::where('status', UserTask::STATUS_ASSIGNED)
            ->where('deadline', Carbon::now()->subDay()->format('Y-m-d'))
            ->get();

        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . ']: Found ' . $expiredUserTasks->count() . ' expired tasks.');

        foreach ($expiredUserTasks as $expiredUserTask) {
            $userNotification = $this->notificationService->addNotification(
                $expiredUserTask->assigned_user_id,
                TranslationCode::USER_TASK_EXPIRED,
                'userTask',
                $expiredUserTask->id
            );

            $this->notificationService->sendNotification($userNotification);
        }
    }
}
