<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPpvAmountToPayPerViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pay_per_views', function($table) {
            $table->float('ppv_amount')->after('payment_id');
            $table->string('coupon_code');
            $table->float('coupon_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('pay_per_views',function($table) {

            $table->dropColumn('ppv_amount');
             $table->dropColumn('coupon_code');
            $table->dropColumn('coupon_amount');
        });
    }
}
