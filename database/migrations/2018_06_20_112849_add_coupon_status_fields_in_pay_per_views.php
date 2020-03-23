<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCouponStatusFieldsInPayPerViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pay_per_views', function (Blueprint $table) {
            $table->tinyInteger('is_coupon_applied')->after('expiry_date');
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
        Schema::table('pay_per_views', function (Blueprint $table) {
            $table->dropColumn('is_coupon_applied');
            $table->dropColumn('coupon_reason');
        });
    }
}
