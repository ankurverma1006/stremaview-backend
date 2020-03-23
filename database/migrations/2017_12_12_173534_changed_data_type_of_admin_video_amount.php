<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangedDataTypeOfAdminVideoAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_videos', function (Blueprint $table) {
            DB::statement("ALTER TABLE `admin_videos` CHANGE `amount` `amount` FLOAT( 11 ) NOT NULL DEFAULT '0'");
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
            //
        });
    }
}
