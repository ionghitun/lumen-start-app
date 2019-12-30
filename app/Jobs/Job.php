<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class Job
 *
 * @package App\Jobs
 */
abstract class Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $id;
    public $relations;
    public $class;
    public $keyBy;
}
