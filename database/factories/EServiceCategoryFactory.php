<?php
/*
 * File name: EServiceCategoryFactory.php
 * Last modified: 2022.02.13 at 22:47:27
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */


use App\Models\ArticleCategories;
use App\Models\Article;
use App\Models\EServiceCategory;
use App\Models\Business;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(EServiceCategory::class, function (Faker $faker) {
    return [
        'article_id' => App\Models\ArticleCategories::all()->random()->id,
        'article_categories_id' => Article::all()->random()->id
    ];
});
