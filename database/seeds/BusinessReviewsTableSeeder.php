<?php
/*
 * File name: BusinessReviewsTableSeeder.php
 * Last modified: 2022.02.15 at 15:05:57
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use App\Models\BusinessReview;
use Illuminate\Database\Seeder;

class BusinessReviewsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('business_reviews')->truncate();


        factory(BusinessReview::class, 100)->create();

    }
}
