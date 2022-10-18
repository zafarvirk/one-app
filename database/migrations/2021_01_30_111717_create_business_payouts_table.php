<?php
/*
 * File name: 2021_01_30_111717_create_salon_payouts_table.php
 * Last modified: 2022.02.14 at 09:14:40
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBusinessPayoutsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_payouts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->unsigned();
            $table->string('method', 127);
            $table->double('amount', 10, 2)->default(0);
            $table->dateTime('paid_date');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_payouts');
    }
}
