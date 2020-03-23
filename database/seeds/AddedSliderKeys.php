<?php

use Illuminate\Database\Seeder;

class AddedSliderKeys extends Seeder
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
		    	'key' => 'home_page_bg_image' ,
		    	'value' => envfile('APP_URL').'/images/home_page_bg_image.jpg',
		    	'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		    [
		    	'key' => 'common_bg_image' ,
		    	'value' => envfile('APP_URL').'/images/login-bg.jpg',
		    	'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ]
		]);
    }
}
