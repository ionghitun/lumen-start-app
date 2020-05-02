<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use IonGhitun\MysqlEncryption\Models\BaseModel;

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
 * @method static Builder|FailedJobs newModelQuery()
 * @method static Builder|FailedJobs newQuery()
 * @method static Builder|FailedJobs query()
 * @method static Builder|FailedJobs whereConnection($value)
 * @method static Builder|FailedJobs whereException($value)
 * @method static Builder|FailedJobs whereFailedAt($value)
 * @method static Builder|FailedJobs whereId($value)
 * @method static Builder|FailedJobs wherePayload($value)
 * @method static Builder|FailedJobs whereQueue($value)
 * @method static Builder|BaseModel orWhereEncrypted($column, $value)
 * @method static Builder|BaseModel orWhereNotEncrypted($column, $value)
 * @method static Builder|BaseModel orderByEncrypted($column, $direction)
 * @method static Builder|BaseModel whereEncrypted($column, $value)
 * @method static Builder|BaseModel whereNotEncrypted($column, $value)
 *
 * @package App\Models
 */
class FailedJobs extends Model
{
    /** @var string */
    protected $table = 'failed_jobs';
}
