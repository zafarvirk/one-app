<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArticleNutritionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_nutrition', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 127);
            $table->integer('quantity')->unsigned()->nullable()->default(0);
            $table->integer('article_id')->unsigned();
            $table->timestamps();
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
        Schema::drop('nutrition');
    }
}
