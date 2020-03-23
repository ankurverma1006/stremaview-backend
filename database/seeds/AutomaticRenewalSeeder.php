<?php

use Illuminate\Database\Seeder;

class AutomaticRenewalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('email_templates')->insert([
	        //
	        [
	        	'template_type'=>AUTOMATIC_RENEWAL,
	            'subject' => "Automatic Renewal Notification",
	            'description' => "Your subscription is renewed automatically.",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s')
        	]
        ]);
    }
}
