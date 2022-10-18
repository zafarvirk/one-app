<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHighlightBusinessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('highlight_businesses', function (Blueprint $table) {
            $table->integer('highlight_id')->unsigned();
            $table->integer('business_id')->unsigned();
            $table->primary(['highlight_id', 'business_id']);
            $table->foreign('highlight_id')->references('id')->on('highlights')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('highlight_businesses');
    }
}
