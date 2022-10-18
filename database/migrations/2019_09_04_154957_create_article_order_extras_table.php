<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArticleOrderExtrasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_order_extras', function (Blueprint $table) {
            $table->integer('article_order_id')->unsigned();
            $table->integer('extra_id')->unsigned();
            $table->double('price', 8, 2)->default(0);
            $table->primary([ 'article_order_id','extra_id']);
            $table->foreign('article_order_id')->references('id')->on('article_orders')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('extra_id')->references('id')->on('extras')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('article_order_extras');
    }
}
