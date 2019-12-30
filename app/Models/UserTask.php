<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserTask
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
    public $id;
    public $user_id;
    public $assigned_user_id;
    public $description;
    public $deadline;
    public $status;
    public $created_at;
    public $updated_at;
    public $deleted_at;
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
    protected $sortable = [
        'id',
        'description',
        'deadline',
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

    /**
     * User assigned.
     *
     * @return BelongsTo
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'id');
    }
}
