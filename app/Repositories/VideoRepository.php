<?php

/**************************************************
* Repository Name: VideoRepository
*
* Purpose: This repository used to do all functions related videos.
*
* @author - Shobana Chandrasekar
*
* Date Created: 22 June 2017
**************************************************/

namespace App\Repositories;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use App\Jobs\StreamviewCompressVideo;

use App\Jobs\PushNotification;

use App\Jobs\SendVideoMail;

use App\Repositories\PushNotificationRepository as PushRepo;


use Validator;

use Hash;

use Log;

use Setting;

use DB;

use Exception;

use Auth;

use File;


use App\PayPerView;

use App\AdminVideo;

use App\Notification;

use App\Category;

use App\VideoCastCrew;


use App\EmailTemplate;


class VideoRepository {

	/**
 	 * Function Name : pay_per_views_status_check
 	 *
 	 * To check the status of the pay per view in each video
 	 *
 	 * @created_by - Shobana Chandrasekar
 	 * 
 	 * @updated_by - - 
 	 *
 	 * @param object $request - Video related details, user related details
 	 *
 	 * @return response of success/failure response of datas
 	 */
 	public static function pay_per_views_status_check($user_id, $user_type, $video_data) {

 		// Check video details present or not

 		if ($video_data) {

 			// Check the video having ppv or not

 			if ($video_data->is_pay_per_view) {

 				$is_ppv_applied_for_user = DEFAULT_FALSE; // To check further steps , the user is applicable or not

 				// Check Type of User, 1 - Normal User, 2 - Paid User, 3 - Both users

 				switch ($video_data->type_of_user) {

 					case NORMAL_USER:
 						
 						if (!$user_type) {

 							$is_ppv_applied_for_user = DEFAULT_TRUE;
 						}

 						break;

 					case PAID_USER:
 						
 						if ($user_type) {

 							$is_ppv_applied_for_user = DEFAULT_TRUE;
 						}
 						
 						break;
 					
 					default:

 						// By default it will taks as Both Users

 						$is_ppv_applied_for_user = DEFAULT_TRUE;

 						break;
 				}

 				if ($is_ppv_applied_for_user) {

 					// Check the user already paid or not

 					$ppv_model = PayPerView::where('status', DEFAULT_TRUE)
 						->where('user_id', $user_id)
 						->where('video_id', $video_data->admin_video_id)
 						->orderBy('id','desc')
 						->first();

 					$watch_video_free = DEFAULT_FALSE;

 					if ($ppv_model) {

 						// Check the type of payment , based on that user will watch the video 

 						switch ($video_data->type_of_subscription) {

		 					case ONE_TIME_PAYMENT:
		 						
		 						$watch_video_free = DEFAULT_TRUE;
		 						
		 						break;

		 					case RECURRING_PAYMENT:

		 						// If the video is recurring payment, then check the user already watched the paid video or not 
		 						
		 						if (!$ppv_model->is_watched) {

		 							$watch_video_free = DEFAULT_TRUE;
		 						}
		 						
		 						break;
		 					
		 					default:

		 						// By default it will taks as true

		 						$watch_video_free = DEFAULT_TRUE;

		 						break;
		 				}

		 				if ($watch_video_free) {

		 					$response_array = ['success'=>true, 'message'=>Helper::get_message(124), 'code'=>124];

		 				} else {

		 					$response_array = ['success'=>false, 'message'=>Helper::get_message(125), 'code'=>125];

		 				}

 					} else {

 						// 125 - User pay and watch the video

 						$response_array = ['success'=>false, 'message'=>Helper::get_message(125), 'code'=>125];
 					}

 				} else {

 					$response_array = ['success'=>true, 'message'=>Helper::get_message(124), 'code'=>124];

 				}

 			} else {

 				// 124 - User can watch the video
 				
 				$response_array = ['success'=>true, 'message'=>Helper::get_message(123), 'code'=>124];

 			}

 		} else {

 			$response_array = ['success'=>false, 'error_messages'=>Helper::get_error_message(906), 
 				'error_code'=>906];

 		}

 		return response()->json($response_array);
 	
 	}

