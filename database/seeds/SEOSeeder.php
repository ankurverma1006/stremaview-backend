<?php

use Illuminate\Database\Seeder;

class SEOSeeder extends Seeder
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
		        'key' => 'meta_title',
		        'value' => "STREAMVIEW",
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		    [
		        'key' => 'meta_description',
		        'value' => "STREAMVIEW",
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		    [
		        'key' => 'meta_author',
		        'value' => "STREAMVIEW",
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],

		    [
		        'key' => 'meta_keywords',
		        'value' => "STREAMVIEW",
		        'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
		]);
    }
}
