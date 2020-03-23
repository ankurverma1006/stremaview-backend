<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedSubscriptionFieldsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
             $table->float('amount_paid')->defined(0)->after('timezone');
            $table->dateTime('expiry_date')->nullable()->after('amount_paid');
            $table->integer('no_of_days')->defined(0)->after('expiry_date');
            $table->integer('one_time_subscription')->comment("0 - Not Subscribed , 1 - Subscribed")->default(0)->after('no_of_days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
