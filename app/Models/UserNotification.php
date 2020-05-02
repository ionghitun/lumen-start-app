<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use IonGhitun\MysqlEncryption\Models\BaseModel;

/**
 * Class UserNotification
 *
 * @property int $id
 * @property int $user_id
 * @property string $message
 * @property string|null $ref_name
 * @property int|null $ref_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property User $user
 *
 * @method static Builder|UserNotification newModelQuery()
 * @method static Builder|UserNotification newQuery()
 * @method static Builder|UserNotification query()
 * @method static Builder|UserNotification whereCreatedAt($value)
 * @method static Builder|UserNotification whereDeletedAt($value)
 * @method static Builder|UserNotification whereId($value)
 * @method static Builder|UserNotification whereMessage($value)
 * @method static Builder|UserNotification whereRefId($value)
 * @method static Builder|UserNotification whereRefName($value)
 * @method static Builder|UserNotification whereStatus($value)
 * @method static Builder|UserNotification whereUpdatedAt($value)
 * @method static Builder|UserNotification whereUserId($value)
 * @method static QueryBuilder|UserNotification onlyTrashed()
 * @method static QueryBuilder|UserNotification withTrashed()
 * @method static QueryBuilder|UserNotification withoutTrashed()
 * @method static Builder|BaseModel orWhereEncrypted($column, $value)
 * @method static Builder|BaseModel orWhereNotEncrypted($column, $value)
 * @method static Builder|BaseModel orderByEncrypted($column, $direction)
 * @method static Builder|BaseModel whereEncrypted($column, $value)
 * @method static Builder|BaseModel whereNotEncrypted($column, $value)
 *
 * @package App\Models
 */
class UserNotification extends Model
{
    use SoftDeletes;

    /** @var int */
    const STATUS_UNREAD = 0;

    /** @var int */
    const STATUS_READ = 1;

    /** @var bool */
    public $timestamps = true;

    /** @var string */
    protected $table = 'user_notifications';

    /** @var array */
    protected $fillable = [
        'user_id',
        'message',
        'ref_name',
        'ref_id',
        'status'
    ];

    /** @var array */
    protected $visible = [
        'id',
        'user_id',
        'message',
        'ref_name',
        'ref_id',
        'status',
        'created_at',
        'updated_at',
        'user'
    ];

    /** @var array */
    protected $casts = [
        'status' => 'int'
    ];

    /** @var array */
    protected $sortable = [
        'id',
        'message',
        'status',
        'created_at',
        'updated_at',
    ];

    /** @var array */
    protected $searchable = [
        'message'
    ];

    /** @var array */
    protected $filterable = [
        'user_id',
        'ref_id',
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
}
