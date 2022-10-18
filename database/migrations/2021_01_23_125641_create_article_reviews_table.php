<?php
/*
 * File name: 2021_01_23_125641_create_e_service_reviews_table.php
 * Last modified: 2021.01.23 at 13:56:41
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArticleReviewsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->text('review')->nullable();
            $table->decimal('rate', 3, 2)->default(0);
            $table->integer('user_id')->unsigned();
            $table->integer('article_id')->unsigned();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('article_id')->references('id')->on('article')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('e_service_reviews');
    }
}
