<?php

use Illuminate\Database\Seeder;

class AddedCustomUsersCount extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert customer user count value
        DB::table('settings')->insert(
        	[
	            'key' => "custom_users_count",
	            'value' => 50,
        	]
        );
    }
}
