<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_schedule', function (Blueprint $table) {
            $table->id();
            $table->integer('article_id')->unsigned();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->time('duration');
            $table->string('repeat')->default('never');
            $table->text('days')->nullable();
            $table->text('recurrence_rules')->nullable();
            $table->timestamps();
            $table->foreign('article_id')->references('id')->on('article')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_schedule');
    }
}
