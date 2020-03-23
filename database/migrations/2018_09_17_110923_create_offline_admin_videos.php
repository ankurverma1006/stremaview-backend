<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfflineAdminVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_admin_videos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_video_id');
            $table->integer('user_id');
            $table->integer('sub_profile_id');
            $table->tinyinteger('download_status')->comment('1 - Started Download, 2 - Onprogress, 3 - Pause, 4 - Completed, 5 - Failed/Cancelled,');
            $table->date('download_date');
            $table->date('expiry_date');
            $table->tinyInteger('is_expired')->default(0);
            $table->tinyinteger('status')->default(0);
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
        Schema::drop('offline_admin_videos');
    }
}
