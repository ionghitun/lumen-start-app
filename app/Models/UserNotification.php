<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
