<?php
/*
 * File name: 2021_01_19_135951_create_e_services_table.php
 * Last modified: 2022.03.12 at 02:37:16
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArticleTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('name')->nullable();
            $table->enum('type', ['service', 'product', 'class'])->default('service');
            $table->double('price', 10, 2)->default(0);
            $table->double('discount_price', 10, 2)->nullable()->default(0);
            $table->string('duration', 16)->nullable();
            $table->longText('description')->nullable();
            $table->text('ingredients')->nullable();
            $table->double('package_items_count', 9, 2)->nullable()->default(0); // added
            $table->double('weight', 9, 2)->nullable()->default(0);
            $table->string('unit', 127)->nullable(); // added
            $table->boolean('featured')->nullable()->default(0);
            $table->boolean('deliverable')->nullable()->default(1); // added
            $table->boolean('enable_booking')->nullable()->default(1);
            $table->boolean('enable_at_customer_address')->nullable()->default(1);
            $table->boolean('enable_at_business')->nullable()->default(1);
            $table->boolean('available')->nullable()->default(1);
            $table->integer('business_id')->unsigned();
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
        Schema::dropIfExists('article');
    }
}
