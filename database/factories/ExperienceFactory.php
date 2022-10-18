<?php
/*
 * File name: ExperienceFactory.php
 * Last modified: 2022.02.02 at 21:16:22
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */


use App\Models\Experience;
use App\Models\Business;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Experience::class, function (Faker $faker) {
    return [
        'title' => $faker->text(127),
        'description' => $faker->realText(),
        'business_id' => Business::all()->random()->id
    ];
});

$factory->state(Experience::class, 'title_more_127_char', function (Faker $faker) {
    return [
        'title' => $faker->paragraph(20),
    ];
});

$factory->state(Experience::class, 'not_exist_business_id', function (Faker $faker) {
    return [
        'business_id' => 500000, // not exist id
    ];
});
