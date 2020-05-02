<?php

use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Class PermissionsTableSeeder
 */
class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Permission::create([
            'id'   => Permission::ID_USERS,
            'name' => 'Users'
        ]);

        Permission::create([
            'id'   => Permission::ID_ROLES,
            'name' => 'Roles'
        ]);

        Permission::create([
            'id'   => Permission::ID_TASKS,
            'name' => 'Tasks'
        ]);
    }
}
