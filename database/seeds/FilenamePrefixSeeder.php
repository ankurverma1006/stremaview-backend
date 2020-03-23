<?php

use Illuminate\Database\Seeder;

class FilenamePrefixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //prefix_file_name

        DB::table('settings')->insert([
    		[
		        'key' => 'prefix_file_name',
		        'value' => "SV"
		    ]
		]);
    }
}
