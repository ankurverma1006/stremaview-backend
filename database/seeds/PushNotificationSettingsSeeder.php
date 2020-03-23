<?php

use Illuminate\Database\Seeder;

class PushNotificationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
    		[
		        'key' => 'is_push_notification',
		        'value' => ON
		    ]
		]);
    }
}
