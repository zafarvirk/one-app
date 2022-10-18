<?php
/*
 * File name: SlideFactory.php
 * Last modified: 2022.02.02 at 21:16:22
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

/** @var Factory $factory */

use App\Models\Article;
use App\Models\Business;
use App\Models\Slide;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Slide::class, function (Faker $faker) {
    $article = $faker->boolean;
    $array = [
        'order' => $faker->numberBetween(0, 5),
        'text' => $faker->sentence(4),
        'button' => $faker->randomElement(['Discover It', 'Book Now', 'Get Discount']),
        'text_position' => $faker->randomElement(['start', 'end', 'center']),
        'text_color' => '#25d366',
        'button_color' => '#25d366',
        'background_color' => '#ccccdd',
        'indicator_color' => '#25d366',
        'image_fit' => 'cover',
        'article_id' => $article ? Article::all()->random()->id : null,
        'business_id' => !$article ? Business::all()->random()->id : null,
        'enabled' => $faker->boolean,
    ];

    return $array;
});
