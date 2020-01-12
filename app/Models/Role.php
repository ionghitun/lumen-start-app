<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Role
 *
 * @package App\Models
 */
class Role extends Model
{
    use SoftDeletes;

    /** @var int */
    const ID_ADMIN = 1;

    /** @var int */
    const ID_USER = 2;

    /** @var bool */
    public $timestamps = true;

    /** @var string */
    protected $table = 'roles';

    /** @var array */
    protected $fillable = [
        'name'
    ];

    /** @var array */
    protected $visible = [
        'id',
        'name',
        'users',
        'rolePermissions'
    ];

    /** @var array */
    protected $sortable = [
        'id',
        'name'
    ];

    /** @var array */
    protected $searchable = [
        'name'
    ];

    /**
     * Role users.
     *
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Role permissions.
     *
     * @return HasMany
     */
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'id');
    }
}
