<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

/**
 * Class NotificationEvent
 *
 * Use sockets to broadcast notification to user.
 *
 * @package App\Events
 */
class NotificationEvent extends Event implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    /** @var array */
    private $data;

    /** @var int */
    private $userId;

    /**
     * NotificationEvent constructor.
     *
     * Initialize notification data and user that should receive the notification.
     *
     * @param array $data
     * @param $userId
     */
    public function __construct(array $data, $userId)
    {
        $this->data = $data;
        $this->userId = $userId;
    }

    /**
     * Get the channels the notification should broadcast on.
     *
     * @return Channel|Channel[]
     */
    public function broadcastOn()
    {
        return new Channel('user.' . $this->userId);
    }

    /**
     * Notification data
     *
     * @return array
     */
    public function broadcastWith()
    {
        return ['notification' => $this->data];
    }
}
