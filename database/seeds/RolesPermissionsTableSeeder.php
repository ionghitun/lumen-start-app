<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

/**
 * Class RolesPermissionsTableSeeder
 */
class RolesPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        RolePermission::create([
            'role_id'       => Role::ID_ADMIN,
            'permission_id' => Permission::ID_USERS,
            'read'          => RolePermission::PERMISSION_TRUE,
            'create'        => RolePermission::PERMISSION_TRUE,
            'update'        => RolePermission::PERMISSION_TRUE,
            'delete'        => RolePermission::PERMISSION_TRUE,
            'manage'        => RolePermission::MANAGE_ALL
        ]);

        RolePermission::create([
            'role_id'       => Role::ID_ADMIN,
            'permission_id' => Permission::ID_ROLES,
            'read'          => RolePermission::PERMISSION_TRUE,
            'create'        => RolePermission::PERMISSION_TRUE,
            'update'        => RolePermission::PERMISSION_TRUE,
            'delete'        => RolePermission::PERMISSION_TRUE,
            'manage'        => RolePermission::MANAGE_ALL
        ]);

        RolePermission::create([
            'role_id'       => Role::ID_ADMIN,
            'permission_id' => Permission::ID_TASKS,
            'read'          => RolePermission::PERMISSION_TRUE,
            'create'        => RolePermission::PERMISSION_TRUE,
            'update'        => RolePermission::PERMISSION_TRUE,
            'delete'        => RolePermission::PERMISSION_TRUE,
            'manage'        => RolePermission::MANAGE_ALL
        ]);

        RolePermission::create([
            'role_id'       => Role::ID_USER,
            'permission_id' => Permission::ID_TASKS,
            'read'          => RolePermission::PERMISSION_TRUE,
            'create'        => RolePermission::PERMISSION_TRUE,
            'update'        => RolePermission::PERMISSION_TRUE,
            'delete'        => RolePermission::PERMISSION_TRUE,
            'manage'        => RolePermission::MANAGE_OWN
        ]);
    }
}
