<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_order_options', function (Blueprint $table) {
            $table->integer('article_order_id')->unsigned();
            $table->integer('option_id')->unsigned();
            $table->double('price', 8, 2)->default(0);
            $table->primary([ 'article_order_id','option_id']);
            $table->foreign('article_order_id')->references('id')->on('article_orders')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('option_id')->references('id')->on('options')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_order_options');
    }
}
