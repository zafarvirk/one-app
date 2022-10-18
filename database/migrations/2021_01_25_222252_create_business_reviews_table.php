<?php
/*
 * File name: 2021_01_25_222252_create_business_reviews_table.php
 * Last modified: 2022.02.14 at 09:14:40
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBusinessReviewsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->text('review')->nullable();
            $table->decimal('rate', 3, 2)->default(0);
            $table->integer('booking_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('business_id')->unsigned();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_reviews');
    }
}
