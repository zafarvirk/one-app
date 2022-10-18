<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_request_id')->unsigned();
            $table->string('quote_amount')->nullable();
            $table->enum('status' , ['pending' ,'rejected' , 'accepted'])->nullable()->default('pending');
            $table->integer('user_id')->unsigned();
            $table->integer('business_id')->unsigned();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('article_request_id')->references('id')->on('article_requests')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_offers');
    }
}
