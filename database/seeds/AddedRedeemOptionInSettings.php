<?php

use Illuminate\Database\Seeder;

class AddedRedeemOptionInSettings extends Seeder
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
		    	'key' => 'minimum_redeem' ,
		    	'value' => 1,
		    	'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s')
		    ],
            [
                'key' => 'redeem_control' ,
                'value' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'key' => 'admin_commission' ,
                'value' => 10,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'key' => 'user_commission' ,
                'value' => 90,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
		]);
    }
}
