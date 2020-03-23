<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Version4TablesAndChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add sub profile id to wishlist table
        
        Schema::table('wishlists', function (Blueprint $table) {
            $table->integer('sub_profile_id')->after('user_id');
        });

        // Add sub profile id to user history table
        
        Schema::table('user_histories', function (Blueprint $table) {
            $table->integer('sub_profile_id')->after('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('email_notification_status')->after('status');
            $table->integer('push_notification_status')->after('email_notification_status');
        });

        Schema::table('continue_watching_videos', function (Blueprint $table) {
            $table->string('duration_in_seconds')->after('duration');
        });

        Schema::table('cards', function (Blueprint $table) {
            $table->string('month')->after('last_four');
            $table->string('year')->after('month');
        });

        Schema::table('admin_videos', function (Blueprint $table) {
            $table->integer('is_original_video')->default(0)->after('status');
            $table->string('mobile_image')->default("")->after('default_image');

        });

        Schema::table('admins', function (Blueprint $table) {
            $table->integer('status')->default(APPROVED)->after('address');
            $table->string('role')->default(ADMIN)->after('status');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('title')->default("")->before('heading');
            $table->integer('status')->default(1)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropColumn('sub_profile_id');
        });

        Schema::table('user_histories', function (Blueprint $table) {
            $table->dropColumn('sub_profile_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_notification_status');
            $table->dropColumn('push_notification_status');
        });

        Schema::table('continue_watching_videos', function (Blueprint $table) {
            $table->dropColumn('duration_in_seconds');
        });

        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('month');
            $table->dropColumn('year');
        });

        Schema::table('admin_videos', function (Blueprint $table) {
            $table->dropColumn('is_original_video');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('role');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('status');
        });
    }
}
