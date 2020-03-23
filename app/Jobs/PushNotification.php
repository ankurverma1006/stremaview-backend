<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Repositories\PushNotificationRepository as PushRepo;

use Log;

use Setting;

class PushNotification extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $push_notification_type;

    protected $title;

    protected $message;

    protected $data;

    protected $register_ids;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($push_notification_type = PUSH_TO_ALL , $title , $message, $data = [] , $register_ids = [])
    {
        $this->push_notification_type = $push_notification_type;

        $this->title = $title;

        $this->message = $message;

        $this->data = $data;

        $this->register_ids = $register_ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("PushNotification Job Queue: Start");

        if(Setting::get('is_push_notification') == ON) {

            if($this->push_notification_type == PUSH_TO_ALL) {
                
                Log::info("PushNotification Job Queue: PUSH_TO_ALL");

                PushRepo::send_push_notification($this->push_notification_type , $this->title , $this->message, $this->data);
            
            }

            if($this->push_notification_type == PUSH_TO_ANDROID) {

                Log::info("PushNotification Job Queue: PUSH_TO_ANDROID");

                PushRepo::push_notification_android($this->register_ids , $this->title , $this->message);
            
            }

            if($this->push_notification_type == PUSH_TO_IOS) {

                Log::info("PushNotification Job Queue: PUSH_TO_IOS");

                PushRepo::push_notification_ios($this->register_ids , $this->title , $this->message);
            }
        } else {

            Log::info("PushNotification disabled by admin");
        }

        Log::info("PushNotification Job Queue: END");

    }
}
