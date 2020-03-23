<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVideoFieldsInAdminVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_videos', function (Blueprint $table) {
            $table->string("video_gif_image")->after('default_image');
            $table->string("video_image_mobile")->after('video_gif_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_videos', function (Blueprint $table) {
            $table->dropColumn("video_gif_image");
            $table->dropColumn("video_image_mobile");
        });
    }
}
