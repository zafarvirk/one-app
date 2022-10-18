<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsInBusinessCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_categories', function (Blueprint $table) {
            $table->string('color', 36)->after('default');
            $table->integer('order')->nullable()->default(0);
            $table->boolean('featured')->nullable()->default(0);
            $table->integer('parent_id')->nullable()->unsigned();
            $table->foreign('parent_id')->references('id')->on('business_categories')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_categories', function (Blueprint $table) {
            //
        });
    }
}
