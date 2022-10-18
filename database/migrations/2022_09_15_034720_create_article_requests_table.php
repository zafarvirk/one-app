<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('required_datetime')->nullable();
            $table->enum('type' , ['pickup' , 'delivery'])->nullable()->default('pickup');
            $table->integer('business_category_id')->nullable()->unsigned();
            $table->integer('transaction_status_id')->nullable()->unsigned();
            $table->integer('address_id')->nullable()->unsigned();
            $table->timestamps();
            $table->foreign('business_category_id')->references('id')->on('business_categories')->onDelete('set null')->onUpdate('set null');
            $table->foreign('transaction_status_id')->references('id')->on('transaction_statuses')->onDelete('set null')->onUpdate('set null');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_requests');
    }
}
