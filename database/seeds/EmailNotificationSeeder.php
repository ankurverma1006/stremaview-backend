<?php

use Illuminate\Database\Seeder;

class EmailNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert(
        	[
	            'key' => "email_notification",
	            'value' => 1,
        	]
        );
    }
}
