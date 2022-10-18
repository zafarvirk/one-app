<?php
/*
 * File name: EServiceReviewFactory.php
 * Last modified: 2021.02.04 at 18:49:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */


use App\Models\Article;
use App\Models\EServiceReview;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(EServiceReview::class, function (Faker $faker) {
    return [
        "review" => $faker->realText(100),
        "rate" => $faker->numberBetween(1, 5),
        "user_id" => User::role('customer')->get()->random()->id,
        "e_service_id" => Article::all()->random()->id,
    ];
});
