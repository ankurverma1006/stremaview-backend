<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Log;
use App\AdminVideo;
use App\User;
use App\Helpers\Helper;

use App\Page;

use Setting;

class SendVideoMail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $videoId;

    protected $video_type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($videoId, $video_type)
    {
        $this->videoId = $videoId;

        $this->video_type = $video_type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Log::info("Inside Queue Mail : ". 'Success');

        $video_details = AdminVideo::where('id', $this->videoId)->first();

        if ($video_details) {

            $users = User::where('is_verified', USER_EMAIL_VERIFIED)->where('email_notification_status', ON)->get();

            \Log::info("Count of users ".count($users));

            $help_page = Page::where('type', 'help')->first();

            $help_id = $help_page ? $help_page->id : 1;

            $admin_video_id = $this->videoId;

            $help_link = Setting::get('ANGULAR_SITE_URL')."page/".$help_id;

            $video_link = Setting::get('ANGULAR_SITE_URL')."video/".$admin_video_id;

            $recent_videos = Helper::recently_added($web = NULL , $skip = 0, $take = 12, $admin_video_id);

            $recent_videos_data = [];

            foreach ($recent_videos as $key => $recent_video_details) {

                $r_data['default_image'] = $recent_video_details->default_image;

                $r_data['video_link'] = Setting::get('ANGULAR_SITE_URL')."video/".$recent_video_details->admin_video_id;

                array_push($recent_videos_data, $r_data);
            }
            
            foreach ($users as $key => $value) {

                $email_data = [];

                $email_data['name'] = $value->name;

                $email_data['category_name'] = $video_details->category ? $video_details->category->name : '';

                $email_data['template_type'] = $this->video_type;

                $email_data['subscriber_name'] = $value ? $value->name : '';

                $email_data['video_id'] = $this->videoId;

                $email_data['video_name'] = $video_details->title;

                $email_data['video_image'] = $video_details->default_image;

                $email_data['help_link'] = $help_link;

                $email_data['play_video_link'] = $email_data['wishlist_link'] = $video_link;

                $email_data['recent_videos'] = $recent_videos_data;

                $page = "emails.video_notification";

                $email = $value->email;
                
                Helper::send_email($page,$subject = null,$email,$email_data);
           
            }   
        
        }
    }
}
