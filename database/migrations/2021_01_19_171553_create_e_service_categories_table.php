<?php
/*
 * File name: 2021_01_19_171553_create_e_service_categories_table.php
 * Last modified: 2022.02.14 at 09:14:40
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEServiceCategoriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('e_service_categories', function (Blueprint $table) {
            $table->integer('article_id')->unsigned();
            $table->integer('article_categories_id')->unsigned();
            $table->primary(['article_id', 'article_categories_id']);
            $table->foreign('article_id')->references('id')->on('article')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('article_categories_id')->references('id')->on('article_categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('e_service_categories');
    }
}
