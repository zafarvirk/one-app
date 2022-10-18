<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBusinessCuisinesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_cuisines', function (Blueprint $table) {
            $table->integer('business_category_id')->unsigned();
            $table->integer('business_id')->unsigned();
            $table->primary([ 'business_category_id','business_id']);
            $table->foreign('business_category_id')->references('id')->on('business_categories')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::drop('business_cuisines');
    }
}
