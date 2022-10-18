<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOfferAcceptedByFieldInArticleRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('article_requests', function (Blueprint $table) {
            $table->integer('offer_accepted_by_business_id')->nullable()->unsigned();
            $table->foreign('offer_accepted_by_business_id')->references('id')->on('businesses')->onDelete('set null')->onUpdate('set null');
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
