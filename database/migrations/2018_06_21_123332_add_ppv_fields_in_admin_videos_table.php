<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPpvFieldsInAdminVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_videos', function (Blueprint $table) {
            $table->tinyInteger('is_pay_per_view')->after('watch_count');
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
             $table->dropColumn('is_pay_per_view');
        });
    }
}
