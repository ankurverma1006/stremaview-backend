<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedPaymentsInModerators extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('moderators', function (Blueprint $table) {
            $table->float('total')->after('timezone');
            $table->float('total_admin_amount')->after('total');
            $table->float('total_user_amount')->after('total_admin_amount');
            $table->float('paid_amount')->after('total_user_amount');
            $table->float('remaining_amount')->after('paid_amount');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('moderators', function (Blueprint $table) {
            //
        });
    }
}
