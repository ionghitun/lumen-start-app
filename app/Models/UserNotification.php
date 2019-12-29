<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserNotification
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
    protected $sortable = [
        'id',
        'message',
        'status',
        'created_at',
        'updated_at',
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
