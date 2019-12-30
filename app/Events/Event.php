<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Class Event
 *
 * @package App\Events
 */
abstract class Event
{
    use SerializesModels;

    public $id;
    public $relations;
    public $class;
    public $connection;
    public $keyBy;
}
