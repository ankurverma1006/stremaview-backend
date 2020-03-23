<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCouponStatusFieldsInUserPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->tinyInteger('is_coupon_applied')->after('is_cancelled');
            $table->text('coupon_reason')->after('is_coupon_applied');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->dropColumn('is_coupon_applied');
            $table->dropColumn('coupon_reason');
        });
    }
}
