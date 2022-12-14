<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostReactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_reaction', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('reaction' , ['like' , 'heart' , 'sad' , 'waoo'])->nullable();
            $table->boolean('is_deleted')->nullable()->default(0);
            $table->integer('user_id')->unsigned();
            $table->integer('post_id')->unsigned();
            $table->integer('post_comment_id')->nullable()->unsigned();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('post_comment_id')->references('id')->on('post_comment')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_reaction');
    }
}
