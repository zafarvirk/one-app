<?php
/*
 * File name: SalonLevelFactory.php
 * Last modified: 2022.02.02 at 19:13:53
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */


use App\Models\BusinessCategory;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(BusinessCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->text(48),
        'commission' => $faker->randomFloat(2, 5, 50),
        'disabled' => $faker->boolean(),
    ];
});

$factory->state(BusinessCategory::class, 'name_more_127_char', function (Faker $faker) {
    return [
        'name' => $faker->paragraph(20),
    ];
});

$factory->state(BusinessCategory::class, 'commission_more_100', function (Faker $faker) {
    return [
        'commission' => 101,
    ];
});

$factory->state(BusinessCategory::class, 'commission_less_0', function (Faker $faker) {
    return [
        'commission' => -1,
    ];
});
