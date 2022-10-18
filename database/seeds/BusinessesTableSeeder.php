<?php
/*
 * File name: SalonsTableSeeder.php
 * Last modified: 2022.02.15 at 15:05:57
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use App\Models\Business;
use App\Models\SalonTax;
use App\Models\SalonUser;
use Illuminate\Database\Seeder;

class BusinessesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('businesses')->truncate();


        factory(Business::class, 10)->create();
        try {
            factory(SalonUser::class, 10)->create();
        } catch (Exception $e) {
        }
        try {
            factory(SalonUser::class, 10)->create();
        } catch (Exception $e) {
        }
        try {
            factory(SalonUser::class, 10)->create();
        } catch (Exception $e) {
        }
        try {
            factory(SalonTax::class, 10)->create();
        } catch (Exception $e) {
        }
        try {
            factory(SalonTax::class, 10)->create();
        } catch (Exception $e) {
        }
        try {
            factory(SalonTax::class, 10)->create();
        } catch (Exception $e) {
        }

    }
}
