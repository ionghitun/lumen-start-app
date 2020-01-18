<?php

use App\Models\Language;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserTask;
use App\Models\UserToken;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/** @var Generator $faker */
$faker = new Generator();

/** @var Factory $factory */
$factory->define(Language::class, function () use ($faker) {
    $languageCode = $faker->languageCode;

    return [
        'name' => $languageCode,
        'code' => $languageCode
    ];
});

$factory->define(Permission::class, function () use ($faker) {
    return [
        'name' => $faker->word
    ];
});

$factory->define(Role::class, function () use ($faker) {
    return [
        'name' => $faker->word
    ];
});

$factory->define(RolePermission::class, function () use ($faker) {
    return [
        'read' => $faker->numberBetween(0, 1),
        'create' => $faker->numberBetween(0, 1),
        'update' => $faker->numberBetween(0, 1),
        'delete' => $faker->numberBetween(0, 1),
        'manage' => $faker->numberBetween(0, 1)
    ];
});

$factory->define(User::class, function () use ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});

$factory->define(UserToken::class, function () use ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});

$factory->define(UserNotification::class, function () use ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});

$factory->define(UserTask::class, function () use ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});
