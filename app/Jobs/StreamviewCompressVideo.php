<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use File;

use App\Helpers\Helper;

use App\AdminVideo;

use App\Notification;

use App\EmailTemplate;

use Log; 

use App\Category;

use App\Repositories\PushNotificationRepository as PushRepo;

use Setting;

class StreamviewCompressVideo extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $inputFile;
    protected $local_url;
    protected $videoId;
    protected $video_type;
    protected $file_name;
    protected $send_notification;

    // Video Status & is_edit_video - used to maintain previous video approve/decline status

    protected $edit_video_is_approved_status;

    protected $is_edit_video;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inputFile, $local_url, $video_type, $videoId, $file_name, $send_notification , $edit_video_is_approved_status , $is_edit_video)
    {
        Log::info("Inside Construct");
       $this->inputFile = $inputFile;
       $this->local_url = $local_url;
       $this->videoId = $videoId;
       $this->video_type = $video_type;
       $this->file_name = $file_name;
       $this->send_notification = $send_notification;
       $this->edit_video_is_approved_status = $edit_video_is_approved_status;
       $this->is_edit_video = $is_edit_video;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        // Load Video Model

        $video = AdminVideo::where('id', $this->videoId)->first();
     
        $video_size = readFileName($this->inputFile);  

        Log::info("attributes : ". print_r($video_size, true));

        if($video) {

            if ($video_size) {

                $explode_attributes = explode('x', $video_size);

                $attributes['width'] = count($explode_attributes) == 2 ? $explode_attributes[0] : '';

                $attributes['height'] = count($explode_attributes) == 2 ? trim($explode_attributes[1]) : '';


                if ($this->video_type == MAIN_VIDEO) {

                    $video->main_video_compress_status = COMPRESS_SENT_QUEUE;

                    $resolutions = $video->video_resolutions ? explode(',', $video->video_resolutions) : [];

                } else {

                    $video->trailer_compress_status = COMPRESS_SENT_QUEUE;

                    $resolutions = $video->trailer_video_resolutions ? explode(',', $video->trailer_video_resolutions) : [];

                }

                $video->save();

                // Get Video Resolutions

                $array_resolutions = $video_resize_path = $pathnames = [];

                $compress = false;

                foreach ($resolutions as $key => $solution) {

                    $exp = explode('x', $solution);

                    Log::info("Resoltuion : ". print_r($exp, true));

                    // Explode $solution value
                    $getwidth = (count($exp) == 2) ? $exp[0] : 0;

                
                    if ($getwidth <= $attributes['width']) {

                        $compress = true;

                        $FFmpeg = new \FFmpeg;
                        $FFmpeg
                        ->input($this->inputFile)
                        ->size($solution)
                        ->vcodec('h264')
                        ->constantRateFactor('28')
                        ->output(public_path().'/uploads/videos/original/'.$solution.$this->local_url)
                        ->ready();

                        $array_resolutions[] = $solution;

     
                        $video_resize_path[] = Helper::web_url().'/uploads/videos/original/'.$solution.$this->local_url;

                        $pathnames[] = $solution.$this->local_url;
                    }

                    if ($this->video_type == MAIN_VIDEO) {

                        $video->main_video_compress_status = COMPRESS_PROCESSING;

                        $video->compress_status = COMPRESS_MAIN_VIDEO_PROCESSING;

                    } else {

                        $video->trailer_compress_status = COMPRESS_PROCESSING;

                        $video->compress_status = COMPRESS_TRAILER_VIDEO_PROCESSING;

                    }

                    $video->save();


                }

                if ($this->video_type == MAIN_VIDEO) {

                    $video->main_video_compress_status = COMPRESS_COMPLETED;

                    $video->video_resolutions = ($array_resolutions) ? implode(',', $array_resolutions) : null;

                    $video->video_resize_path = ($video_resize_path) ? implode(',', $video_resize_path) : null;

                    Log::info('Main Compress status : '.$video->main_video_compress_status);

                    $video->compress_status = COMPRESS_MAIN_VIDEO_COMPLETED;

                } else {

                    $video->trailer_compress_status = COMPRESS_COMPLETED;

                    $video->trailer_video_resolutions = ($array_resolutions) ? implode(',', $array_resolutions) : null;

                    $video->trailer_resize_path = ($video_resize_path) ? implode(',', $video_resize_path) : null;

                    $video->compress_status = COMPRESS_TRAILER_VIDEO_COMPLETED;
                }

                $video->save();

                if ($array_resolutions) {
                    $myfile = fopen(public_path().'/uploads/smil/'.$this->file_name.'.smil', "w");
                    $txt = '<smil>
                      <head>
                        <meta base="'.\Setting::get('streaming_url').'" />
                      </head>
                      <body>
                        <switch>';
                        $txt .= '<video src="'.$this->local_url.'" height="'.$attributes['height'].'" width="'.$attributes['width'].'" />';
                        foreach ($pathnames as $i => $value) {
                            $resoltionsplit = explode('x', $array_resolutions[$i]);
                            if (count($resoltionsplit))
                            $txt .= '<video src="'.$value.'" height="'.$resoltionsplit[1].'" width="'.$resoltionsplit[0].'" />';
                        }
                     $txt .= '</switch>
                      </body>
                    </smil>';
                    fwrite($myfile, $txt);
                    fclose($myfile);
                
                }

                $video = AdminVideo::find($this->videoId);

                if($video) {

                    Log::info("Compress Type Video : ".$this->video_type);

                    Log::info("Video Compress Status : ".$video->main_video_compress_status);

                    Log::info("Trailer Compress Status : ".$video->trailer_compress_status);

                    Log::info("Main compress Status : ".$video->compress_status);


                    if (in_array($video->main_video_compress_status , [COMPRESS_COMPLETED , COMPRESSION_NOT_HAPPEN]) && in_array($video->trailer_compress_status , [COMPRESS_COMPLETED, COMPRESSION_NOT_HAPPEN])) {

                        if($this->is_edit_video) {

                            $video->is_approved = $this->edit_video_is_approved_status;

                        } else {
                        
                            $video->is_approved = DEFAULT_TRUE;

                        }

                        $video->compress_status = OVERALL_COMPRESS_COMPLETED;

                    }

                    $video->save();

                }

            } else {

                Log::info('Error page');

                if($this->video_type == TRAILER_VIDEO) {
                    
                    $video->trailer_video_resolutions = $video->trailer_resize_path = '';

                    $video->trailer_compress_status = COMPRESSION_NOT_HAPPEN;

                }

                if($this->video_type == MAIN_VIDEO) {

                    $video->video_resolutions = $video->video_resize_path = '';

                    $video->main_video_compress_status = COMPRESSION_NOT_HAPPEN;

                }

                if($this->is_edit_video) {

                    $video->is_approved = $this->edit_video_is_approved_status;

                } else {
                
                    $video->is_approved = DEFAULT_TRUE;

                }

                $video->compress_status = OVERALL_COMPRESS_COMPLETED;

                $video->save();
            }

            if ($this->send_notification) {

                Notification::save_notification($video->id);

                // Send Notifications to mobile push notification

                $id = 'all';

                // Load Template content of upload/edit video

                $template = EmailTemplate::where('template_type', NEW_VIDEO)->first();

                $subject = $content = "";

                if ($template) {

                    $category = Category::find($video->category_id);

                    $content = str_replace('<%category_name%>', $category->name, $template->content);

                    $content = str_replace('&lt;%category_name%&gt;', $category->name, $content);

                    $content = str_replace('<%video_name%>', $video->title, $content);

                    $content = str_replace('&lt;%video_name%&gt;', $video->title, $content);

                    $content = strip_tags($content);

                    $subject = $template->subject ?  str_replace('<%video_name%>', $video->title,$template->subject) : '';

                    $subject = $subject ?  str_replace('&lt;%video_name%&gt;', $video->title, $subject) : '';

                    $subject = strip_tags($subject);

                }

                // Sending Notifications to mobile

                $data = [];

                $data['admin_video_id'] = $this->videoId;

                $title = Setting::get('site_name');

                $message = $video->title ? $video->title : "";

                PushRepo::send_push_notification(PUSH_TO_ALL , $title , $message, $data);

                // Helper::send_notification($id,$subject,$content);

            }  


        }  else {

            Log::info("Video not found..! - ".$this->videoId);
        }
    }
}
