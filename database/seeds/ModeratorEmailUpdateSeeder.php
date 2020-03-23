<?php

use Illuminate\Database\Seeder;

class ModeratorEmailUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('email_templates')->insert([
        	[
	        	'template_type'=>MODERATOR_UPDATE_MAIL,
	            'subject' => "Email Change Notification",
	            'description' => "You receive this one at your old email address. Please note that this email is a security measure to protect your account in case someone is trying to take it over.  <br> <b> Your New Email Address is : <%email%> </b>",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s')
        	],

        ]);
    }
}
