<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentModeInPayPerViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pay_per_views', function (Blueprint $table) {
            $table->string('payment_mode', 8)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pay_per_views', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
    }
}
