<?php

use App\Models\Language;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserTask;
use App\Models\UserToken;
use Illuminate\Database\Seeder;

/**
 * Class FakerSeeder
 */
class FakerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $languages = factory(Language::class, 5)->create();

        $permissions = factory(Permission::class, 20)->create();

        $roles = factory(Role::class, 5)->create()
                                        ->each(function ($role) use ($permissions) {
                                            $role->permissions()->attach(
                                                $permissions->random(rand(2, 5))->pluck('id')->toArray(),
                                                [
                                                    'read'   => random_int(0, 1),
                                                    'create' => random_int(0, 1),
                                                    'update' => random_int(0, 1),
                                                    'delete' => random_int(0, 1),
                                                    'manage' => random_int(0, 1)
                                                ]
                                            );
                                        });


        $users = factory(User::class, 50)->create([
            'language_id' => function () use ($languages) {
                return $languages->random(1)->pluck('id')[0];
            },
            'role_id'     => function () use ($roles) {
                return $roles->random(1)->pluck('id')[0];
            }
        ]);

        factory(UserToken::class, 10)->create([
            'user_id' => function () use ($users) {
                return $users->random(1)->pluck('id')[0];
            }
        ]);

        factory(UserNotification::class, 30)->create([
            'user_id' => function () use ($users) {
                return $users->random(1)->pluck('id')[0];
            }
        ]);

        factory(UserTask::class, 30)->create([
            'user_id'          => function () use ($users) {
                return $users->random(1)->pluck('id')[0];
            },
            'assigned_user_id' => function () use ($users) {
                return $users->random(1)->pluck('id')[0];
            }
        ]);
    }
}
