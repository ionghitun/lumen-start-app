<?php

use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Class UsersTableSeeder
 */
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('parola'),
            'status' => User::STATUS_CONFIRMED,
            'language_id' => Language::ID_EN,
            'role_id' => Role::ID_ADMIN
        ]);
    }
}
