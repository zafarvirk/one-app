<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArticleOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->double('price', 8, 2)->default(0);
            $table->integer('quantity')->unsigned()->default(0);
            $table->integer('article_id')->unsigned();
            $table->integer('order_id')->unsigned();
            $table->timestamps();
            $table->foreign('article_id')->references('id')->on('article')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('food_orders');
    }
}
