<?php

namespace App\Models;

/**
 * Class Language
 *
 * @property int $id
 * @property string $connection
 * @property string $queue
 * @property string $payload
 * @property string $exception
 * @property $failed_at
 *
 * @package App\Models
 */
class FailedJobs extends Model
{
    /** @var string */
    protected $table = 'failed_jobs';
}
