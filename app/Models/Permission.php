<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Permission
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Collection|Role[] $roles
 * @property-read Collection|RolePermission[] $rolePermissions
 *
 * @package App\Models
 */
class Permission extends Model
{
    use SoftDeletes;

    /** @var int */
    const ID_USERS = 1;

    /** @var int */
    const ID_ROLES = 2;

    /** @var int */
    const ID_TASKS = 3;

    /** @var bool */
    public $timestamps = true;

    /** @var string */
    protected $table = 'permissions';

    /** @var array */
    protected $fillable = [
        'name'
    ];

    /** @var array */
    protected $visible = [
        'id',
        'name',
        'roles',
        'access',
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
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id')
            ->as('access')
            ->using(RolePermission::class)
            ->withPivot([
                'read',
                'create',
                'update',
                'delete',
                'manage'
            ]);
    }

    /**
     * Role permissions.
     *
     * @return HasMany
     */
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'permission_id', 'id');
    }
}
