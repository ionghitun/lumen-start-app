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
    protected $visible = [];

    /** @var array */
    protected $sortable = [];

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
