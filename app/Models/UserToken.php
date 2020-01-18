<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserToken
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property int $type
 * @property Carbon|null $expire_on
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
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
    protected $casts = [
        'type' => 'int'
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
     * @param $value
     *
     * @return Carbon
     */
    public function getExpireOnAttribute($value)
    {
        if ($value !== null) {
            return Carbon::parse($value);
        }

        return null;
    }
}
