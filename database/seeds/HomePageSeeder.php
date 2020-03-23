<?php

use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
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
		        'key' => 'home_banner_heading',
		        'value' => "See what's next.",
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		    [
		        'key' => 'home_banner_description',
		        'value' => "WATCH ANYWHERE. CANCEL ANYTIME.",
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		    [
		        'key' => 'home_about_site',
		        'value' => "Streamview is programmed to start subscription based on-demand video streaming sites like Netflix and Amazon Prime. Any business idea with this core concept can be easily developed using Streamview. From admin uploading a video to users making payment to users watching the videos, it’s all automated by a dynamic and responsive admin panel with multiple monetization channels.",
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],

		    [
		        'key' => 'home_cancel_content',
		        'value' => "If you decide Streamview isn't for you - no problem. No commitment. Cancel online at anytime.",
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		    		    [
		        'key' => 'home_browse_desktop_image',
		        'value' => 'http://demo.streamhash.com/img/lap.png',
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		    		    [
		        'key' => 'home_browse_tv_image',
		        'value' => 'http://demo.streamhash.com/img/tv-ui.png',
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		    		    [
		        'key' => 'home_browse_mobile_image',
		        'value' => 'http://demo.streamhash.com/img/mobile.png',
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		    		    [
		        'key' => 'home_cancel_image',
		        'value' => 'http://demo.streamhash.com/img/cancel.png',
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		]);
    }
}
