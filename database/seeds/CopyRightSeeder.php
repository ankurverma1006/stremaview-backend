<?php

use Illuminate\Database\Seeder;

class CopyRightSeeder extends Seeder
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
	            'key' => "copyright_content",
	            'value' => "Copyrights 2018 . All rights reserved.",
        	],
        	[
	            'key' => "contact_email",
	            'value' => "",
        	],
        	[
	            'key' => "contact_address",
	            'value' => "",
        	],
        	[
	            'key' => "contact_mobile",
	            'value' => "",
        	]
        ]);
    }
}
