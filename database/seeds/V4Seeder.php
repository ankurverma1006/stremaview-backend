<?php

use Illuminate\Database\Seeder;

class V4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Schema::hasTable('settings')) {

         	DB::table('settings')->insert([
	    		[
                    'key' => 'currency_code',
                    'value' => 'USD',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'key' => 'max_banner_count',
                    'value' => 6,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'key' => 'max_home_count',
                    'value' => 6,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
                    'key' => 'max_origianl_count',
                    'value' => 20,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                [
			        'key' => 'is_home_category_feature',
			        'value' => NO,
			        'created_at' => date('Y-m-d H:i:s'),
			        'updated_at' => date('Y-m-d H:i:s')
			    ]
			]);
    	}
    }
}
