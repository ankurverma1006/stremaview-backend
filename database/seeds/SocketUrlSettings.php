<?php

use Illuminate\Database\Seeder;

class SocketUrlSettings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert(
        	[
	            'key' => "socket_url",
	            'value' => "http://your-ip-address:3003/",
        	]
        );
    }
}
