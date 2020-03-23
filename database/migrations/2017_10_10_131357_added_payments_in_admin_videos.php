<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedPaymentsInAdminVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_videos', function (Blueprint $table) {
            $table->float('admin_amount')->after('redeem_amount');
            $table->float('user_amount')->after('admin_amount');
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
