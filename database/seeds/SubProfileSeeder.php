<?php

use Illuminate\Database\Seeder;

class SubProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /**
         * Used to add demo users - subprofiles details
         *
         * Created BY: 
         *
         * Edited By: vidhya
         */

        $users = DB::table('users')->whereIn('email' , ['user@streamview.com' , 'test@streamview.com'])->get();

        $arr = [];

        foreach ($users as $key => $value) {

            DB::table('sub_profiles')->where('user_id' , $value->id)->delete();

            # code...
            $arr[] = [
                'user_id' => $value->id,
                'name' => $value->name,
                'picture' => $value->picture,
                'status' =>1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

        }

        DB::table('sub_profiles')->insert(
            $arr
        );

    }
}
