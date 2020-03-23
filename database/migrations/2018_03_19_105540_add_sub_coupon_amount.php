<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubCouponAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('user_payments',function($table){

            $table->float('sub_amount')->after('payment_id');
            $table->string('coupon_code')->after('sub_amount');
            $table->string('coupon_amount')->after('coupon_code');
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
        //
        Schema::table('user_payments',function($table){

            $table->dropColumn('sub_amount');
            $table->dropColumn('coupon_code');
            $table->dropColumn('coupon_amount');
        });
    }
}
