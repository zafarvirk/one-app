<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('price_type', ['free', 'recuring', 'one_time'])->default('free');
            $table->enum('price_frequency', ['day', 'month', 'year'])->default('day');
            $table->double('price', 10, 2)->default(0);
            $table->longText('description')->nullable();
            $table->enum('type', ['package', 'memebership'])->default('package');
            $table->integer('no_of_sessions')->nullable();
            $table->integer('plan_duration')->nullable(); 
            $table->date('custom_start_date')->nullable();
            $table->boolean('allow_canceltion')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
}
