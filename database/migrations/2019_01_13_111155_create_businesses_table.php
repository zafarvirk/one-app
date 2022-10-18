<?php
/*
 * File name: 2021_01_13_111155_create_salons_table.php
 * Last modified: 2022.02.14 at 09:14:40
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBusinessesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('name')->nullable();
            $table->integer('business_category_id')->unsigned();
            $table->integer('address_id')->nullable();
            $table->string('latitude', 24)->nullable();
            $table->string('longitude', 24)->nullable();
            $table->longText('description')->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('mobile_number', 50)->nullable();
            $table->text('information')->nullable();
            $table->double('admin_commission', 8, 2)->nullable()->default(0);
            $table->double('delivery_fee', 8, 2)->nullable()->default(0);
            $table->double('delivery_range', 8, 2)->nullable()->default(0);//added
            $table->double('default_tax', 8, 2)->nullable()->default(0); // //added
            $table->double('availability_range', 9, 2)->nullable()->default(0);
            $table->boolean('available')->nullable()->default(1);
            $table->boolean('featured')->nullable()->default(0);
            $table->boolean('accepted')->nullable()->default(0);
            $table->boolean('closed')->nullable()->default(0); // //added
            $table->boolean('active')->nullable()->default(0); // //added
            $table->boolean('available_for_delivery')->nullable()->default(1); //added
            $table->timestamps();
            $table->foreign('business_category_id')->references('id')->on('business_categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('businesses');
    }
}
