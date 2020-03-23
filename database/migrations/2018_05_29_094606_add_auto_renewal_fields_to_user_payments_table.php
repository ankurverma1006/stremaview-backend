<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoRenewalFieldsToUserPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->integer('from_auto_renewed')->after('status');
            $table->string('reason_auto_renewal_cancel')->after('from_auto_renewed');
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
            $table->dropColumn('reason_auto_renewal_cancel');
            $table->dropColumn('from_auto_renewed');
        });
    }
}
