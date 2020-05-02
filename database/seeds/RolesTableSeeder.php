<?php

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Class RolesTableSeeder
 */
class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Role::create([
            'id'   => Role::ID_ADMIN,
            'name' => 'Admin'
        ]);

        Role::create([
            'id'   => Role::ID_USER,
            'name' => 'User'
        ]);
    }
}
