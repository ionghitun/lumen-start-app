<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;

/**
 * Class User
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
        'language_id',
        'email',
        'password',
        'picture',
        'status',
        'activation_code',
        'forgot_code',
        'forgot_time',
        'facebook_id',
        'twitter_id',
        'google_id'
    ];

    /** @var array */
    protected $hidden = [
        'password',
        'activation_code',
        'forgot_code',
        'forgot_time',
        'facebook_id',
        'twitter_id',
        'google_id'
    ];

    /** @var array */
    protected $visible = [
        'id',
        'name',
        'language_id',
        'email',
        'picture',
        'status',
        'created_at',
        'updated_at',
        'language',
        'userTokens',
        'userNotifications',
        'userTasks',
        'userAssignedTasks'
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

    /**
     * User boot
     */
    protected static function boot()
    {
        parent::boot();

        /** Delete all user associations */
        static::deleting(function ($user) {
            if ($user->userTokens) {
                foreach ($user->userTokens as $userToken) {
                    $userToken->delete();
                }
            }

            if ($user->userNotifications) {
                foreach ($user->userNotifications as $userNotification) {
                    $userNotification->delete();
                }
            }

            if ($user->userTasks) {
                foreach ($user->userTasks as $userTask) {
                    $userTask->delete();
                }
            }

            if ($user->userAssignedTasks) {
                foreach ($user->userAssignedTasks as $userAssignedTask) {
                    $userAssignedTask->assigned_user_id = $userAssignedTask->user_id;

                    $userAssignedTask->save();
                }
            }
        });
    }

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
}
