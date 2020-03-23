<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDurationOfTheVideoInContinueWatching extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('continue_watching_videos', function (Blueprint $table) {
            $table->string('duration',8)->after('admin_video_id');
            $table->tinyInteger('genre_position')->after('position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('continue_watching_videos', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
}
