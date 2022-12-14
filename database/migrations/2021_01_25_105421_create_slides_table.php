<?php
/*
 * File name: 2021_01_25_105421_create_slides_table.php
 * Last modified: 2022.02.14 at 09:14:40
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSlidesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order')->unsigned()->default(0)->nullable();
            $table->longText('text')->nullable();
            $table->longText('button')->nullable();
            $table->string('text_position', 50)->default('start')->nullable();
            $table->string('text_color', 36)->nullable();
            $table->string('button_color', 36)->nullable();
            $table->string('background_color', 36)->nullable();
            $table->string('indicator_color', 36)->nullable();
            $table->string('image_fit', 50)->default('cover')->nullable();
            $table->integer('article_id')->unsigned()->nullable();
            $table->integer('business_id')->unsigned()->nullable();
            $table->boolean('enabled')->default(1)->nullable();
            $table->timestamps();
            $table->foreign('article_id')->references('id')->on('article')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slides');
    }
}
