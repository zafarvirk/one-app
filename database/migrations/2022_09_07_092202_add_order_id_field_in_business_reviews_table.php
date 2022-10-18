<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderIdFieldInBusinessReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_reviews', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->integer('booking_id')->nullable()->unsigned()->change();
            $table->integer('order_id')->nullable()->unsigned()->after('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null')->onUpdate('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_reviews', function (Blueprint $table) {
            //
        });
    }
}
