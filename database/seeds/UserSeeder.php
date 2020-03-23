<?php

use Illuminate\Database\Seeder;

use App\Helpers\Helper;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Used to add demo user details
         *
         * Created BY: 
         *
         * Edited By: vidhya
         */

        if(Schema::hasTable('users')) {

            $check_user_details = DB::table('users')->where('email' , 'user@streamview.com')->count();

            if(!$check_user_details) {

                $user_details = DB::table('users')->insert([
                    [
                        'name' => 'User',
                        'email' => 'user@streamview.com',
                        'password' => \Hash::make('123456'),
                        'picture' =>"http://adminview.streamhash.com/placeholder.png",
                        'login_by' =>"manual",
                        'device_type' =>"web",
                        'is_activated' =>1,
                        'status' =>1,
                        'user_type' =>1,
                        'is_verified' =>1,
                        'token' => Helper::generate_token(),
                        'token_expiry' => Helper::generate_token_expiry(),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                ]);

            }

            $check_test_details = DB::table('users')->where('email' , 'test@streamview.com')->count();

            if(!$check_test_details) {

                $test_details = DB::table('users')->insert([
                    [
                        'name' => 'Test',
                        'email' => 'test@streamview.com',
                        'password' => \Hash::make('123456'),
                        'picture' =>"http://adminview.streamhash.com/placeholder.png",
                        'login_by' =>"manual",
                        'device_type' =>"web",
                        'is_activated' =>1,
                        'status' =>1,
                        'user_type' =>1,
                        'is_verified' =>1,
                        'token' => Helper::generate_token(),
                        'token_expiry' => Helper::generate_token_expiry(),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                ]);

            }

        }

    }
}
