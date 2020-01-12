<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Permission
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
     * Role permissions.
     *
     * @return HasMany
     */
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'permission_id', 'id');
    }
}
