<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_admins', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('picture');
            $table->string('description');
            $table->integer('status');
            $table->enum('gender',array('male','female','others'));
            $table->string('mobile');
            $table->string('paypal_email');
            $table->string('address');
            $table->string('token');
            $table->string('token_expiry');
            $table->string('timezone');
            $table->rememberToken();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sub_admins');
    }
}
