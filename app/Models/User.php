<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;

/**
 * Class User
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $password
 * @property array|null $picture
 * @property int $status
 * @property int $language_id
 * @property int $role_id
 * @property string|null $activation_code
 * @property string|null $forgot_code
 * @property Carbon|null $forgot_time
 * @property string|null $facebook_id
 * @property string|null $google_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Collection|UserToken[] $userTokens
 * @property-read Collection|UserNotification[] $userNotifications
 * @property Language $language
 * @property Role $role
 * @property-read Collection|UserTask[] $userTasks
 * @property-read Collection|UserTask[] $userAssignedTasks
 *
 * @package App\Models
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, SoftDeletes;

    /** @var int */
    const STATUS_UNCONFIRMED = 0;

    /** @var int */
    const STATUS_CONFIRMED = 1;

    /** @var int */
    const STATUS_EMAIL_UNCONFIRMED = 2;

    /** @var bool */
    public $timestamps = true;

    /** @var string */
    protected $table = 'users';

    /** @var array */
    protected $fillable = [
        'name',
        'email',
        'password',
        'picture',
        'status',
        'language_id',
        'role_id',
        'activation_code',
        'forgot_code',
        'forgot_time',
        'facebook_id',
        'google_id'
    ];

    /** @var array */
    protected $hidden = [
        'password',
        'activation_code',
        'forgot_code',
        'forgot_time',
        'facebook_id',
        'google_id'
    ];

    /** @var array */
    protected $visible = [
        'id',
        'name',
        'email',
        'picture',
        'status',
        'language_id',
        'role_id',
        'created_at',
        'updated_at',
        'language',
        'role',
        'userNotifications',
        'userTasks',
        'userAssignedTasks'
    ];

    /** @var array */
    protected $casts = [
        'status' => 'int'
    ];

    /** @var array */
    protected $sortable = [
        'id',
        'name',
        'email',
        'status',
        'created_at',
        'updated_at',
    ];

    /** @var array */
    protected $searchable = [
        'name',
        'email'
    ];

    /** @var array */
    protected $encrypted = [
        'name',
        'email'
    ];

    /** @var array */
    protected $filterable = [
        'status',
        'language_id',
        'role_id'
    ];

    /**
     * User tokens.
     *
     * @return HasMany
     */
    public function userTokens()
    {
        return $this->hasMany(UserToken::class, 'user_id', 'id');
    }

    /**
     * User notifications.
     *
     * @return HasMany
     */
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id', 'id');
    }

    /**
     * Language.
     *
     * @return BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    /**
     * Role.
     *
     * @return BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * User tasks.
     *
     * @return HasMany
     */
    public function userTasks()
    {
        return $this->hasMany(UserTask::class, 'user_id', 'id');
    }

    /**
     * User assigned tasks.
     *
     * @return HasMany
     */
    public function userAssignedTasks()
    {
        return $this->hasMany(UserTask::class, 'assigned_user_id', 'id');
    }

    /**
     * @param $value
     *
     * @return Carbon|null
     */
    public function getForgotTimeAttribute($value)
    {
        if ($value !== null) {
            return Carbon::parse($value);
        }

        return null;
    }

    /**
     * @param $value
     *
     * @return array|null
     */
    public function getPictureAttribute($value)
    {
        if ($value !== null && $value !== '') {
            return json_decode($value, true);
        }

        return null;
    }
}
