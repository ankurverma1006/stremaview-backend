<?php

use Illuminate\Database\Seeder;

class SocialEmailSuffix extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('settings')->insert([
    		[
		        'key' => 'social_email_suffix',
		        'value' => '@streamhash.com'
		    ],
		]);
    }
}
