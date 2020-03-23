<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommissionFieldsToPayPerViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pay_per_views', function (Blueprint $table) {
            $table->string('currency')->before('amount');
            $table->float('admin_amount')->after('amount');
            $table->float('moderator_amount')->after('admin_amount');
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
            $table->dropColumn('currency');
            $table->dropColumn('admin_amount');
            $table->dropColumn('moderator_amount');
        });
    }
}
