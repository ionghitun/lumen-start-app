<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RolePermission
 *
 * @package App\Models
 */
class RolePermission extends Model
{
    use SoftDeletes;

    /** @var int */
    const PERMISSION_FALSE = 0;

    /** @var int */
    const PERMISSION_TRUE = 1;

    /** @var int */
    const MANAGE_OWN = 0;

    /** @var int */
    const MANAGE_ALL = 1;

    /** @var bool */
    public $timestamps = true;

    /** @var string */
    protected $table = 'role_permissions';

    /** @var array */
    protected $fillable = [
        'role_id',
        'permission_id',
        'read',
        'create',
        'update',
        'delete',
        'manage'
    ];

    /** @var array */
    protected $visible = [
        'id',
        'role_id',
        'permission_id',
        'read',
        'create',
        'update',
        'delete',
        'manage',
        'role',
        'permission'
    ];

    /** @var array */
    protected $sortable = [
        'id'
    ];

    /** @var array */
    protected $searchable = [];

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
     * Permission.
     *
     * @return BelongsTo
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}
