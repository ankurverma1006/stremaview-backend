<?php

use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('notification_templates')->delete();

    	DB::table('notification_templates')->insert([
    		[
		        'type' => 'NEW_VIDEO',
		        'subject'=>"'<%video_name%>' in <%site_name%>",
		        'content'=>"'<%video_name%>' video uploaded in '<%category_name%>' Category, don't miss the video from <%site_name%>",
		        'status'=> APPROVED,
		    ],
		    [
		        
		        'type'=>'EDIT_VIDEO',
	            'subject' => "'<%video_name%>' in <%site_name%>",
	            'content' => "'<%video_name%>' video uploaded in '<%category_name%>' Category, don't miss the video from <%site_name%>",
	            'status'=> APPROVED,
		    ],
		   
		]);
    }
}
