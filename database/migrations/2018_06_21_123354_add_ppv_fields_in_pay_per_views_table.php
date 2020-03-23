<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPpvFieldsInPayPerViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pay_per_views', function (Blueprint $table) {
            $table->tinyInteger('is_watched')->after('coupon_reason');
            $table->date('paid_date')->after('is_watched');
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
            $table->dropColumn('is_watched');
            $table->dropColumn('paid_date');
        });
    }
}
