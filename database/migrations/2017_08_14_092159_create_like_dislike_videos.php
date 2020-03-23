<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLikeDislikeVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('like_dislike_videos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_video_id');
            $table->integer('user_id');
            $table->integer('sub_profile_id');
            $table->integer('like_status');
            $table->integer('dislike_status');
            $table->integer('status');
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
        Schema::drop('like_dislike_videos');
    }
}
