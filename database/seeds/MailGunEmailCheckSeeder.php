<?php

use Illuminate\Database\Seeder;

class MailGunEmailCheckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// This key used to enable the email address check process (verify the email address is valid or not)

        DB::table('settings')->insert([
    		[
		        'key' => 'is_mailgun_check_email',
		        'value' => 0
		    ]
		]);
    }
}
