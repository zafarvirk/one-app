<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAminitiesBusinessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aminities_business', function (Blueprint $table) {
            $table->integer('aminities_id')->unsigned();
            $table->integer('business_id')->unsigned();
            $table->primary(['aminities_id', 'business_id']);
            $table->foreign('aminities_id')->references('id')->on('aminities')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('aminities_business');
    }
}
