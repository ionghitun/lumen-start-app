<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserToken
 *
 * @package App\Models
 */
class UserToken extends Model
{
    /** @var int */
    const TYPE_REMEMBER_ME = 1;
    public $id;
    public $user_id;
    public $token;
    public $type;
    public $expire_on;
    public $created_at;
    public $updated_at;
    /** @var bool */
    public $timestamps = true;

    /** @var string */
    protected $table = 'user_tokens';

    /** @var array */
    protected $fillable = [
        'user_id',
        'token',
        'type',
        'expire_on'
    ];

    /** @var array */
    protected $visible = [
        'id',
        'user_id',
        'token',
        'type',
        'expire_on',
        'created_at',
        'updated_at',
        'user'
    ];

    /** @var array */
    protected $sortable = [
        'id',
        'token',
        'type',
        'expire_on',
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
