<?php

use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
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
	        	'template_type'=>USER_WELCOME,
	            'subject' => "Welcome to <%site_name%>",
	            'description' => "Thanks for signing up! We're very excited to have you on board.",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s')
        	],
        	[
	        	'template_type'=>ADMIN_USER_WELCOME,
	            'subject' => "Welcome to <%site_name%>",
	            'description' => "Thanks for signing up! Where very excited to have you on board.",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s')
        	],
        	[
	        	'template_type'=>FORGOT_PASSWORD,
	            'subject' => "Your new password",
	            'description' => "Your Forgot Password request has been Accepted. Please find your credentials of <%site_name%>, <br> Email : <%email%> <br> Password : <%password%>",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s')
        	],
        	[
	        	'template_type'=>MODERATOR_WELCOME,
	            'subject' => "Welcome to <%site_name%>",
	            'description' => "Congratulations! Admin has made you a Content Creator. Please use the link and details below to login and upload Content.<br> Please find your credentials of <%site_name%>,  <br> Email : <%email%> <br> Password : <%password%>",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s')
        	],
        	[
	        	'template_type'=>PAYMENT_EXPIRED,
	            'subject' => "Payment Notification",
	            'description' => "Your notification has expired. To keep using channel creation  & upload video without interruption, subscribe any one of our plans and continue to upload",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s')
        	],
        	[
	        	'template_type'=>PAYMENT_GOING_TO_EXPIRY,
	            'subject' => "Payment Notification",
	            'description' => "Your subscription will expire soon. Our records indicate that no payment method has been associated with this subscripton account. Go to the subscription plans and provide the required payment information to renew your subscription for watching videos and continue using your profile uninterrupted.",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s')
        	],
        	[
	        	'template_type'=>NEW_VIDEO,
	            'subject' => "'<%video_name%>' in <%site_name%>",
	            'description' => "'<%video_name%>' video uploaded in '<%category_name%>' Category, don't miss the video from <%site_name%>",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s')
        	],
        	[
	        	'template_type'=>EDIT_VIDEO,
	            'subject' => "'<%video_name%>' in <%site_name%>",
	            'description' => "'<%video_name%>' video uploaded in '<%category_name%>' Category, don't miss the video from <%site_name%>",
	            'status'=>1,
	            'created_at'=>date('Y-m-d H:i:s'),
	            'updated_at'=>date('Y-m-d H:i:s'),
        	]
        ]
        );
    }

}
