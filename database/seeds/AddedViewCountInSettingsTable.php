<?php

use Illuminate\Database\Seeder;

class AddedViewCountInSettingsTable extends Seeder
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
		    	'key' => 'video_viewer_count' ,
		    	'value' => 10,
		    	'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
            [
                'key' => 'amount_per_video' ,
                'value' => 100,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
		]);
    }
}
