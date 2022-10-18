<?php
/*
 * File name: 2022_02_26_090930_update_to_v120.php
 * Last modified: 2022.03.10 at 22:37:47
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateToV120 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("UPDATE `media` SET `custom_properties` = REPLACE(`custom_properties`,',\"generated_conversions\":{\"thumb\":true,\"icon\":true}','') WHERE `media`.`model_type` = 'App\\Models\\Category' OR `media`.`model_type` = 'App\\Models\\Option'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
