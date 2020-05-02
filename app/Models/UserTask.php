<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use IonGhitun\MysqlEncryption\Models\BaseModel;

/**
 * Class UserTask
 *
 * @property int $id
 * @property int $user_id
 * @property int $assigned_user_id
 * @property string $description
 * @property Carbon $deadline
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property User $user
 * @property User $assignedUser
 *
 * @method static Builder|UserTask newModelQuery()
 * @method static Builder|UserTask newQuery()
 * @method static Builder|UserTask query()
 * @method static Builder|UserTask whereAssignedUserId($value)
 * @method static Builder|UserTask whereCreatedAt($value)
 * @method static Builder|UserTask whereDeadline($value)
 * @method static Builder|UserTask whereDeletedAt($value)
 * @method static Builder|UserTask whereDescription($value)
 * @method static Builder|UserTask whereId($value)
 * @method static Builder|UserTask whereStatus($value)
 * @method static Builder|UserTask whereUpdatedAt($value)
 * @method static Builder|UserTask whereUserId($value)
 * @method static QueryBuilder|UserTask onlyTrashed()
 * @method static QueryBuilder|UserTask withTrashed()
 * @method static QueryBuilder|UserTask withoutTrashed()
 * @method static Builder|BaseModel orWhereEncrypted($column, $value)
 * @method static Builder|BaseModel orWhereNotEncrypted($column, $value)
 * @method static Builder|BaseModel orderByEncrypted($column, $direction)
 * @method static Builder|BaseModel whereEncrypted($column, $value)
 * @method static Builder|BaseModel whereNotEncrypted($column, $value)
 *
 * @package App\Models
 */
class UserTask extends Model
{
    use SoftDeletes;

    /** @var int */
    const STATUS_ASSIGNED = 0;

    /** @var int */
    const STATUS_COMPLETED = 1;

    /** @var bool */
    public $timestamps = true;

    /** @var string */
    protected $table = 'user_tasks';

    /** @var array */
    protected $fillable = [
        'user_id',
        'assigned_user_id',
        'description',
        'deadline',
        'status'
    ];

    /** @var array */
    protected $visible = [
        'id',
        'user_id',
        'assigned_user_id',
        'description',
        'deadline',
        'status',
        'created_at',
        'updated_at',
        'user',
        'assignedUser'
    ];

    /** @var array */
    protected $casts = [
        'status' => 'int'
    ];

    /** @var array */
    protected $sortable = [
        'id',
        'description',
        'deadline',
        'status',
        'created_at',
        'updated_at',
    ];

    /** @var array */
    protected $searchable = [
        'description'
    ];

    /** @var array */
    protected $filterable = [
        'user_id',
        'status'
    ];

    /**
     * User.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * User assigned.
     *
     * @return BelongsTo
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'id');
    }

    /**
     * @param $value
     *
     * @return Carbon
     */
    public function getDeadlineAttribute($value)
    {
        return Carbon::parse($value);
    }
}
