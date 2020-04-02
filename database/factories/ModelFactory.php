<?php

use App\Models\Language;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserTask;
use App\Models\UserToken;
use Faker\Generator;
use Faker\Provider\DateTime;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Faker\Provider\Miscellaneous;
use Faker\Provider\Person;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @var Generator $faker */
$faker = new Generator();

$faker->addProvider(new Lorem($faker));
$faker->addProvider(new Miscellaneous($faker));
$faker->addProvider(new Person($faker));
$faker->addProvider(new Internet($faker));
$faker->addProvider(new DateTime($faker));

/** @var Factory $factory */
$factory->define(Language::class, function () use ($faker) {
    return [
        'name' => $faker->word,
        'code' => $faker->languageCode
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

$factory->define(User::class, function () use ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => Hash::make($faker->password),
        'status' => $faker->randomElement([User::STATUS_UNCONFIRMED, User::STATUS_CONFIRMED, User::STATUS_EMAIL_UNCONFIRMED])
    ];
});

$factory->define(UserToken::class, function () use ($faker) {
    return [
        'token' => Str::random(32),
        'type' => UserToken::TYPE_REMEMBER_ME,
        'expire_on' => $faker->dateTime("+ 1 month")->format('Y-m-d H:i:s')
    ];
});

$factory->define(UserNotification::class, function () use ($faker) {
    return [
        'message' => $faker->text,
        'status' => $faker->randomElement([UserNotification::STATUS_UNREAD, UserNotification::STATUS_READ])
    ];
});

$factory->define(UserTask::class, function () use ($faker) {
    return [
        'description' => $faker->text,
        'deadline' => $faker->date('Y-m-d', "+ 1 month"),
        'status' => $faker->randomElement([UserTask::STATUS_ASSIGNED, UserTask::STATUS_COMPLETED])
    ];
});
