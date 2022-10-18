<?php
/*
 * File name: BusinessReviewFactory.php
 * Last modified: 2022.02.12 at 02:17:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */


use App\Models\Booking;
use App\Models\BusinessReview;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(BusinessReview::class, function (Faker $faker) {
    return [
        "review" => $faker->realText(100),
        "rate" => $faker->numberBetween(1, 5),
        "booking_id" => Booking::all()->random()->id,
    ];
});
