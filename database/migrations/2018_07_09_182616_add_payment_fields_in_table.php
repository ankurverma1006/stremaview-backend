<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentFieldsInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('redeem_requests', function (Blueprint $table) {
            $table->string('payment_id')->after('paid_amount');
            $table->float('admin_paid_amount')->after('payment_id')->comment('Temporary Column');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('redeem_requests', function (Blueprint $table) {
            $table->dropColumn('payment_id');
            $table->dropColumn('admin_paid_amount')->comment('Temporary Column');
        });
    }
}
