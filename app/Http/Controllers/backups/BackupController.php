<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class BackupController extends Controller
{
    public function add_video(Request $request) {

        $categories = Category::where('categories.is_approved' , 1)
                        ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                            'categories.is_series' ,'categories.status' , 'categories.is_approved')
                        ->leftJoin('sub_categories' , 'categories.id' , '=' , 'sub_categories.category_id')
                        ->groupBy('sub_categories.category_id')
                        ->havingRaw("COUNT(sub_categories.id) > 0")
                        ->orderBy('categories.name' , 'asc')
                        ->get();
           
         return view('admin.videos.video_upload')
                ->with('categories' , $categories)
                ->with('page' ,'videos')
                ->with('sub_page' ,'add-video');

    }

    public function edit_video(Request $request) {

        Log::info("Queue Driver ".envfile('QUEUE_DRIVER'));

        $categories =  $categories = Category::where('categories.is_approved' , 1)
                        ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                            'categories.is_series' ,'categories.status' , 'categories.is_approved')
                        ->leftJoin('sub_categories' , 'categories.id' , '=' , 'sub_categories.category_id')
                        ->groupBy('sub_categories.category_id')
                        ->havingRaw("COUNT(sub_categories.id) > 0")
                        ->orderBy('categories.name' , 'asc')
                        ->get();

        $video = AdminVideo::where('admin_videos.id' , $request->id)
                    ->leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
                    ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
                    ->leftJoin('genres' , 'admin_videos.genre_id' , '=' , 'genres.id')
                    ->select('admin_videos.id as video_id' ,'admin_videos.title' , 
                             'admin_videos.description' , 'admin_videos.ratings' , 
                             'admin_videos.reviews' , 'admin_videos.created_at as video_date' ,'admin_videos.is_banner','admin_videos.banner_image',
                             'admin_videos.video','admin_videos.trailer_video',
                             'admin_videos.video_type','admin_videos.video_upload_type',
                             'admin_videos.publish_time','admin_videos.duration',

                             'admin_videos.category_id as category_id',
                             'admin_videos.sub_category_id',
                             'admin_videos.genre_id',
                             'admin_videos.details',
                             'admin_videos.default_image',
                             'categories.name as category_name' , 'categories.is_series',
                             'sub_categories.name as sub_category_name' ,
                             'genres.name as genre_name',
                             'admin_videos.age')
                    ->orderBy('admin_videos.created_at' , 'desc')
                    ->first();
        if(!$video) {

            return back()->with('flash_error', tr('something_error'));

        }

        $page = 'videos';
        $sub_page = 'add-video';

        $subcategories = [];

        if($video->category_id) {
            $subcategories = get_sub_categories($video->category_id);
        }

        if($video->is_banner == 1) {
            $page = 'banner-videos';
            $sub_page = 'banner-videos';
        }

         return view('admin.videos.edit-video')
                ->with('categories' , $categories)
                ->with('video' ,$video)
                ->with('page' ,$page)
                ->with('sub_page' ,$sub_page)->with('subCategories',$subcategories);
    }

    public function add_video_process(Request $request) {

        if($request->has('video_type') && $request->video_type == VIDEO_TYPE_UPLOAD) {

            $video_validator = Validator::make( $request->all(), array(
                        'video'     => 'required|mimes:mkv,mp4,qt',
                        'trailer_video'  => ($request->genre_id) ? 'mimes:mkv,mp4,qt' : 'required|mimes:mkv,mp4,qt',
                        )
                    );

            $video_link = $request->file('video');

            $trailer_video = $request->hasFile('trailer_video') ? $request->file('trailer_video') : '';


        } else {

            $video_validator = Validator::make( $request->all(), array(
                        'other_video'     => 'required|url',
                        'other_trailer_video'  => 'required|url',
                        )
                    );

            $video_link = $request->other_video;

            $trailer_video = $request->other_trailer_video;

        }

        if($video_validator) {


             if($video_validator->fails()) {

                $error_messages = implode(',', $video_validator->messages()->all());


                if ($request->has('ajax_key')) {

                    return ['error_messages'=>$error_messages, 'error_code'=>500];

                } else {

                    return back()->with('flash_error', $error_messages);

                }
            }
        }
        $validator = Validator::make( $request->all(), array(
                    'title'         => 'required|max:255',
                    'description'   => 'required',
                    'category_id'   => 'required|integer|exists:categories,id',
                    'sub_category_id' => 'required|integer|exists:sub_categories,id,category_id,'.$request->category_id,
                    'genre'     => 'exists:genres,id,sub_category_id,'.$request->sub_category_id,
                    'default_image' => 'required|mimes:jpeg,jpg,bmp,png',
                    'banner_image' => 'mimes:jpeg,jpg,bmp,png',
                    'other_image1' => 'required|mimes:jpeg,jpg,bmp,png',
                    'other_image2' => 'required|mimes:jpeg,jpg,bmp,png',
                    'ratings' => 'required',
                    'reviews' => 'required',
                    'duration'=>'required',
                    'age'=>$request->is_banner ? '' : 'required|max:3|min:2',
                    )
                );
        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            if ($request->has('ajax_key')) {

                return ['error_messages'=>$error_messages, 'error_code'=>500];

            } else  {

                return back()->with('flash_error', $error_messages);

            }

        } else {

            $video = new AdminVideo;
            $video->title = $request->title;
            $video->description = $request->description;
            $video->category_id = $request->category_id;
            $video->sub_category_id = $request->sub_category_id;
            $video->genre_id = $request->has('genre_id') ? $request->genre_id : 0;

            $video->age = $request->age ? $request->age : 0;

            // Intialize the position is zero

            $position = 0;

            // Check the video has genre type or not

            if ($video->genre_id) {

                // If genre, in order to give the position of the admin videos

                $position = 1; // By default intialize 1

                /*
                 * Check is there any videos present in same genre, 
                 * if it is assign the position with increment of 1 otherwise intialize as zero
                 */

                if($check_position = AdminVideo::where('genre_id' , $video->genre_id)
                        ->orderBy('position' , 'desc')->first()) {
                    $position = $check_position->position + 1;
                } 

            }

            $video->position = $position;


            if($request->has('duration')) {
                $video->duration = $request->duration;
            }

            $main_video_duration = null;

            $trailer_video_duration = null;

            if($request->video_type == VIDEO_TYPE_UPLOAD) {

                $video->video_upload_type = $request->video_upload_type;

                if($request->video_upload_type == VIDEO_UPLOAD_TYPE_s3) {

                    $video->video = Helper::upload_file_to_s3($video_link);

                    if ($trailer_video) {

                        $video->trailer_video = Helper::upload_file_to_s3($trailer_video);

                    }

                } else {

                    $main_video_duration = Helper::video_upload($video_link, $request->compress_video);

                    $video->video = $main_video_duration['db_url'];

                    $video->video_resolutions = ($request->video_resolutions) ? implode(',', $request->video_resolutions) : '';


                    if ($trailer_video) {

                        $trailer_video_duration = Helper::video_upload($trailer_video, $request->compress_video);
                        $video->trailer_video = $trailer_video_duration['db_url'];  
                        
                        
                        $video->trailer_video_resolutions = ($request->video_resolutions) ? implode(',', $request->video_resolutions) : '';

                    }
  
                }     

            } elseif($request->video_type == VIDEO_TYPE_YOUTUBE) {

                $video->video = get_youtube_embed_link($video_link);
                $video->trailer_video = get_youtube_embed_link($trailer_video);

            } else {
                
                $video->video = $video_link;
                $video->trailer_video = $trailer_video;
            }

            $video->video_type = $request->video_type;


            $video->publish_time = date('Y-m-d H:i:s', strtotime($request->publish_time));
            
            $video->default_image = Helper::normal_upload_picture($request->file('default_image'));

            if($request->is_banner) {
                $video->is_banner = 1;
                $video->banner_image = Helper::normal_upload_picture($request->file('banner_image'));
            }

            $video->details = $request->has('details') ? $request->details : '';

            $video->ratings = $request->ratings;
            $video->reviews = $request->reviews;             

            if(strtotime($request->publish_time) < strtotime(date('Y-m-d H:i:s'))) {

                $video->status = DEFAULT_TRUE;

            } else {

                $video->status = DEFAULT_FALSE;

            }


            if($request->hasFile('video_subtitle')) {

                $video->video_subtitle =  Helper::subtitle_upload($request->file('video_subtitle'));


            }

            if($request->hasFile('trailer_subtitle')) {

                $video->trailer_subtitle =  Helper::subtitle_upload($request->file('trailer_subtitle'));

            }

            if (empty($video->video_resolutions)) {
                $video->compress_status = DEFAULT_TRUE;
                $video->trailer_compress_status = DEFAULT_TRUE;
                $video->is_approved = DEFAULT_TRUE;
            }
            
            $video->uploaded_by = ADMIN;

            // dd($video);
            Log::info("Approved : ".$video->is_approved);

            $video->save();

            Log::info("saved Video Object : ".'Success');


            if($video) {

                $video->unique_id = $video->id;

                $video->save();

                if($video->is_approved && $video->status) {

                    Log::info("Send Notification ".$request->send_notification);

                    if ($request->send_notification) {

                        Log::info("Mail queue started : ".'Success');

                        dispatch(new SendVideoMail($video->id, NEW_VIDEO));

                        Log::info("Mail queue completed : ".'Success');

                        Notification::save_notification($video->id);

                    }

                }

                if($video->video_resolutions) {

                    if ($main_video_duration) {
                        $inputFile = $main_video_duration['baseUrl'];
                        $local_url = $main_video_duration['local_url'];
                        $file_name = $main_video_duration['file_name'];

                        if (file_exists($inputFile)) {
                            Log::info("Main queue Videos : ".'Success');
                            dispatch(new StreamviewCompressVideo($inputFile, $local_url, MAIN_VIDEO, $video->id, $file_name, $request->send_notification));
                            Log::info("Main Compress Status : ".$video->compress_status);
                            Log::info("Main queue completed : ".'Success');
                        }
                    }
                    if ($trailer_video_duration) {
                        if ($trailer_video) {
                            $inputFile = $trailer_video_duration['baseUrl'];
                            $local_url = $trailer_video_duration['local_url'];
                            $file_name = $trailer_video_duration['file_name'];
                            if (file_exists($inputFile)) {
                                Log::info("Trailer queue Videos : ".'Success');
                                dispatch(new StreamviewCompressVideo($inputFile, $local_url, TRAILER_VIDEO, $video->id,$file_name,$request->send_notification));
                                Log::info("Trailer Compress Status : ".$video->trailer_compress_status);
                                Log::info("Trailer queue completed : ".'Success');
                            }
                        }
                    }
                }
                
                Helper::upload_video_image($request->file('other_image1'),$video->id,2);

                Helper::upload_video_image($request->file('other_image2'),$video->id,3);

                if (envfile('QUEUE_DRIVER') != 'redis') {

                    \Log::info("Queue Driver : ".envfile('QUEUE_DRIVER'));

                    $video->compress_status = DEFAULT_TRUE;

                    $video->trailer_compress_status = DEFAULT_TRUE;

                    $video->save();
                }

                if (Setting::get('track_user_mail')) {

                    user_track("StreamHash - Video Created");

                }

                if ($request->has('ajax_key')) {
                    Log::info('Video Id Ajax : '.$video->id);
                    return ['id'=>route('admin.view.video', array('id'=>$video->id))];
                } else  {
                    Log::info('Video Id : '.$video->id);
                    return redirect(route('admin.view.video', array('id'=>$video->id)));
                }

            } else {

                if($request->has('ajax_key')) {
                    
                    return tr('admin_not_error');

                } else { 
                    
                    return back()->with('flash_error', tr('admin_not_error'));

                }
            }
        }
    
    }

    public function edit_video_process(Request $request) {

        Log::info("Initiaization Edit Process : ".print_r($request->all(),true));


        $video = AdminVideo::find($request->id);

        $video_validator = array();

        $video_link = $video->video;

        $trailer_video = $video->trailer_video;

        // dd($request->all());

        if($request->has('video_type') && $request->video_type == VIDEO_TYPE_UPLOAD) {

            Log::info("Video Type : ".$request->has('video_type'));

            if (isset($request->video)) {
                if ($request->video != '') {

                    $video_validator = Validator::make( $request->all(), array(
                            'video'     => 'required|mimes:mkv,mp4,qt',
                            // 'trailer_video'  => 'required|mimes:mkv,mp4,qt',
                            )
                        );

                    $video_link = $request->hasFile('video') ? $request->file('video') : array();   

                }
            }

            if (isset($request->trailer_video)) {
                if ($request->trailer_video != '') {
                    $video_validator = Validator::make( $request->all(), array(
                            // 'video'     => 'required|mimes:mkv,mp4,qt',
                             'trailer_video'  => ($request->genre_id) ? 'mimes:mkv,mp4,qt' : 'required|mimes:mkv,mp4,qt',
                            )
                        );

                    $trailer_video = $request->hasFile('trailer_video') ? $request->file('trailer_video') : array();
                }
            }
        

        } elseif($request->has('video_type') && in_array($request->video_type , array(VIDEO_TYPE_YOUTUBE,VIDEO_TYPE_OTHER))) {

            $video_validator = Validator::make( $request->all(), array(
                        'other_video'     => 'required|url',
                        'other_trailer_video'  => 'required|url',
                        )
                    );

            $video_link = $request->has('other_video') ? $request->other_video : array();

            $trailer_video = $request->has('other_trailer_video') ? $request->other_trailer_video : array();
        }

        if($video_validator) {

             if($video_validator->fails()) {
                $error_messages = implode(',', $video_validator->messages()->all());
                if ($request->has('ajax_key')) {
                    return $error_messages;
                } else {
                    return back()->with('flash_error', $error_messages);
                }
            }
        }

        $validator = Validator::make( $request->all(), array(
                    'id' => 'required|integer|exists:admin_videos,id',
                    'title'         => 'max:255',
                    'description'   => '',
                    'category_id'   => 'required|integer|exists:categories,id',
                    'sub_category_id' => 'required|integer|exists:sub_categories,id,category_id,'.$request->category_id,
                    'genre'     => 'exists:genres,id,sub_category_id,'.$request->sub_category_id,
                    // 'video'     => 'mimes:mkv,mp4,qt',
                    // 'trailer_video'  => 'mimes:mkv,mp4,qt',
                    'default_image' => 'mimes:jpeg,jpg,bmp,png',
                    'other_image1' => 'mimes:jpeg,jpg,bmp,png',
                    'other_image2' => 'mimes:jpeg,jpg,bmp,png',
                    'ratings' => 'required',
                    'reviews' => 'required',
                    'age'=>$request->is_banner ? '' : 'required|min:2|max:3'
                    )
                );

        if($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            if ($request->has('ajax_key')) {
                return $error_messages;
            } else {
                return back()->with('flash_error', $error_messages);
            }

        } else {

            Log::info("Success validation checking : Success");

            $video->title = $request->has('title') ? $request->title : $video->title;

            $video->description = $request->has('description') ? $request->description : $video->description;

            $video->category_id = $request->has('category_id') ? $request->category_id : $video->category_id;

            $video->sub_category_id = $request->has('sub_category_id') ? $request->sub_category_id : $video->sub_category_id;

            $video->genre_id = $request->has('genre_id') ? $request->genre_id : $video->genre_id;

            $video->age = $request->age ? $request->age : 0;

            if($request->has('duration')) {
                $video->duration = $request->duration;
            }

            if(strtotime($request->publish_time) < strtotime(date('Y-m-d H:i:s'))) {
                $video->status = DEFAULT_TRUE;
            } else {
                $video->status = DEFAULT_FALSE;
            }

            $video->details = $request->has('details') ? $request->details : $video->details;

            $main_video_url = null;
            $trailer_video_url = null;

            if($request->video_type == VIDEO_TYPE_UPLOAD && $video_link) {

                 Log::info("To Be upload videos : ".'Success');

                // Check Previous Video Upload Type, to delete the videos

                if($video->video_upload_type == VIDEO_UPLOAD_TYPE_s3) {

                    Helper::s3_delete_picture($video->video);   

                    if ($trailer_video) {

                        Helper::s3_delete_picture($video->trailer_video);  

                    }

                } else {

                    $videopath = '/uploads/videos/original/';

                    // dd($request->all());

                    if ($request->hasFile('video')) {
                        Helper::delete_picture($video->video, $videopath); 
                        // @TODO
                        $splitVideos = ($video->video_resolutions) 
                                    ? explode(',', $video->video_resolutions)
                                    : [];
                        foreach ($splitVideos as $key => $value) {
                           Helper::delete_picture($video->video, $videopath.$value.'/');
                        }
                        Log::info("Deleted Main Video : ".'Success');   
                    }
                    if ($request->hasFile('trailer_video')) {

                        if ($trailer_video) {
                            Helper::delete_picture($video->trailer_video, $videopath);
                            // @TODO
                            $splitTrailer = ($video->trailer_video_resolutions) 
                                        ? explode(',', $video->trailer_video_resolutions)
                                        : [];
                            foreach ($splitTrailer as $key => $value) {
                               Helper::delete_picture($video->trailer_video, $videopath.$value.'/');
                            }
                            Log::info("Deleted Trailer Video : ".'Success');

                        }
                    }
                
                }

                if($request->video_upload_type == VIDEO_UPLOAD_TYPE_s3) {

                    $video->video = Helper::upload_file_to_s3($video_link);

                    if ($trailer_video) {

                        $video->trailer_video = Helper::upload_file_to_s3($trailer_video);

                    } 

                } else {
                    if ($request->hasFile('video')) {
                        $video->compress_status = DEFAULT_FALSE;
                        $video->is_approved = DEFAULT_FALSE;
                        $main_video_url = Helper::video_upload($video_link, $request->compress_video);
                        Log::info("New Video Uploaded ( Main Video ) : ".'Success');
                        $video->video = $main_video_url['db_url'];
                        $video->video_resolutions = ($request->video_resolutions) ? implode(',', $request->video_resolutions) : null;
                    } else {
                        $video->video = $video_link;
                    }
                    // dd($request->hasFile('trailer_video'));
                    if ($request->hasFile('trailer_video')) {
                        $video->trailer_compress_status = DEFAULT_FALSE;
                        $video->is_approved = DEFAULT_FALSE;
                        $trailer_video_url = Helper::video_upload($trailer_video, $request->compress_video);
                        Log::info("New Video Uploaded ( Trailer Video ) : ".'Success');
                        $video->trailer_video = $trailer_video_url['db_url']; 
                        $video->trailer_video_resolutions = ($request->video_resolutions) ? implode(',', $request->video_resolutions) : null; 
                    } else {
                        $video->trailer_video = $trailer_video;
                    }
                
                    Log::info("Video Resoltuions : ".print_r($video->video_resolutions, true));
                    Log::info("Trailer Video Resoltuions : ".print_r($video->trailer_video_resolutions, true));
                }                

            } elseif($request->video_type == VIDEO_TYPE_YOUTUBE && $video_link && $trailer_video) {

                $video->video = get_youtube_embed_link($video_link);
                $video->trailer_video = get_youtube_embed_link($trailer_video);
            } else {
                $video->video = $video_link ? $video_link : $video->video;
                $video->trailer_video = $trailer_video ? $trailer_video : $video->trailer_video;
            }

            if($request->hasFile('default_image')) {
                Helper::delete_picture($video->default_image, "/uploads/images/");
                $video->default_image = Helper::normal_upload_picture($request->file('default_image'));
            }

            if($video->is_banner == 1) {
                if($request->hasFile('banner_image')) {
                    Helper::delete_picture($video->banner_image, "/uploads/images/");
                    $video->banner_image = Helper::normal_upload_picture($request->file('banner_image'));
                }
            }

            $video->video_type = $request->video_type ? $request->video_type : $video->video_type;

            $video->video_upload_type = $request->video_upload_type ? $request->video_upload_type : $video->video_upload_type;

            $video->ratings = $request->has('ratings') ? $request->ratings : $video->ratings;

            $video->reviews = $request->has('reviews') ? $request->reviews : $video->reviews;

            $video->edited_by = ADMIN;

            $video->unique_id = $video->id;

            if($video->video_type != VIDEO_TYPE_UPLOAD) {
                $video->trailer_resize_path = null;
                $video->video_resize_path = null;
                $video->trailer_video_resolutions = null;
                $video->video_resolutions = null;
            }

            if (empty($video->video_resolutions)) {
                $video->compress_status = DEFAULT_TRUE;
                $video->trailer_compress_status = DEFAULT_TRUE;
                $video->is_approved = DEFAULT_TRUE;
                Log::info("Empty Resoltuions");
            }

            Log::info("Approved : ".$video->is_approved);


            if($request->hasFile('trailer_subtitle')) {

                if ($video->id) {

                    if ($video->trailer_subtitle) {

                        Helper::delete_picture($video->trailer_subtitle, "/uploads/subtitles/");  

                    }  
                }

                $video->trailer_subtitle =  Helper::subtitle_upload($request->file('trailer_subtitle'));

            }

            if($request->hasFile('video_subtitle')) {

                if ($video->id) {

                    if ($video->video_subtitle) {

                        Helper::delete_picture($video->video_subtitle, "/uploads/subtitles/");  

                    }  
                }

                $video->video_subtitle =  Helper::subtitle_upload($request->file('video_subtitle'));

            }

            $video->save();

            Log::info("saved Video Object : ".'Success');

            if($video) {
                if ($request->hasFile('video') && $video->video_resolutions) {
                    if ($main_video_url) {
                        $inputFile = $main_video_url['baseUrl'];
                        $local_url = $main_video_url['local_url'];
                        $file_name = $main_video_url['file_name'];
                        if (file_exists($inputFile)) {
                            Log::info("Main queue Videos : ".'Success');
                            dispatch(new StreamviewCompressVideo($inputFile, $local_url, MAIN_VIDEO, $video->id,$file_name,$request->send_notification));
                            Log::info("Main Compress Status : ".$video->compress_status);
                            Log::info("Main queue completed : ".'Success');
                        }
                    }
                }

                if($request->hasFile('trailer_video') && $video->trailer_video_resolutions) {
                    if ($trailer_video_url) {
                        $inputFile = $trailer_video_url['baseUrl'];
                        $local_url = $trailer_video_url['local_url'];
                        $file_name = $trailer_video_url['file_name'];
                        if (file_exists($inputFile)) {
                            Log::info("Trailer queue Videos : ".'Success');
                            dispatch(new StreamviewCompressVideo($inputFile, $local_url, TRAILER_VIDEO, $video->id, $file_name,$request->send_notification));
                            Log::info("Trailer Compress Status : ".$video->compress_status);
                            Log::info("Trailer queue completed : ".'Success');
                        }
                    }
                }

                if($request->hasFile('other_image1')) {
                    Helper::upload_video_image($request->file('other_image1'),$video->id,2);  
                }

                if($request->hasFile('other_image2')) {
                   Helper::upload_video_image($request->file('other_image2'),$video->id,3); 
                }

                if($video->is_approved && $video->status) {

                    Log::info("Send Notification ".$request->send_notification);

                    if ($request->send_notification) {

                        Log::info("Mail queue started : ".'Success');

                        dispatch(new SendVideoMail($video->id, EDIT_VIDEO));
                        
                        Log::info("Mail queue completed : ".'Success');

                        Notification::save_notification($video->id);

                    }

                }


                if (envfile('QUEUE_DRIVER') != 'redis') {

                    \Log::info("Queue Driver : ".envfile('QUEUE_DRIVER'));

                    $video->compress_status = DEFAULT_TRUE;

                    $video->trailer_compress_status = DEFAULT_TRUE;

                    $video->save();
                }

                if (Setting::get('track_user_mail')) {

                    user_track("StreamHash - Updated Video");

                }

                if ($request->has('ajax_key')) {
                    return ['id'=>route('admin.view.video', array('id'=>$video->id))];
                } else {
                    return redirect(route('admin.view.video', array('id'=>$video->id)));
                }

            } else {
                if ($request->has('ajax_key')) {
                    return tr('admin_not_error');
                } else {
                    return back()->with('flash_error', tr('admin_not_error'));
                }
            }
        }
    
    }

    /**
     * Function Name : wishlist_delete()
     * 
     * @uses To delete wishlist based on the logged in user id and video id
     *
     * @usage only WEB
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - User Id & Video Id
     *
     * @return response with boolean status
     *
     */
    public function wishlist_delete(Request $request) {

        Log::info(print_r($request->all() , true));

        try {

            DB::beginTransaction();

            if (!$request->has('sub_profile_id')) {

                $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                if ($sub_profile) {

                    $request->request->add([ 
                        'sub_profile_id' => $sub_profile->id,
                    ]);

                } else {

                    throw new Exception(tr('sub_profile_details_not_found'));
                    
                }

            } else {

                $subProfile = SubProfile::where('user_id', $request->id)
                                    ->where('id', $request->sub_profile_id)->first();

                if (!$subProfile) {

                    throw new Exception(tr('sub_profile_details_not_found'));
                    
                }
            
            }

            $validator = Validator::make(
                $request->all(),
                array(
                    'wishlist_id' => 'integer|exists:admin_videos,id',
                    'sub_profile_id' => 'integer|exists:sub_profiles,id',
                ),
                array(
                    'exists' => 'The :attribute doesn\'t exists please add to wishlists',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

                throw new Exception($error_messages);
                
            } else {

                /** Clear All wishlist of the loggedin user */

                if($request->status == 1) {

                    Log::info("Check Delete Wishlist - 1");

                    $wishlist = Wishlist::where('user_id',$request->sub_profile_id)->delete();

                } else {  /** Clear particularv wishlist of the loggedin user */

                    Log::info("Check Delete Wishlist - 0");

                    $wishlist = Wishlist::where('admin_video_id',$request->wishlist_id)
                            ->where('user_id', $request->sub_profile_id)
                            ->first();

                    if($wishlist) {

                        $wishlist->delete();

                    } else {

                        throw new Exception(tr('video_not_found'));
                        
                    }
                }

                $response_array = array('success' => true, 'message'=>tr('delete_wishlist_success'));
           
            }

            DB::commit();

            $response = response()->json($response_array, 200);

            return $response;

        } catch (Exception $e) {

            DB::rollback();

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);

        }
    
    }


    /**
     * Function Name : api_search_video()
     *
     * @uses To search videos based on title
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - Title of the video (For Mobile Usage)
     *
     * @return response of the array 
     */

    public function api_search_video(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'key' => '',
                'sub_profile_id'=>'required|exists:sub_profiles,id',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $subProfile = SubProfile::where('user_id', $request->id)
                        ->where('id', $request->sub_profile_id)->first();

            if (!$subProfile) {

                return response()->json(['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')]);
                
            }

            if($request->key) {
                $query = AdminVideo::where('admin_videos.is_approved' , 1)
                    ->leftjoin('categories', 'categories.id', '=', 'admin_videos.category_id')
                    ->leftjoin('sub_categories', 'sub_categories.id', '=', 'admin_videos.sub_category_id')
                    ->where('title', 'like', '%' . $request->key . '%')
                    ->whereNotIn('admin_videos.is_banner',[1])
                    ->where('admin_videos.status' , 1)
                    ->where('categories.is_approved' , 1)
                    ->where('sub_categories.is_approved' , 1)
                    ->orderBy('admin_videos.created_at' , 'desc');

            } else {

                $query = AdminVideo::where('admin_videos.is_approved' , 1)
                    ->leftjoin('categories', 'categories.id', '=', 'admin_videos.category_id')
                    ->leftjoin('sub_categories', 'sub_categories.id', '=', 'admin_videos.sub_category_id')
                ->where('title', 'like', '%' . $request->key . '%')
                ->whereNotIn('admin_videos.is_banner',[1])
                ->where('admin_videos.status' , 1)
                ->where('categories.is_approved' , 1)
                ->where('sub_categories.is_approved' , 1)
                ->orderBy('admin_videos.created_at' , 'desc')->skip(0)->take(6);
            }
                
            if ($request->sub_profile_id) {

                
                $flagVideos = getFlagVideos($request->sub_profile_id);

                if($flagVideos) {

                    $query->whereNotIn('admin_videos.id', $flagVideos);
                }

            }

            $videos = $query->get();

            $results = [];

            $user = User::find($request->id);

            if (!empty($videos) && $videos != null) {

                foreach ($videos as $key => $value) {

                    $full_details = displayFullDetails($value->id, $user ? $user->id : '', $user ? $user->user_type : '', $request->device_type, $request->sub_profile_id);

                    if($full_details) {

                        $results[] = $full_details;

                    }

                }
            }

            $response_array = array('success' => true, 'data' => $results, 'title'=>$request->key);
        }

        $response = response()->json($response_array, 200);

        return $response;

    } 
}