 	/**
 	 * Function Name : video_save
 	 *
 	 * @uses To save the video (Common function for both create and edit)
 	 *
 	 * @created: Shobana Chandrasekar
 	 * 
 	 * @updated: Vidhya R
 	 *
 	 * @param object $request - Video related details
 	 *
 	 * @return response of success/failure response of datas
 	 */
 	public static function video_save(Request $request) {

 		try {

 			DB::beginTransaction();

	 		// Basic validations of video save form

	 		$validator = Validator::make($request->all(),
	 			[
	 				'admin_video_id'=>'exists:admin_videos,id',
	 				'title'=>'required|max:255|min:4',	
	 				'publish_type'=>'required|in:'.PUBLISH_NOW.','.PUBLISH_LATER,
	 				'publish_time'=>$request->publish_type == PUBLISH_LATER ? 'required' : '',
	 				'age'=>'required|min:2|max:3',
	 				'ratings'=>'required|numeric|min:1|max:5',
	 				'description'=>'required',
	 				'details'=>'required',
	 				'category_id'=>'required|exists:categories,id,is_approved,'.DEFAULT_TRUE,
	 				'sub_category_id'=>'required|exists:sub_categories,id,is_approved,'.DEFAULT_TRUE,
	 				'genre_id'=>'exists:genres,id,is_approved,'.DEFAULT_TRUE,
	 				'default_image'=> $request->admin_video_id ? 'mimes:png,jpeg,jpg' : 'required|mimes:png,jpeg,jpg',
	 				'other_image1'=>$request->admin_video_id ? 'mimes:png,jpeg,jpg' : 'required|mimes:png,jpeg,jpg',
	 				'other_image2'=>$request->admin_video_id ? 'mimes:png,jpeg,jpg' : 'required|mimes:png,jpeg,jpg',
	 				'video_type'=>'required|in:'.VIDEO_TYPE_UPLOAD.','.VIDEO_TYPE_YOUTUBE.','.VIDEO_TYPE_OTHER,
	 				'compress_video'=>'required|in:'.COMPRESS_ENABLED.','.COMPRESS_DISABLED,
	 				'video_upload_type'=> ($request->video_type == VIDEO_TYPE_UPLOAD) ? 'required|in:'.VIDEO_UPLOAD_TYPE_s3.','.VIDEO_UPLOAD_TYPE_DIRECT : '',
	 				'video'=> ($request->video_type == VIDEO_TYPE_UPLOAD) ? ($request->admin_video_id ? 'mimes:mp4' : 'required|mimes:mp4') : 'url|max:255',
	 				'trailer_video'=>$request->hasFile('trailer_subtitle') ? ($request->video_type == VIDEO_TYPE_UPLOAD ?  'mimes:mp4' : 'url|max:255') : ($request->video_type == VIDEO_TYPE_UPLOAD ? 'mimes:mp4' : 'url|max:255'), // If trailer subtitle uploading by admin without trailer video it will throw an error
	 				'duration'=>'required',
	 				'trailer_duration'=>'required',
	 				'is_kids_video'=> 'in:'.KIDS_SECTION_YES.','.KIDS_SECTION_NO
	 				// |date_format:hh:mm:ss
	 			],
	 			[
	 				'category_id.exists' => tr('category_not_exists'),
	 				'sub_category_id.exists' => tr('subcategory_not_exists'),
	 				'genre_id.exists' => tr('genre_not_exists'),
	 				'admin_video_id.exists' => tr('video_not_exists'),
	 				'cast_crew_ids.exists' => tr('cast_crew_not_exists'),
	 			]
	 		);

	 		if ($validator->fails()) {

	 			$errors = implode(',', $validator->messages()->all());

	 			throw new Exception($errors, 101);
	 		} 

 			// Check the category having genre or not

 			$load_category = Category::find($request->category_id);

 			if ($load_category) {

 				if ($load_category->is_series) {

 					// If Genre not present, trailer video should be required

	 				$is_genre_existing = Validator::make($request->all(),[

	 					'genre_id'=> 'required',

	 				],[

	 					'genre_id.exists' => tr('genre_not_exists'),

	 				]);

	 				if ($is_genre_existing->fails()) {

			 			$errors = implode(',', $is_genre_existing->messages()->all());

			 			throw new Exception($errors);

			 		}

 				}

 			}

 			// Check Genre present or not

 			if($request->genre_id <= 0) {

 				// If Genre not present, trailer video should be required

 				$genre_validator = Validator::make($request->all(),[

 					'trailer_video'=> $request->video_type == VIDEO_TYPE_UPLOAD ? ($request->admin_video_id ? 'mimes:mp4' : 'required|mimes:mp4') : 'required|url|max:255'

 				]);

 			} else {

 				// If Genre present, trailer video Optional

 				$genre_validator = Validator::make($request->all(),[

 					'trailer_video'=> $request->video_type == VIDEO_TYPE_UPLOAD ? ($request->admin_video_id ? 'mimes:mp4' : 'mimes:mp4') : 'required|url|max:255'

 				]);

 			}

 			if ($genre_validator->fails()) {

	 			$errors = implode(',', $genre_validator->messages()->all());

	 			throw new Exception($errors);

	 		}

	 		$videopath = '/uploads/videos/original/';

	 		// If video id present, load video and check whether the user changed the video type or not
 			if ($request->admin_video_id) {

 				$video_model = AdminVideo::find($request->admin_video_id);

 				$different_type = DEFAULT_FALSE;

 				if ($request->video_type == $video_model->video_type) {

 					$different_type = DEFAULT_FALSE;

 					$video_validator = Validator::make($request->all(),[

	 					'video'=> $request->video_type == VIDEO_TYPE_UPLOAD ? 'mimes:mp4' : 'required|url|max:255',

	 					'trailer_video'=> $request->video_type == VIDEO_TYPE_UPLOAD ? 'mimes:mp4' : 'required|url|max:255',

	 				]);

 				} else {

 					$different_type = DEFAULT_TRUE;

 					$video_validator = Validator::make($request->all(),[

	 					'video'=> $request->video_type == VIDEO_TYPE_UPLOAD ? 'required|mimes:mp4' : 'required|url|max:255',

	 					'trailer_video'=> $request->video_type == VIDEO_TYPE_UPLOAD ? 'required|mimes:mp4' : 'required|url|max:255',

	 				]);

 				}

 				if ($video_validator->fails()) {

		 			$errors = implode(',', $video_validator->messages()->all());

		 			throw new Exception($errors);

		 		}

		 		$video_model->edited_by = $request->edited_by ? $request->edited_by : ADMIN;

		 		// If the video type is different, Based on type just delete the videos

		 		if ($different_type && $request->admin_video_id) {

		 			if ($video_model->video_type == VIDEO_TYPE_UPLOAD && $request->video_type != VIDEO_TYPE_UPLOAD) {

		 				if($video_model->video_upload_type == VIDEO_UPLOAD_TYPE_s3) {

			        		Helper::s3_delete_picture($video_model->video);   

			               	Helper::s3_delete_picture($video_model->trailer_video);  

			        	} else {

			        		Helper::delete_picture($video_model->video, $videopath); 

	                		$splitVideos = ($video_model->video_resolutions) 
                                ? explode(',', $video_model->video_resolutions)
                                : [];

	                        foreach ($splitVideos as $key => $value) {

	                        	Helper::delete_picture($video_model->video, $videopath.$value.'/');

	                        }

	                        Helper::delete_picture($video_model->trailer_video, $videopath); 

	                		$splitVideos = ($video_model->trailer_video_resolutions) 
                                ? explode(',', $video_model->trailer_video_resolutions)
                                : [];

	                        foreach ($splitVideos as $key => $value) {

	                        	Helper::delete_picture($video_model->trailer_video, $videopath.$value.'/');

	                        }

			        	}

		 			}

		 		}


 			} else {

 				$video_model = new AdminVideo;

 				$video_model->uploaded_by = $request->uploaded_by ? $request->uploaded_by : ADMIN;

 				$video_model->is_approved = $request->uploaded_by ? VIDEO_DECLINED : VIDEO_APPROVED;

 			}

 			$video_model->title = $request->title;

 			$timezone =  $request->timezone;

 			$current_date_time = date('Y-m-d H:i:s');

 			$converted_current_datetime = convertTimeToUSERzone($current_date_time, $timezone);

 			// Check the publish type based on that convert the time to timezone

 			if ($request->publish_type == PUBLISH_LATER) {

 				$publish_time = $request->publish_time;

 				$strtotime_publish_time = strtotime($publish_time);

 				$current_strtotime = strtotime($converted_current_datetime);

 				if ($strtotime_publish_time <= $current_strtotime) {

 					throw new Exception(Helper::get_error_message(166), 166);

 				}

 				$video_model->publish_time = date('Y-m-d H:i:s', $strtotime_publish_time);

 				// Based on publishing time the status will change

 				$video_model->status = VIDEO_NOT_YET_PUBLISHED;

 			} else {

 				$video_model->publish_time = $converted_current_datetime;

 				$video_model->status = VIDEO_PUBLISHED;

 			}

 			$video_model->age = $request->age;

 			$video_model->duration = $request->duration;

 			$video_model->trailer_duration = $request->trailer_duration;

 			$video_model->ratings = $request->ratings;

 			$video_model->description = $request->description;

 			$video_model->reviews = '';

 			$video_model->details = $request->details;

 			$video_model->category_id = $request->category_id;

 			$video_model->sub_category_id = $request->sub_category_id;

 			$video_model->genre_id = $request->genre_id ? $request->genre_id : 0;

 			$video_model->video_type = $request->video_type;

 			$video_model->video_upload_type = $request->video_type == VIDEO_TYPE_UPLOAD ? $request->video_upload_type : '';

 			$video_model->unique_id = seoUrl($video_model->title);

 			$video_model->duration = $request->duration;

 			$video_model->is_kids_video = $request->is_kids_video ? $request->is_kids_video : KIDS_SECTION_NO;

            $main_video_details = $trailer_video_details = "";

            $no_need_compression = DEFAULT_TRUE;

            // If the video type is manual upload then below code will excute

	        if($request->video_type == VIDEO_TYPE_UPLOAD) {

	        	// In Manual Upload, need to check whether its s3 bucket / streaming upload, if it is streaming upload need to show resolution based upload video

	        	if($request->video_upload_type == VIDEO_UPLOAD_TYPE_s3) {

	        		if ($request->hasFile('video')) {

	        			if ($request->admin_video_id) {

	        			 	Helper::s3_delete_picture($video_model->video);   

	        			 }

	        			$video_model->video = Helper::upload_file_to_s3($request->file('video'));

	        		}

                    if ($request->hasFile('trailer_video')) {

                    	if ($request->admin_video_id) {

	                        Helper::s3_delete_picture($video_model->trailer_video);  

	                    }

                        $video_model->trailer_video = Helper::upload_file_to_s3($request->file('trailer_video'));

                    }

	        	} else {

		        	//$video_model->compress_video = $request->compress_video;

		        	$video_model->video_resolutions = $request->video_resolutions ? implode(',', $request->video_resolutions) : ($request->admin_video_id ? $video_model->video_resolutions : "");

		        	$video_model->trailer_video_resolutions = $request->trailer_video_resolutions ? implode(',', $request->trailer_video_resolutions) : ($request->admin_video_id ? $video_model->video_resolutions : "");

		        	// $video_model->is_approved = DEFAULT_FALSE;

		        	$video_model->main_video_compress_status = COMPRESS_NOT_YET_STARTED;

	                $video_model->trailer_compress_status = COMPRESS_NOT_YET_STARTED;

	                $video_model->compress_status = COMPRESS_NOT_YET_STARTED;

	                if (Setting::get('ffmpeg_installed') == FFMPEG_NOT_INSTALLED) {

	                	$request->compress_video = DO_NOT_COMPRESS;
	                }

                	/****** ORIGINAL VIDEO UPLOAD START *****/

	                if ($request->hasFile('video')) {

	                	if ($request->admin_video_id) {

	                		Helper::delete_picture($video_model->video, $videopath); 

	                		$splitVideos = ($video_model->video_resolutions) 
                                ? explode(',', $video_model->video_resolutions)
                                : [];

	                        foreach ($splitVideos as $key => $value) {

	                        	Helper::delete_picture($video_model->video, $videopath.$value.'/');

	                        }

	                	}

		                $main_video_details = Helper::video_upload($request->file('video'), $request->compress_video);

                    	$video_model->video = $main_video_details['db_url'];

                    }

                	if($request->hasFile('trailer_video')) {

                		if ($request->admin_video_id) {

	                		Helper::delete_picture($video_model->trailer_video, $videopath); 

	                		$splitVideos = ($video_model->trailer_video_resolutions) 
                                ? explode(',', $video_model->trailer_video_resolutions)
                                : [];

	                        foreach ($splitVideos as $key => $value) {

	                        	Helper::delete_picture($video_model->trailer_video, $videopath.$value.'/');

	                        }

	                	}

                        $trailer_video_details = Helper::video_upload($request->file('trailer_video'), $request->compress_video);

                        $video_model->trailer_video = $trailer_video_details['db_url'];  

                	}

                	/****** ORIGINAL VIDEO UPLOAD END *****/

                	/****** RESOLUTION CHECK START *****/

                	// If moderator or admin choosed any resolutions - check compression queue is applicable for video

                	if ($request->video_resolutions && $request->trailer_video_resolutions) {

                		Log::info('Both resolutions : ');

                		if ($request->hasFile('video') &&  $request->hasFile('trailer_video')) {

                    		$no_need_compression = DEFAULT_FALSE;

                    	} else {

                    		$no_need_compression = DEFAULT_TRUE;

                    	}

                	} else if(!empty($request->video_resolutions) && empty($request->trailer_video_resolutions)){

                		Log::info('Video resolutions : ');

                		$video_model->trailer_compress_status = COMPRESS_COMPLETED;

                		if ($request->hasFile('video')) {

                    		$no_need_compression = DEFAULT_FALSE;

                    	} else {

                    		$no_need_compression = DEFAULT_TRUE;
                    	}

                	} else if(empty($request->video_resolutions) && !empty($request->trailer_video_resolutions)){	
                		Log::info('Trailer resolutions : ');

                		$video_model->main_video_compress_status = COMPRESS_COMPLETED;

                		if ($request->hasFile('trailer_video')) {

                    		$no_need_compression = DEFAULT_FALSE;

                    	} else {

                    		$no_need_compression = DEFAULT_TRUE;
                    	}

                	} else {

                		Log::info('Empty Value');

                		$no_need_compression = DEFAULT_TRUE;
                			
                	}

                	/****** RESOLUTION CHECK END *****/

	            }

	        } else if($request->video_type == VIDEO_TYPE_YOUTUBE) {

                $video_model->video = get_youtube_embed_link($request->video);

                $video_model->trailer_video = get_youtube_embed_link($request->trailer_video);

            } else {
                
                $video_model->video = $request->video;

                $video_model->trailer_video = $request->trailer_video;
	           
	        }

	        if($request->hasFile('trailer_subtitle')) {

                if ($video_model->id) {

                    if ($video_model->trailer_subtitle) {

                        Helper::delete_picture($video_model->trailer_subtitle, "/uploads/subtitles/");  

                    }  
                }

                $video_model->trailer_subtitle =  Helper::subtitle_upload($request->file('trailer_subtitle'));

            }

            if($request->hasFile('video_subtitle')) {

                if ($video_model->id) {

                    if ($video_model->video_subtitle) {

                        Helper::delete_picture($video_model->video_subtitle, "/uploads/subtitles/");  

                    }  
                }

                $video_model->video_subtitle =  Helper::subtitle_upload($request->file('video_subtitle'));

            }

            // Intialize the position is zero

            $position = 0;

            // Check the video has genre type or not

            if ($video_model->genre_id) {

                // If genre, in order to give the position of the admin videos

                $position = 1; // By default intialize 1

                /*
                 * Check is there any videos present in same genre, 
                 * if it is assign the position with increment of 1 otherwise intialize as zero
                 */

                if($check_position = AdminVideo::where('genre_id' , $video_model->genre_id)
                        ->orderBy('position' , 'desc')->first()) {

                	// check the edit or upload video 

                	if($request->admin_video_id) {

                		if($video_model->genre_id == $request->genre_id) {

                			// VIDHYA R - On edit no need to update the position of the video. if they wants to change the video position means USE CHANGE POSITION option in admin panel

                			$position = $video_model->position;

                		} else {

                			// When admin changing the genre - need to update the latest genre position for selected video 

                			$position = $check_position->position + 1;

                	
                		}

                	} else {
                    	
                    	$position = $check_position->position + 1;

                	}
                } 

            }

            $video_model->position = $position;

            Log::info("no_need_compression ".$no_need_compression);

            // Incase of queue and ffmpeg not configured properly, compress will not work so by default we will approve the videos

            if (envfile('QUEUE_DRIVER') != 'redis' || Setting::get('ffmpeg_installed') == FFMPEG_NOT_INSTALLED || $no_need_compression) {

                \Log::info("Queue Driver : ".envfile('QUEUE_DRIVER'));

                // On update check the video & trailer video having resolutions

                if($request->admin_video_id && $no_need_compression) {

                	$video_model->video_resolutions = $video_model->video_resolutions ? $video_model->video_resolutions : "";

                	$video_model->video_resize_path = $video_model->video_resize_path ? $video_model->video_resize_path : "";

                } else {

	        		$video_model->video_resolutions = '';

	        		$video_model->video_resize_path = '';

                }

                if($request->admin_video_id && $no_need_compression) {

                	$video_model->trailer_video_resolutions = $video_model->trailer_video_resolutions ? $video_model->trailer_video_resolutions : "";

                	$video_model->trailer_resize_path = $video_model->trailer_resize_path ? $video_model->trailer_resize_path : "";

                } else {

	        		$video_model->video_resolutions = '';

	        		$video_model->trailer_resize_path = '';

                }

	        	// check the moderator uploaded video or admin uploaded video

	        	// if(is_numeric($video_model->uploaded_by) && $request->admin_video_id && $video_model->status ==) {

	        	// 	if ($request->admin_video_id) {

	        			// BY VIDHYA - NO NEED TO UPDATE THE STATUS OF THE VIDEO (FOR FOLLOWING ISSUE)

	        			// - Admin declined the video and moderator(or admin )edited the video. Automatically its getting approved

	        			// $video_model->is_approved = DEFAULT_TRUE;
	        			
	        		// } else {

						// $video_model->is_approved = DEFAULT_FALSE;

					// }

	        	// } else {

	        		// $video_model->is_approved = DEFAULT_TRUE;

	        	// }

	        	$video_model->main_video_compress_status = COMPRESSION_NOT_HAPPEN;

                $video_model->trailer_compress_status = COMPRESSION_NOT_HAPPEN;

                $video_model->compress_status = COMPRESSION_NOT_HAPPEN;
            }

            if ($video_model->save()) {

            	if($request->hasFile('default_image')) {

	 				if ($request->admin_video_id) {

	                	Helper::delete_picture($video_model->default_image, "/uploads/images/");

	                	Helper::delete_picture($video_model->default_image, "/uploads/images/385x225/");

	                }

	                $video_model->default_image = Helper::normal_upload_picture($request->file('default_image'), '', "video_".$video_model->id."_001");

	                // If ffmpeg installed then resize the image

                	if (Setting::get('ffmpeg_installed') == FFMPEG_INSTALLED) {

                		$path = public_path().'/uploads/images/385x225';

                		$default_image_input_path = public_path().'/uploads/images/'.get_video_end($video_model->default_image);

                		$default_image_output_path = public_path().'/uploads/images/385x225/'.get_video_end($video_model->default_image);

                		if (!File::isDirectory($path)) {

							File::makeDirectory($path, $mode = 0777, true, true);

						}

		                $FFmpeg = new \FFmpeg;

			            $FFmpeg
			                ->input($default_image_input_path)
			                ->imageScale("385:225")
			                ->output($default_image_output_path)
			                ->ready();

			            Log::info(print_r($FFmpeg->command,true));

		            }

	                $video_model->save();
	            
	            }

	            if($request->hasFile('other_image1')) {

	            	Log::info("other_image1");

	 				// To upload second image of the video

	 				Helper::upload_video_image($request->file('other_image1'),$video_model->id, $position = 2);
            
	            }

	            if($request->hasFile('mobile_image')) {

	            	Log::info("mobile_image");

	            	if ($request->admin_video_id) {

	                	Helper::delete_picture($video_model->mobile_image, "/uploads/images/");

	                }

	 				// To upload mobile image of the video

	                $video_model->mobile_image = Helper::normal_upload_picture($request->file('mobile_image'), '', "video_mobile_".$video_model->id."_001");
            
	            }

	            if($request->hasFile('other_image2')) {

	            	Log::info("other_image2");

	            	// To upload third image of the video

	 				Helper::upload_video_image($request->file('other_image2'),$video_model->id, $position = 3);

	            }

	            // Save cast & crews based on tagging into this video

            	$cast_crew_ids = $request->cast_crew_ids ? $request->cast_crew_ids : [];

            	$removed_crews = [];

            	$crews_id = [];

            	if ($request->admin_video_id) {

            		// Load Cast & crews which is tagged into this video

            		$load_cast_crews = VideoCastCrew::select('cast_crew_id')->where('admin_video_id', $request->admin_video_id)->get();

            		if (count($load_cast_crews) > 0 ) {

            			// Check if any removed index present or not, if removed from the list.delete the row from db

            			foreach ($load_cast_crews as $key => $value) {
            				
            				if (in_array($value->cast_crew_id, $cast_crew_ids)) {

            					$crews_id[] = $value->cast_crew_id;

            				} else {

            					$removed_crews[] = $value->cast_crew_id;

            				}

            			}

            			// If the admin removed any one of the crews, those cast will be deleting here
            			if(count($removed_crews) > 0) {

            				VideoCastCrew::whereIn('cast_crew_id', $removed_crews)->where('admin_video_id', $video_model->id)->delete();

            			}

            		}

            	} 

            	// Check is there any cast deleted by admin

            	// if (count($crews_id) > 0) {

            		// If the casts exists, check the cast existing or not. if not add the cast details.

            		foreach ($cast_crew_ids as $key => $value) {

            			if (!in_array($value, $crews_id)) {

		            		$video_cast_crew = new VideoCastCrew;

		            		$video_cast_crew->admin_video_id = $video_model->id;

		            		$video_cast_crew->cast_crew_id = $value;

		            		$video_cast_crew->save();

	            		}

	            	}

	            	// }


	            // Queue Dispatch code

	            // Check whether the video resolutions present or not if is need to run queue

	            if ($video_model->trailer_video_resolutions && $request->hasFile('trailer_video')) {

                    if ($trailer_video_details) {

                        $inputFile = $trailer_video_details['baseUrl'];

                        $local_url = $trailer_video_details['local_url'];

                        $file_name = $trailer_video_details['file_name'];

                        if (file_exists($inputFile)) {

                        	$video_status = $video_model->status; // If any failure in compression, the status will revert back to old status ( edit video)

                        	$video_model->is_approved = DEFAULT_FALSE;

                    		$video_model->save();

                            dispatch(new StreamviewCompressVideo($inputFile, $local_url, TRAILER_VIDEO, $video_model->id,$file_name,$request->send_notification, $video_status , $request->admin_video_id));
                        }
                        
                    }
                    
                } else {

	                $video_model->trailer_compress_status = COMPRESSION_NOT_HAPPEN;

                }
                
	            if($video_model->video_resolutions && $request->hasFile('video')) {

                    if ($main_video_details) {

                    	Log::info("Inside video resolutions main video");

                        $inputFile = $main_video_details['baseUrl'];

                        $local_url = $main_video_details['local_url'];

                        $file_name = $main_video_details['file_name'];

                        Log::info(file_exists($inputFile));

                        if (file_exists($inputFile)) {

                        	$video_status = $video_model->status; // If any failure in compression, the status will revert back to old status ( edit video)

                        	$video_model->is_approved = DEFAULT_FALSE;

                    		$video_model->save();

                        	Log::info($inputFile);

                            dispatch(new StreamviewCompressVideo($inputFile, $local_url, MAIN_VIDEO, $video_model->id, $file_name, $request->send_notification , $video_status , $request->admin_video_id));
                       
                        }

                    }

                } else {

                	$video_model->main_video_compress_status = COMPRESSION_NOT_HAPPEN;

                }

                if($video_model->trailer_compress_status == COMPRESSION_NOT_HAPPEN && $video_model->main_video_compress_status == COMPRESSION_NOT_HAPPEN) {
	                
	                $video_model->compress_status = COMPRESSION_NOT_HAPPEN;

                }

                $video_model->save();

                // If ffmpeg installed then generate gif

                if (Setting::get('ffmpeg_installed') == FFMPEG_INSTALLED) {

                	$path = public_path().'/uploads/gifs';

            		if (!File::isDirectory($path)) {

						File::makeDirectory($path, $mode = 0777, true, true);

					}

	                // Gif Generation Based on three images

		            $FFmpeg = new \FFmpeg;

		            $FFmpeg
		                ->setImage('image2')
		                ->setFrameRate(1)
		                ->input( public_path()."/uploads/images/video_{$video_model->id}_%03d.png")
		                ->setAspectRatio("4:2")
		                ->frameRate(50)
		                ->output(public_path()."/uploads/gifs/video_{$video_model->id}.gif")
		                ->ready();

		            $video_model->video_gif_image = Helper::web_url()."/uploads/gifs/video_{$video_model->id}.gif";

		            $video_model->save();

		            Log::info(print_r($FFmpeg->command,true));

		        }

		        // Send Email and push notification to users

		        if($video_model->is_approved && $video_model->status) {

            		Log::info("Send Notification ".$request->send_notification);

                    if ($request->send_notification) {

                        Log::info("Mail queue started : ".'Success');

                        dispatch(new SendVideoMail($video_model->id, $request->admin_video_id ? EDIT_VIDEO : NEW_VIDEO));

                        Log::info("Mail queue completed : ".'Success');

                        Notification::save_notification($video_model->id);

                        // Send Notifications to mobile push notification

				        $id = 'all';

				        // Load Template content of upload/edit video

				        $template = EmailTemplate::where('template_type', $request->admin_video_id ? EDIT_VIDEO : NEW_VIDEO)->first();

				        $subject = $content = "";

				        if ($template) {

				        	$category = Category::find($video_model->category_id);

				        	$content = str_replace('<%category_name%>', $category->name, $template->content);

	                        $content = str_replace('&lt;%category_name%&gt;', $category->name, $content);

	                        $content = str_replace('<%video_name%>', $video_model->title, $content);

	                        $content = str_replace('&lt;%video_name%&gt;', $video_model->title, $content);

	                        $content = strip_tags($content);

	                        $subject = $template->subject ?  str_replace('<%video_name%>', $video_model->title,$template->subject) : '';

	                        $subject = $subject ?  str_replace('&lt;%video_name%&gt;', $video_model->title, $subject) : '';

	                        $subject = strip_tags($subject);

				        }

				        // Sending Notifications to mobile

	            		$data = [];

		                $data['admin_video_id'] = $video_model->id;

		                $title = Setting::get('site_name');

		                $message = $video_model->title ? $video_model->title : "";

                        dispatch(new PushNotification(PUSH_TO_ALL , $title , $message, $data));

		                // PushRepo::send_push_notification(PUSH_TO_ALL , $title , $message, $data);

                    }

			    }

			    // Check the mobile_image is not empty

			    if(!$video_model->mobile_image) {

			    	$video_model->mobile_image = $video_model->default_image;

			    	$video_model->save();
			    }

	        } else {

	        	throw new Exception(Helper::get_error_message(167), 167);
	        }

 			DB::commit();

 			$response_array = ['success'=>true, 'message'=>tr('video_upload_success'),'data'=>$video_model];

 			return response()->json($response_array);

 		} catch(Exception $e) {

 			DB::rollback();

 			$response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

 			return response()->json($response_array);

 		}

 	}

 	/**
 	 * Function Name : image_resolution_covertor
 	 *
 	 * @uses used to change the image resolutions
 	 *
 	 * @created: Vidhya R
 	 * 
 	 * @updated: Vidhya R
 	 *
 	 * @param object $request - Video related details
 	 *
 	 * @return response of success/failure response of datas
 	 */

 	public static function image_resolution_covertor($picture , $width = null, $height = null , $type = "fit") {

 	}

 	/**
	 *
	 * Function Name: 
	 *
	 * @uses used to get the common list details for video
	 *
	 * @created Vidhya R
	 *
	 * @updated Vidhya R
	 *
	 * @param 
	 *
	 * @return
	 */

 	public static function video_list_response($admin_video_ids, $orderby = 'updated_at', $other_select_columns = "") {

 		$base_query = AdminVideo::whereIn('admin_videos.id' , $admin_video_ids)
 							->orderBy($orderby , 'desc');

 		if($other_select_columns != "") {

 			$base_query = $base_query->BaseResponse($other_select_columns);

 		} else {

 			$base_query = $base_query->BaseResponse();
 		}
 		
 		$admin_videos = $base_query->get();

 		return $admin_videos;

 	}
}