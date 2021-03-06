<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use IonGhitun\MysqlEncryption\Models\BaseModel;

/**
 * Class RolePermission
 *
 * @property int $id
 * @property int $role_id
 * @property int $permission_id
 * @property int $read
 * @property int $create
 * @property int $update
 * @property int $delete
 * @property int $manage
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Role $role
 * @property Permission $permission
 *
 * @method static Builder|RolePermission newModelQuery()
 * @method static Builder|RolePermission newQuery()
 * @method static Builder|RolePermission query()
 * @method static Builder|RolePermission whereCreate($value)
 * @method static Builder|RolePermission whereCreatedAt($value)
 * @method static Builder|RolePermission whereDelete($value)
 * @method static Builder|RolePermission whereId($value)
 * @method static Builder|RolePermission whereManage($value)
 * @method static Builder|RolePermission wherePermissionId($value)
 * @method static Builder|RolePermission whereRead($value)
 * @method static Builder|RolePermission whereRoleId($value)
 * @method static Builder|RolePermission whereUpdate($value)
 * @method static Builder|RolePermission whereUpdatedAt($value)
 * @method static Builder|BaseModel orWhereEncrypted($column, $value)
 * @method static Builder|BaseModel orWhereNotEncrypted($column, $value)
 * @method static Builder|BaseModel orderByEncrypted($column, $direction)
 * @method static Builder|BaseModel whereEncrypted($column, $value)
 * @method static Builder|BaseModel whereNotEncrypted($column, $value)
 *
 * @package App\Models
 */
class RolePermission extends Pivot
{
    /** @var int */
    const PERMISSION_FALSE = 0;

    /** @var int */
    const PERMISSION_TRUE = 1;

    /** @var int */
    const MANAGE_OWN = 0;

    /** @var int */
    const MANAGE_ALL = 1;

    /** @var bool */
    public $incrementing = true;

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
        'read',
        'create',
        'update',
        'delete',
        'manage',
        'role',
        'permission'
    ];

    /** @var array */
    protected $hidden = [
        'role_id',
        'permission_id'
    ];

    /** @var array */
    protected $casts = [
        'read'   => 'int',
        'create' => 'int',
        'update' => 'int',
        'delete' => 'int',
        'manage' => 'int',
    ];

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
