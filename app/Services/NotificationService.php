<?php

namespace App\Services;

use App\Events\NotificationEvent;
use App\Models\UserNotification;

/**
 * Class NotificationService
 *
 * @package App\Services
 */
class NotificationService
{
    /**
     * Send notification to user.
     *
     * @param  UserNotification  $notification
     */
    public function sendNotification(UserNotification $notification)
    {
        event(new NotificationEvent($notification->toArray(), $notification->user_id));
    }

    /**
     * Add new user notification.
     *
     * @param $userId
     * @param $message
     * @param $refName
     * @param $refId
     *
     * @return UserNotification
     */
    public function addNotification($userId, $message, $refName, $refId)
    {
        $userNotification = new UserNotification();

        $userNotification->user_id  = $userId;
        $userNotification->message  = $message;
        $userNotification->ref_name = $refName;
        $userNotification->ref_id   = $refId;
        $userNotification->status   = UserNotification::STATUS_UNREAD;

        $userNotification->save();

        return $userNotification;
    }
}
