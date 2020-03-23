<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddV41Migrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('is_home_display')->default(0)->after('status');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->integer('is_home_display')->default(0)->after('status');
        });

        Schema::table('genres', function (Blueprint $table) {
            $table->integer('is_home_display')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('is_home_display');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn('is_home_display');
        });

        Schema::table('genres', function (Blueprint $table) {
            $table->dropColumn('is_home_display');
        });
    }
}
