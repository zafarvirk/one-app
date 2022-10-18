<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdateFieldArticleRequestsInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('article_requests', function (Blueprint $table) {
            $table->enum('scope' , ['area' , 'city', 'province', 'country'])->nullable();
            $table->integer('no_of_offers')->default(0)->nullable();
            $table->integer('merchants_informed')->default(0)->nullable();
            $table->integer('order_id')->nullable();
            $table->string('address_from_text')->nullable();
            $table->string('address_from_coordinates')->nullable();
            $table->enum('request_type' , ['delivery' , 'order', 'booking'])->nullable();
            $table->enum('price_type' , ['fixed' , 'range', 'starting_from'])->nullable();
            $table->string('price')->nullable();
            $table->string('price_from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('article_requests', function (Blueprint $table) {
            //
        });
    }
}
