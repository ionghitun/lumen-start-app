<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Role
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Collection|User[] $users
 * @property-read Collection|Permission[] $permissions
 * @property-read Collection|RolePermission[] $rolePermissions
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
        'permissions',
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
     * Role users.
     *
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
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
        return $this->hasMany(RolePermission::class, 'role_id', 'id');
    }
}
