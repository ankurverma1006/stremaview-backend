<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use App\Helpers\VideoHelper;

use App\Jobs\NormalPushNotification;

use App\Repositories\PaymentRepository as PaymentRepo;

use App\Repositories\VideoRepository as VideoRepo;

use Log;

use Hash;

use File;

use DB;

use Auth;

use Setting;

use Validator;

use Exception;

use App\Admin;

use App\AdminVideo;

use App\AdminVideoImage;

use App\Card;

use App\CastCrew;

use App\Category;

use App\ContinueWatchingVideo;

use App\Coupon;

use App\Flag;

use App\Genre;

use App\Language;

use App\LikeDislikeVideo;

use App\MobileRegister;

use App\Moderator;

use App\Notification;

use App\OfflineAdminVideo;

use App\Page;

use App\PageCounter;

use App\PayPerView;

use App\Settings;

use App\SubCategory;

use App\SubProfile;

use App\Subscription;

use App\User;

use App\UserCoupon;

use App\UserHistory;

use App\UserLoggedDevice;

use App\UserPayment;

use App\UserRating;

use App\VideoCastCrew;

use App\Wishlist;

class V4UserApiController extends Controller {

    public function __construct(Request $request) {

        $this->middleware('NewUserApiVal', ['except' => ['register', 'login']]);

    }

    /**
	 *
	 * @method home_first_section() 
	 *
	 * @uses used to get the first set of sections based on the page type
	 *
	 * @created Vidhya R
	 *
	 * @updated Vidhya R
	 *
	 * @param
	 *
	 * @return
	 */

    public function home_first_section(Request $request) {

        Log::info("home_first_section".print_r($request->all() , true));

    	try {

            $user_details = User::find($request->id);

            $sub_profile_details = SubProfile::find($request->sub_profile_id);

            $data = [];

            /* - - - - - - - - - - - My List section - - - - - - - - - - - */

            $wishlist_videos = VideoHelper::wishlist_videos($request);

            $wishlist_videos_data['title'] = tr('header_wishlist');

            $wishlist_videos_data['url_type'] = URL_TYPE_WISHLIST;

            $wishlist_videos_data['url_page_id'] = 0;

            $wishlist_videos_data['see_all_url'] = route('userapi.section_wishlists');

            $wishlist_videos_data['data'] = $wishlist_videos ?: [];

            array_push($data, $wishlist_videos_data);

            /* - - - - - - - - - - - My List section - - - - - - - - - - - */


            /* - - - - - - - - - - - New Releases section - - - - - - - - - - - */

            $new_releases_videos = VideoHelper::new_releases_videos($request);

            $new_releases_videos_data['title'] = tr('header_new_releases');

            $new_releases_videos_data['url_type'] = URL_TYPE_NEW_RELEASE;

            $new_releases_videos_data['url_page_id'] = 0;

            $new_releases_videos_data['see_all_url'] = route('userapi.section_new_releases');

            $new_releases_videos_data['data'] = $new_releases_videos ?: [];

            array_push($data, $new_releases_videos_data);

            /* - - - - - - - - - - - New Releases section - - - - - - - - - - - */


            /* - - - - - - - - - - - Continue Watching section - - - - - - - - - - - */

            $continue_watching_videos = VideoHelper::continue_watching_videos($request);

            $c_w_videos_data['title'] = tr('header_continue_watching' , $sub_profile_details->name);

            $c_w_videos_data['url_type'] = URL_TYPE_CONTINUE_WATCHING;

            $c_w_videos_data['url_page_id'] = 0;

            $c_w_videos_data['see_all_url'] = route('userapi.section_continue_watching_videos');

            $c_w_videos_data['data'] = $continue_watching_videos ?: [];

            array_push($data, $c_w_videos_data);

            /* - - - - - - - - - - - Continue Watching section - - - - - - - - - - - */

            /* - - - - - - - - - - - Trending Now section - - - - - - - - - - - */

            $trending_videos = VideoHelper::trending_videos($request);

            $trending_videos_data['title'] = tr('header_trending');

            $trending_videos_data['url_type'] = URL_TYPE_TRENDING;

            $trending_videos_data['url_page_id'] = 0;

            $trending_videos_data['see_all_url'] = route('userapi.section_trending');

            $trending_videos_data['data'] = $trending_videos ?: [];

            array_push($data, $trending_videos_data);

            /* - - - - - - - - - - - Trending Now section - - - - - - - - - - - */

            /* - - - - - - - - - - - Recommented section - - - - - - - - - - - */

            $suggestion_videos = VideoHelper::suggestion_videos($request);

            $suggestion_videos_data['title'] = tr('header_recommended');

            $suggestion_videos_data['url_type'] = URL_TYPE_SUGGESTION;

            $suggestion_videos_data['url_page_id'] = 0;

            $suggestion_videos_data['see_all_url'] = route('userapi.section_suggestions');

            $suggestion_videos_data['data'] = $suggestion_videos ?: [];

            array_push($data, $suggestion_videos_data);

            /* - - - - - - - - - - - Recommented section - - - - - - - - - - - */

            /* - - - - - - - - - - - Banner section - - - - - - - - - - - */

            $banner_videos = VideoHelper::banner_videos($request);

            $banner_videos_data['title'] = tr('header_banner');

            $banner_videos_data['url_type'] = "";

            $banner_videos_data['url_page_id'] = 0;

            $banner_videos_data['see_all_url'] = "";

            $banner_videos_data['data'] = $banner_videos ?: [];

            /* - - - - - - - - - - - Banner section - - - - - - - - - - - */

            /* - - - - - - - - - - - Originals section - - - - - - - - - - - */

            $originals_videos = VideoHelper::original_videos($request);

            $originals_videos_data['title'] = tr('header_originals');

            $originals_videos_data['url_type'] = URL_TYPE_ORIGINAL;

            $originals_videos_data['url_page_id'] = 0;

            $originals_videos_data['see_all_url'] = route('userapi.section_originals');

            $originals_videos_data['data'] = $originals_videos ?: [];

            /* - - - - - - - - - - - Originals section - - - - - - - - - - - */

            // Get the page title

            $api_page_title = ""; 

            if($request->category_id) {

                $category_details = Category::find($request->category_id);

                $api_page_title = $category_details->name ?: "Category"; 

            }

			$response_array = ['success' => true , 'page_title' => $api_page_title,'data' => $data ,'banner' => $banner_videos_data , 'originals' => $originals_videos_data];

			return response()->json($response_array , 200);

		} catch(Exception $e) {

			$error_messages = $e->getMessage();

			$error_code = $e->getCode();

			$response_array = ['success' => false , 'error_messages' => $error_messages , 'error_code' => $error_code];

			return response()->json($response_array , 200);

		}
    
    }

    /**
     *
     * @method home_second_section() 
     *
     * @uses used to get the first set of sections based on the page type
     *
     * @created Vidhya R
     *
     * @updated Vidhya R
     *
     * @param
     *
     * @return
     */

    public function home_second_section(Request $request) {

        try {

            $validator = Validator::make($request->all() , [
                'skip' => ''
            ]);

            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            }

            $skip = $request->skip ?: 0;

            $take = $request->take ?: 4;

            $base_query = SubCategory::where('sub_categories.is_approved', APPROVED)
                                    ->orderBy('sub_categories.updated_at', 'desc')
                                    ->skip($skip)->take($take);

            if($request->category_id) {

                $base_query = $base_query->where('category_id', $request->category_id);

            }

            $sub_categories = $base_query->get();

            $data = [];

            foreach ($sub_categories as $key => $sub_category_details) {

                // Get the sub category videos

                $request->request->add(['sub_category_id' => $sub_category_details->id]);

                $sub_category_videos = VideoHelper::sub_category_videos($request);

                if(count($sub_category_videos)) {

                    $sub_category_videos_data['title'] = $sub_category_details->name;

                    $sub_category_videos_data['url_type'] = URL_TYPE_SUB_CATEGORY;

                    $sub_category_videos_data['url_page_id'] = $sub_category_details->id;

                    $sub_category_videos_data['see_all_url'] = route('userapi.sub_category_videos');

                    $sub_category_videos_data['data'] = $sub_category_videos ?: [];

                    array_push($data, $sub_category_videos_data);

                }

            }

            $response_array = ['success' => true , 'data' => $data];

            return response()->json($response_array , 200);

        } catch(Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false , 'error_messages' => $error_messages , 'error_code' => $error_code];

            return response()->json($response_array , 200);

        }

    }

    /**
     *
     * @method home_second_section() 
     *
     * @uses used to get the first set of sections based on the page type
     *
     * @created Vidhya R
     *
     * @updated Vidhya Rs
     *
     * @param
     *
     * @return
     */

    public function see_all_section(Request $request) {

        Log::info("See All section".print_r($request->all(), true));

        try {

            switch ($request->url_type) {

                case URL_TYPE_WISHLIST:
                    $admin_videos = VideoHelper::wishlist_videos($request);
                    break;
                case URL_TYPE_NEW_RELEASE:
                    $admin_videos = VideoHelper::new_releases_videos($request);
                    break;
                case URL_TYPE_CONTINUE_WATCHING:
                    $admin_videos = VideoHelper::continue_watching_videos($request);
                    break;
                case URL_TYPE_TRENDING:
                    $admin_videos = VideoHelper::trending_videos($request);
                    break;
                case URL_TYPE_SUGGESTION:
                    $admin_videos = VideoHelper::suggestion_videos($request);
                    break;
                case URL_TYPE_ORIGINAL:
                    $admin_videos = VideoHelper::original_videos($request);
                    break;
                case URL_TYPE_CATEGORY:
                    $request->request->add(['category_id' => $request->url_page_id]);

                    $admin_videos = VideoHelper::category_videos($request);
                    break;
                case URL_TYPE_SUB_CATEGORY:
                    $request->request->add(['sub_category_id' => $request->url_page_id]);
                    $admin_videos = VideoHelper::sub_category_videos($request);
                    break;
                case URL_TYPE_GENRE:
                    $request->request->add(['genre_id' => $request->url_page_id]);
                    $admin_videos = VideoHelper::genre_videos($request);
                    break;
                case URL_TYPE_CAST_CREW:
                    $request->request->add(['cast_crew_id' => $request->url_page_id]);
                    $admin_videos = VideoHelper::cast_crew_videos($request);
                    break;
                default:
                    $admin_videos = VideoHelper::suggestion_videos($request);
                    break;
            }

            $response_array = ['success' => true, 'data' => $admin_videos];

            return response()->json($response_array, 200);

        } catch(Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false , 'error_messages' => $error_messages , 'error_code' => $error_code];

            return response()->json($response_array , 200);

        }

    }

	/**
     *
     * @method admin_videos_view()
     *
     * @uses used to get video details based on the selected video id
     *
     * @created Vidhya R
     *
     * @updated Vidhya R
     *
     * @param integer admin_video_id
     *
     * @return JSON Response
     */

    public function admin_videos_view(Request $request) {

        try {

            $admin_video_details = AdminVideo::SingleVideoResponse()->where('admin_videos.id' , $request->admin_video_id)->first();

            $user_details = User::find($request->id);

            if(!$admin_video_details) {

                throw new Exception(Helper::get_error_message(157), 157);
                
            }

            // Check the video is marked as spam

            if (check_flag_video($request->admin_video_id, $request->sub_profile_id)) {

                throw new Exception(Helper::get_error_message(904), 904);

            }

            $data = new \stdClass();

            $data = $admin_video_details;

            $data->user_type = $user_details->user_type;

            $data->share_link = Setting::get('ANGULAR_SITE_URL').'video/'.$request->admin_video_id;


            // ************** Continue watching section *************** //

            $continue_watching_video_details = VideoHelper::videoPlayDuration($request->admin_video_id, $request->sub_profile_id);

            $data->video_play_duration = $data->seek_time_in_seconds = "";

            if($continue_watching_video_details) {
                
                $data->video_play_duration = $continue_watching_video_details->duration;

                $data->seek_time_in_seconds = $continue_watching_video_details->duration_in_seconds;
            }

            // ************** Continue watching section *************** //

            $data->wishlist_status = VideoHelper::wishlist_status($request->admin_video_id,$request->sub_profile_id);

            $data->history_status = VideoHelper::history_status($request->admin_video_id, $request->sub_profile_id);

            $data->is_liked = VideoHelper::like_status($request->admin_video_id, $request->sub_profile_id,$request->admin_video_id);

            $data->likes = number_format_short(VideoHelper::likes_count($request->admin_video_id));
            
            $data->dislikes = number_format_short(VideoHelper::dislikes_count($request->admin_video_id));

            $data->currency = Setting::get('currency') ?: "$";

            $data->main_video = $admin_video_details->video;

            $data->trailer_video = $admin_video_details->trailer_video;

            $data->is_series = $admin_video_details->genre_id ? YES : NO;

            /* $ $ $ $ $ $ $ $ $ $ PPV STATUS CHECK START $ $ $ $ $ $ $ $ $ $*/

            $ppv_details = VideoRepo::pay_per_views_status_check($user_details->id, $user_details->user_type, $data)->getData();

            $data->is_pay_per_view = $ppv_details->success ? NO : YES; // not using. Don't use.

            $watch_video_free = DEFAULT_TRUE;

            $data->should_display_ppv = $ppv_details->success == $watch_video_free ? NO : YES;

            $ppv_page_type_data = VideoHelper::get_ppv_page_type($admin_video_details, $user_details->user_type, $admin_video_details->is_pay_per_view);

            $data->ppv_page_type = $ppv_page_type_data->ppv_page_type;

            $data->ppv_page_type_content = $ppv_page_type_data->ppv_page_type_content;

            /* $ $ $ $ $ $ $ $ $ $ PPV STATUS CHECK END $ $ $ $ $ $ $ $ $ $*/


            $data->images = AdminVideoImage::where('admin_video_id' , $request->admin_video_id)->orderBy('is_default' , 'desc')
                                    ->lists('image')->toArray();

            $data->cast_crews = VideoCastCrew::select('cast_crew_id', 'name')
                                    ->where('admin_video_id', $request->admin_video_id)
                                    ->leftjoin('cast_crews', 'cast_crews.id', '=', 'video_cast_crews.cast_crew_id')
                                    ->get()->toArray();

            /* @@@@@@@@@@@@@@@@@@@@@@ DOWNLOAD STATUS START @@@@@@@@@@@@@@@@@@@@@@ */

            $download_button_status = VideoHelper::download_button_status($request->admin_video_id , $request->id, $admin_video_details->download_status, $user_details->user_type, $data->is_pay_per_view);

            $data->download_button_status = $download_button_status;


            $offline_admin_video_details = OfflineAdminVideo::where('admin_video_id' , $request->admin_video_id)
                                                ->where('user_id', $request->user_id)
                                                ->first();

            $download_status_text = "";  $downloading_video_status = 0;

            if($offline_admin_video_details) {

                $downloading_video_status = $offline_admin_video_details->status;

                $download_status_text = VideoHelper::download_status_text($offline_admin_video_details->status);

            }

            $data->downloading_video_status = $downloading_video_status;

            $data->download_status_text = $download_status_text;

            $main_resolutions = $trailer_resolutions = $download_urls = [];

            // Main video and download urls data

            if ($admin_video_details->video_resolutions) {

                $request_data = ['video_resolutions' => $admin_video_details->video_resolutions , 'video_resize_path' => $admin_video_details->video_resize_path, 'video' => $admin_video_details->video , 'device_type' => $user_details->device_type,'video_type' => $admin_video_details->video_type ,'video_upload_type' => $admin_video_details->video_upload_type];

                $video_res_request = new \Illuminate\Http\Request();

                $video_res_request->setMethod('POST');

                $video_res_request->request->add($request_data);

                $video_resolutions_data = VideoHelper::get_video_resolutions($video_res_request);

                $main_resolutions = $video_resolutions_data[0];

                $download_urls = $video_resolutions_data[1];

            }

            $data->main_resolutions = $main_resolutions ?: [];

            $data->download_urls = $download_urls ?: [];
            
            /* @@@@@@@@@@@@@@@@@@@@@@ DOWNLOAD STATUS START @@@@@@@@@@@@@@@@@@@@@@ */


            $response_array = ['success' => true , 'data' => $data];

            return response()->json($response_array , 200);

        } catch(Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false , 'error_messages' => $error_messages , 'error_code' => $error_code];

            return response()->json($response_array , 200);

        }

    }

    /**
	 *
	 * @method admin_videos_view_second()
	 *
	 * @uses used to get video details based on the selected video id
	 *
	 * @created Vidhya R
	 *
	 * @updated Vidhya R
	 *
	 * @param integer admin_video_id
	 *
	 * @return JSON Response
	 */

	public function admin_videos_view_second(Request $request) {

		try {

			$admin_video_details = AdminVideo::SingleVideoResponse()->where('admin_videos.id' , $request->admin_video_id)->first();

            $user_details = User::find($request->id);

			if(!$admin_video_details) {

				throw new Exception(Helper::get_error_message(157), 157);
				
			}

            // Check the video is marked as spam

            if (check_flag_video($request->admin_video_id, $request->sub_profile_id)) {

                throw new Exception(Helper::get_error_message(904), 904);

            }

            $data = new \stdClass();

            /* $ $ $ $ $ $ $ $ $ $ PPV STATUS CHECK START $ $ $ $ $ $ $ $ $ $*/

            $ppv_details = VideoRepo::pay_per_views_status_check($user_details->id, $user_details->user_type, $admin_video_details)->getData();

            $is_pay_per_view = $ppv_details->success ? YES : NO;

            /* $ $ $ $ $ $ $ $ $ $ PPV STATUS CHECK END $ $ $ $ $ $ $ $ $ $*/

            $data->is_series = $admin_video_details->genre_id ? YES : NO;


            $offline_admin_video_details = OfflineAdminVideo::where('admin_video_id' , $request->admin_video_id)
                                                ->where('user_id', $request->user_id)
                                                ->first();


            $main_resolutions = $trailer_resolutions = $download_urls = [];

            $trailer_video_type = $admin_video_details->video_type;

            // Trailer video resolutions

            if ($admin_video_details->trailer_video_resolutions) {

                $request_data = ['video_resolutions' => $admin_video_details->trailer_video_resolutions , 'video_resize_path' => $admin_video_details->trailer_resize_path, 'video' => $admin_video_details->trailer_video , 'device_type' => $user_details->device_type,'video_type' => $admin_video_details->video_type ,'video_upload_type' => $admin_video_details->video_upload_type];

                $video_res_request = new \Illuminate\Http\Request();

                $video_res_request->setMethod('POST');

                $video_res_request->request->add($request_data);

                $trailer_video_resolutions_data = VideoHelper::get_video_resolutions($video_res_request);

                $trailer_resolutions = $trailer_video_resolutions_data[0];

            } else {

                $trailer_video_type = $admin_video_details->video_type;

                $trailer_video_resolutions_data['original'] = $admin_video_details->trailer_video;

                $trailer_resolutions = $trailer_video_resolutions_data;
            }

            $trailer_data = $trailer_section_data = [];

            $trailer_data['name'] = $admin_video_details->title;

            $trailer_data['default_image'] = $trailer_data['mobile_image'] = $admin_video_details->default_image;

            $trailer_data['video_type'] = $trailer_video_type ?: VIDEO_TYPE_UPLOAD;

            $trailer_data['resolutions'] = $trailer_resolutions;

            array_push($trailer_section_data, $trailer_data);

            $data->trailer_section = $trailer_section_data;
            
            /* @@@@@@@@@@@@@@@@@@@@@@ DOWNLOAD STATUS START @@@@@@@@@@@@@@@@@@@@@@ */


            /** = = = = = = = GENRE SECTION = = = = = = = = */

            $data->genres = $data->genre_videos = [];

            if($admin_video_details->genre_id) {

                $genres = Genre::orderBy('created_at' , 'desc')->select('id as genre_id' , 'name as genre_name')->get();

                foreach ($genres as $key => $genre_details) {

                    $genre_details->is_selected = $genre_details->genre_id == $admin_video_details->genre_id ? YES : NO;
                }

                $data->genres = $genres;

                $request->request->add(['genre_id' => $admin_video_details->genre_id]);

                $data->genre_videos = VideoHelper::genre_videos($request);
           
            }

            /** = = = = = = = GENRE SECTION = = = = = = = = */

            // Suggestion videos for if is series = 0

            if($data->is_series == NO) {
                
                $suggestion_videos = VideoHelper::suggestion_videos($request);

                $suggestion_videos_data['title'] = tr('header_recommended');

                $suggestion_videos_data['see_all_url'] = route('userapi.section_suggestions');

                $suggestion_videos_data['data'] = $suggestion_videos ?: [];

                $data->suggestion_videos = $suggestion_videos_data;

            }

			$response_array = ['success' => true , 'data' => $data];

			return response()->json($response_array , 200);

		} catch(Exception $e) {

			$error_messages = $e->getMessage();

			$error_code = $e->getCode();

			$response_array = ['success' => false , 'error_messages' => $error_messages , 'error_code' => $error_code];

			return response()->json($response_array , 200);

		}

	}

    /**
     * @method wishlist_index()
     *
     * @uses To get all the lists based on logged in user id
     *
     * @created Vidhya R
     * 
     * @updated Vidhya R
     *
     * @param object $request - Wishlist id
     *
     * @return respone with array of objects
     */
    public function wishlist_index(Request $request)  {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {
            
                $wishlist_videos = VideoHelper::wishlist_videos($request);
                
                $response_array = ['success' => true, 'data' => $wishlist_videos , 'total' => count($wishlist_videos)];

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }
    
    }

	/**
     * @method wishlist_operations()
     *
     * @uses To add / Remove by using this operation favorite
     *
     * @created Vidhya R
     *
     * @updated Vidhya R
     *
     * @param object $request - song id and user id, token
     *
     * @return response of details
     */
    public function wishlist_operations(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                [
                    'clear_all_status' => 'numeric|in:'.YES.','.NO,
                    'sub_profile_id' => 'required|exists:sub_profiles,id',
                    'admin_video_id' => $request->clear_all_status == NO ? 'required|integer|exists:admin_videos,id,status,'.VIDEO_PUBLISHED.',is_approved,'.VIDEO_APPROVED : "",
                ],
                [
                	'exists.sub_profile_id' => Helper::get_error_message(168),
                	'required.admin_video_id' => Helper::get_error_message(157)
                ]
            );

            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);

            } else {

                if ($request->clear_all_status == YES) {

                    Wishlist::where('sub_profile_id', $request->sub_profile_id)->delete();
                    
                    $response_array = ['success' => true, 'message'=> Helper::get_message(127)];

                } else {

                	if (check_flag_video($request->admin_video_id,$request->sub_profile_id)) {

	                    throw new Exception(Helper::get_error_message(904), 904);

	                }

                    $wishlist_details = Wishlist::where('admin_video_id', $request->admin_video_id)
                                ->where('sub_profile_id', $request->sub_profile_id)
                                ->first();

                    if ($wishlist_details) {

                        if ($wishlist_details->delete()) {

                            $response_array = ['success' => true, 'message'=> Helper::get_message(129)];

                        } else {

                            throw new Exception(Helper::error_message(113), 113);
                            
                        }

                    } else {

                        $wishlist_details = new Wishlist;

                        $wishlist_details->user_id = $request->id;

                        $wishlist_details->sub_profile_id = $request->sub_profile_id;

                        $wishlist_details->admin_video_id = $request->admin_video_id;

                        $wishlist_details->status = APPROVED;

                        $wishlist_details->save();

                        $response_array = ['success' => true, 'message' => Helper::get_message(128),'wishlist_id' => $wishlist_details->id];
                    }

                }
                
            }

            DB::commit();

            return response()->json($response_array , 200);

        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error'=>$error, 'error_code'=>$code];

            return response()->json($response_array);
        }

    }

    /**
     * @method notification_settings()
     *
     * To enable/disable notifications of email / push notification
     *
     * @created Vidhya R
     *
     * @updated Vidhya R
     *
     * @param - 
     *
     * @return response of details
     */
    public function notification_settings(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'status' => 'required|numeric',
                    'type'=>'required|in:'.EMAIL_NOTIFICATION.','.PUSH_NOTIFICATION
                )
            );

            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);

            } else {

                
                $user_details = User::find($request->id);
            
                if ($request->type == EMAIL_NOTIFICATION) {

                    $user_details->email_notification_status = $request->status;

                }

                if ($request->type == PUSH_NOTIFICATION) {

                    $user_details->push_notification_status = $request->status;

                }

                $user_details->save();

                if($request->status) {

                    $message = tr('notification_enable');

                } else {

                    $message = tr('notification_disable');

                }

                $data = ['id' => $user_details->id , 'token' => $user_details->token];

                $response_array = [
                    'success' => true ,'message' => $message, 
                    'email_notification_status' => (int) $user_details->email_notification_status,  // Don't remove int (used ios)
                    'push_notification_status' => (int) $user_details->push_notification_status,    // Don't remove int (used ios)
                    'data' => $data
                ];
                
            }

            DB::commit();

            return response()->json($response_array , 200);

        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error'=>$error, 'error_code'=>$code];

            return response()->json($response_array);
        }

    }

    /**
     * @method section_new_releases()
     *
     * @uses used to get videos based on the new release
     *
     * @created vidhya
     *
     * @updated vidhya
     *
     * @param - 
     *
     * @return response of details
     */
    public function section_new_releases(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {
            
                $new_releases_videos = VideoHelper::new_releases_videos($request);
                
                $response_array = ['success' => true, 'data' => $new_releases_videos , 'total' => count($new_releases_videos)];

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method section_trending()
     *
     * @uses used to get videos based on the new release
     *
     * @created vidhya
     *
     * @updated vidhya
     *
     * @param - 
     *
     * @return response of details
     */
    public function section_trending(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {
            
                $trending_videos = VideoHelper::trending_videos($request);
                
                $response_array = ['success' => true, 'data' => $trending_videos , 'total' => count($trending_videos)];

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method section_continue_watching_videos()
     *
     * @uses used to get videos based on the new release
     *
     * @created vidhya
     *
     * @updated vidhya
     *
     * @param - 
     *
     * @return response of details
     */
    public function section_continue_watching_videos(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {
            
                $continue_watching_videos = VideoHelper::continue_watching_videos($request);
                
                $response_array = ['success' => true, 'data' => $continue_watching_videos , 'total' => count($continue_watching_videos)];

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method section_suggestions()
     *
     * @uses used to get videos based on the new release
     *
     * @created vidhya
     *
     * @updated vidhya
     *
     * @param - 
     *
     * @return response of details
     */
    public function section_suggestions(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {
            
                $suggestion_videos = VideoHelper::suggestion_videos($request);
                
                $response_array = ['success' => true, 'data' => $suggestion_videos , 'total' => count($suggestion_videos)];

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method section_originals()
     *
     * @uses used to get videos based on the new release
     *
     * @created vidhya
     *
     * @updated vidhya
     *
     * @param - 
     *
     * @return response of details
     */
    public function section_originals(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {
            
                $suggestion_videos = VideoHelper::suggestion_videos($request);
                
                $response_array = ['success' => true, 'data' => $suggestion_videos , 'total' => count($suggestion_videos)];

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method category_videos()
     *
     * @uses used to get videos based on the new release
     *
     * @created vidhya
     *
     * @updated vidhya
     *
     * @param - 
     *
     * @return response of details
     */
    public function category_videos(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {
            
                $category_videos = VideoHelper::category_videos($request);
                
                $response_array = ['success' => true, 'data' => $category_videos , 'total' => count($category_videos)];

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method sub_category_videos()
     *
     * @uses used to get videos based on the new release
     *
     * @created vidhya
     *
     * @updated vidhya
     *
     * @param - 
     *
     * @return response of details
     */
    public function sub_category_videos(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {
            
                $sub_category_videos = VideoHelper::sub_category_videos($request);
                
                $response_array = ['success' => true, 'data' => $sub_category_videos , 'total' => count($sub_category_videos)];

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method genre_videos()
     *
     * @uses used to get videos based on the new release
     *
     * @created vidhya
     *
     * @updated vidhya
     *
     * @param - 
     *
     * @return response of details
     */
    public function genre_videos(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {
            
                $genre_videos = VideoHelper::genre_videos($request);
                
                $response_array = ['success' => true, 'data' => $genre_videos , 'total' => count($genre_videos)];

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method save_continue_watching_video
     *
     * @uses To save every few seconds in continue wattching videos
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - Userid, token, sub_profile_id & admin video id, duarion
     *
     * @return response of success / failure
     */
    public function continue_watching_videos_save(Request $request) {

        // If user watching the video, we shouldn't allow user get logout.

        check_token_expiry($request->id);

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(),[
                    'admin_video_id' => 'required|exists:admin_videos,id',
                    'duration'=>'required',
                ]);

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages);
                
            }

            $continue_watching_video_details = ContinueWatchingVideo::where('sub_profile_id', $request->sub_profile_id)->where('admin_video_id', $request->admin_video_id)->first();

            if (!$continue_watching_video_details) {

                $continue_watching_video_details = new ContinueWatchingVideo;

            }

            $admin_video_details = AdminVideo::where('is_approved' , 1)->where('status' , 1)->where('id', $request->admin_video_id)->first();

            if(!$admin_video_details) {
                
                throw new Exception(tr('video_not_approved_by_admin'));

            }

            $continue_watching_video_details->user_id = $request->id;

            $continue_watching_video_details->sub_profile_id = $request->sub_profile_id;

            $continue_watching_video_details->admin_video_id = $admin_video_details->id;

            $continue_watching_video_details->status = DEFAULT_TRUE; 

            $continue_watching_video_details->is_genre = $admin_video_details->genre_id > 0 ? DEFAULT_TRUE : DEFAULT_FALSE;

            if ($continue_watching_video_details->is_genre) {

                $genre_details = Genre::where('status', DEFAULT_TRUE)->where('is_approved', DEFAULT_TRUE)->where('id', $admin_video_details->genre_id)->first();

                if (!$genre_details) {

                    throw new Exception(tr('genre_not_found'), 101);
                    
                }

                $continue_watching_video_details->position = $admin_video_details->position;

                $continue_watching_video_details->genre_position = $genre_details->position;

            } else {

                $continue_watching_video_details->position = 0;

                $continue_watching_video_details->genre_position = 0;
            }

            $continue_watching_video_details->duration = gmdate("H:i:s", $request->duration);

            $continue_watching_video_details->duration_in_seconds = $request->duration ?: 0;

            if($continue_watching_video_details->save()) {

                DB::commit();
                
                $response_array = array('success' => true, 'data' => $continue_watching_video_details);

            } else {
                throw new Exception(tr('continue_watching_video_save_error'), 101);
                
            }

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method sub_profiles_delete()
     * 
     * @uses Based on logged in user , Delete sub profiles using sub profile id
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - User Id , sub profile id
     *
     * @return response of boolean
     */
    public function sub_profiles_delete(Request $request) {

        Log::info("sub_profiles_delete".print_r($request->all(), true));

        try {

            DB::beginTransaction();

            $user_details = User::find($request->id);

            $validator = Validator::make($request->all(),[
                    'delete_sub_profile_id'=>'required'
                ]);

            if ($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error);
            } 

            $next_sub_profile_id = $request->sub_profile_id;

            $delete_sub_profile = SubProfile::find($request->delete_sub_profile_id);

            if (!$delete_sub_profile) {

                throw new Exception(tr('sub_profile_details_not_found'), 101);
            }

            $delete_sub_profile_check_default_status = $delete_sub_profile->status;

            $delete_sub_profile->delete();

            $next_sub_profile = SubProfile::where('user_id', $request->id)->first();

            if ($delete_sub_profile_check_default_status == DEFAULT_SUB_PROFILE) {

                if(count($next_sub_profile) > 0) {

                    $next_sub_profile->status = DEFAULT_TRUE;

                    $next_sub_profile->save();

                    $next_sub_profile_id = $next_sub_profile->id;

                } else {

                    throw new Exception(Helper::get_error_message(169), 169);
                }
            }
            
            $user_details->no_of_account -= 1;

            if ($user_details->save()) {

                $response_array = ['success' => true , 'message' => tr('sub_profile_deleted'),'sub_profile_id' => $next_sub_profile_id];

            } else {

                throw new Exception(tr('user_details_not_save'));
            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch(Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method spam_videos()
     * 
     * @uses index
     *
     * @created Vithya R 
     *
     * @updated Vithya R 
     *
     * @param object $request - sub profile id, video id
     * 
     * @return spam video details
     */
    public function spam_videos(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                            'sub_profile_id'=>'required|exists:sub_profiles,id',
                            'skip' => 'required|numeric',

                        ], 
                        [
                            'exists' => 'The :attribute doesn\'t exists',
                        ]);
        
            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            }

            $base_query = Flag::where('flags.user_id', $request->id)
                                ->where('flags.sub_profile_id', $request->sub_profile_id);

            $take = Setting::get('admin_take_count', 12);

            $skip = $request->skip ?: 0;

            $suggestion_video_ids = $base_query->skip($skip)->take($take)->lists('video_id')->toArray();

            $admin_videos = VideoRepo::video_list_response($suggestion_video_ids);

            $response_array = ['success' => true, 'data' => $admin_videos];

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method spam_videos_add()
     * 
     * @uses Spam videos based on each single video based on logged in user id, If they flagged th video they wont see in any of the pages except spam videos page
     *
     * @created Vithya R 
     *
     * @updated Vithya R 
     *
     * @param object $request - sub profile id, video id
     * 
     * @return spam video details
     */
    public function spam_videos_add(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                            'admin_video_id' => 'required|exists:admin_videos,id',
                            'sub_profile_id'=>'required|exists:sub_profiles,id',
                            'reason' => 'required',
                        ], 
                        [
                            'exists' => 'The :attribute doesn\'t exists',
                        ]);
        
            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            }

            $spam_video_details = Flag::where('user_id', $request->id)->where('video_id', $request->admin_video_id)->where('sub_profile_id', $request->sub_profile_id)->first();

            if (!$spam_video_details) {

                $data = $request->all();

                $data['user_id'] = $request->id;

                $data['video_id'] =$request->admin_video_id;

                $data['sub_profile_id'] = $request->sub_profile_id;
                
                $data['status'] = DEFAULT_TRUE;

                if (Flag::create($data)) {

                    $response_array = ['success' => true, 'message' => tr('report_video_success_msg')];

                } else {

                    throw new Exception(tr('admin_published_video_failure'), 101);
                    
                }

            } else {

                $spam_video_details->status =  DEFAULT_TRUE;

                $spam_video_details->save();

                $response_array = ['success' => true, 'message' => tr('report_video_success_msg')];
            
            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

    }

    /**
     * @method spam_videos_remove()
     * 
     * @uses Remove Spam videos based on each single video based on logged in user id, You can see the videos in all the pages
     *
     * @created Vithya R 
     *
     * @updated Vithya R 
     *
     * @param object $request - sub profile id, video id
     * 
     * @return spam video details
     */
    public function spam_videos_remove(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                            'sub_profile_id'=>'required|exists:sub_profiles,id',
                        ], 
                        [
                            'exists' => 'The :attribute doesn\'t exists',
                        ]);
        
            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            }

            if($request->clear_all_status == YES) {

                $spam_videos = Flag::where('user_id', $request->id)->where('sub_profile_id', $request->sub_profile_id)->delete();

                $response_array = ['success' => true, 'message' => tr('unmark_report_video_success_msg')];

            } else {

                $admin_video_details = AdminVideo::where('id', $request->admin_video_id)->first();
                

                if(!$admin_video_details) {

                    throw new Exception(Helper::get_error_message(157), 157);
                    
                }
            
                $spam_video_details = Flag::where('user_id', $request->id)
                                        ->where('sub_profile_id', $request->sub_profile_id)
                                        ->where('video_id', $request->admin_video_id)
                                        ->first();

                if (!$spam_video_details) {

                    throw new Exception(tr('spam_not_found'), 101);   

                }                 

                $spam_video_details->delete();

                $response_array = ['success' => true, 'message' => tr('unmark_report_video_success_msg')];

                DB::commit();

            }

            return response()->json($response_array, 200);


        } catch (Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);
        }
    }

    /**
     * @method register()
     * 
     * @uses Register a new user 
     *
     * @created Anjana H
     * 
     * @edited: Anjana H
     *
     * @param object $request - New User Details
     * 
     * @return Json Response with user details
     *
     */
    public function register(Request $request) {
        
        try {

            DB::beginTransaction();

            $basicValidator = Validator::make(
                $request->all(),
                array(
                    'device_type' => 'required|in:'.DEVICE_ANDROID.','.DEVICE_IOS.','.DEVICE_WEB,
                    'device_token' => 'required',
                    'login_by' => 'required|in:manual,facebook,google',
                )
            );

            if($basicValidator->fails()) {

                $error_messages = implode(',', $basicValidator->messages()->all());

                throw new Exception($error_messages, 101);

            } else {

                $allowedSocialLogin = ['facebook','google'];

                $new_user_send_email = YES;

                if (in_array($request->login_by,$allowedSocialLogin)) {

                    // validate social registration fields

                    $socialValidator = Validator::make(
                                $request->all(),
                                array(
                                    'social_unique_id' => 'required',
                                    'name' => 'required|min:2|max:100',
                                    'email' => 'required|email|max:255',
                                    'mobile' => 'digits_between:4,16',
                                    'picture' => "",
                                )
                            );

                    if ($socialValidator->fails()) {

                        $error_messages = implode(',', $socialValidator->messages()->all());
                        throw new Exception($error_messages, 101);
                    }

                    $user = User::where('email' , $request->email)->first();

                    if ($user) {

                        $new_user_send_email = NO;
                    }

                } else {

                    // Validate manual registration fields

                    $manualValidator = Validator::make(
                        $request->all(),
                        array(
                            'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                            'email' => 'required|email|max:255',
                            'password' => 'required|min:6',
                            'mobile' => 'digits_between:4,16',
                            'picture' => 'mimes:jpeg,bmp,png',
                        )
                    );

                    if($manualValidator->fails()) {

                        $error_messages = implode(',', $manualValidator->messages()->all());

                        throw new Exception($error_messages, 101);                        
                    } 

                    // validate email existence

                    $emailValidator = Validator::make(
                        $request->all(),
                        array(
                            'email' => 'unique:users,email',
                        )
                    );

                    if($emailValidator->fails()) {

                        $error_messages = implode(',', $emailValidator->messages()->all());

                        throw new Exception($error_messages, 101);
                    }

                    $user = "";
                }

                // Creating the user

                if($new_user_send_email) {

                    $user = new User;

                    register_mobile($request->device_type);

                } else {

                    if ($user->is_activated == USER_DECLINED) {

                        throw new Exception(Helper::get_error_message(905),905);
                    
                    }

                    $sub_profile = SubProfile::where('user_id', $user->id)->first();

                    if (!$sub_profile) {

                        $new_user_send_email = YES;

                    }

                }


                $user->name = $request->name;

                $user->email = $request->email;

                if($request->has('mobile')) {

                    $user->mobile = $request->mobile;

                }

                $user->password = Hash::make($request->password);

                $user->gender = $request->has('gender') ? $request->gender : "male";

                $user->token = Helper::generate_token();

                $user->token_expiry = Helper::generate_token_expiry();

                $check_device_exist = User::where('device_token', $request->device_token)->first();

                if($check_device_exist){

                    $check_device_exist->device_token = "";

                    $check_device_exist->save();
                }

                $user->device_token = $request->has('device_token') ? $request->device_token : "";

                $user->device_type =$request->device_type;

                $user->login_by = $request->login_by;

                $user->social_unique_id = $request->has('social_unique_id') ? $request->social_unique_id : '';

                $user->picture = asset('placeholder.png');

                // Upload Picture

                $user->is_verified = USER_EMAIL_VERIFIED;

                $user->is_activated = $user->no_of_account = $user->logged_in_account = $user->status = 1;

                if($request->login_by == MANUAL) {

                    if($request->hasFile('picture')) {

                        $user->picture = Helper::normal_upload_picture($request->file('picture'));

                    }

                } else {

                    if($request->has('picture')) {

                        $user->picture = $request->picture;

                    }

                }

                if(Setting::get('email_verify_control')) {

                    $user->status = DEFAULT_FALSE;

                    if ($request->login_by == 'manual') {

                        $user->is_verified = USER_EMAIL_NOT_VERIFIED;

                    }

                } 

                if ($user->save()) {

                    // Send welcome email to the new user:
                    
                    if($new_user_send_email == YES) {

                        // Check the default subscription and save the user type 

                        $user->user_type = user_type_check();

                        $user->user_type_change_by = $user->user_type ? "" : "USER CHECK";

                        if ($user->login_by == MANUAL) {

                            $user->password = $request->password;

                            $email_data = [];

                            $email_data['user_id'] = $user->id;

                            $email_data['verification_code'] = $user->verification_code;

                            $email_data['template_type'] = USER_WELCOME;

                            $page = "emails.welcome";

                            $email = $user->email;

                            if(Setting::get('email_notification') == YES) {

                                Helper::send_email($page,$subject = null,$email,$email_data);

                            } else {
                                Log::info("Email notification off for user");
                            }

                        }

                        $sub_profile = new SubProfile;

                        $sub_profile->user_id = $user->id;

                        $sub_profile->name = $user->name;

                        $sub_profile->picture = $user->picture;

                        $sub_profile->status = DEFAULT_TRUE;

                        if ($sub_profile->save()) {

                            // Response with registered user details:

                            if (!Setting::get('email_verify_control')) {

                                $logged_device = new UserLoggedDevice();

                                $logged_device->user_id = $user->id;

                                $logged_device->token_expiry = Helper::generate_token_expiry();

                                $logged_device->status = DEFAULT_TRUE;

                                $logged_device->save();

                            }
                            

                        } else {

                            throw new Exception(tr('sub_profile_not_save'),101);
                            
                        }
                    }

                    $moderator = Moderator::where('email', $user->email)->first();

                    // If the user already registered as moderator, automatically the status will update.

                    if($moderator && $user) {

                        $user->is_moderator = DEFAULT_TRUE;

                        $user->moderator_id = $moderator->id;

                        $user->save();

                        $moderator->is_activated = DEFAULT_TRUE;

                        $moderator->is_user = DEFAULT_TRUE;

                        $moderator->save();

                    }

                    if ($user->is_verified) {

                        $user_details = User::where('id' , $user->id)->CommonResponse()->first();

                        $data = $user_details->toArray();

                        $data['card_last_four_number'] = "";

                        $data['sub_profile_id'] = $sub_profile->id;

                        $data['payment_subscription'] = $data['appstore_update_status'] = (int) Setting::get('ios_payment_subscription_status');

                        $email_verify_control = Setting::get('email_verify_control');

                        $data['verification_control'] = $email_verify_control;

                        $message = $email_verify_control && !$user_details->is_verified ? tr('register_verify_success') : tr('register_success');
                        
                        $response_array = ['success' => true, 'message'=> $message ,'data' => $data];                       

                    } else {

                        $response_array = ['success' => false, 'error_messages' => Helper::get_error_message(3001), 'error_code' => 3001];

                    }

                }

            }

            DB::commit();

            $response = response()->json($response_array, 200);

            return $response;

        } catch(Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$error, 'error_code'=>$code];

            return response()->json($response_array);
        }
    
    }

        /**
     * @method login()
     *
     * @uses Registered user can login using their email & Password
     * 
     * @created Vithya R
     * 
     * @updated vithya R
     *
     * @param object $request - User Email & Password
     *
     * @return Json response with user details
     */
    public function login(Request $request) {

        try {

            DB::beginTransaction();
            
            $basicValidator = Validator::make($request->all(), 
                array(

                    'device_token' => 'required',
                    'device_type' => 'required|in:'.DEVICE_ANDROID.','.DEVICE_IOS.','.DEVICE_WEB,
                    'login_by' => 'required|in:manual,facebook,google',
                )
            );
           
            if($basicValidator->fails()){
                
                $error_messages = implode(',',$basicValidator->messages()->all());

                throw new Exception($error_messages, 101);
            
            } else {

                /*validate manual login fields*/

                $manualValidator = Validator::make($request->all(),
                    array(
                        'email' => 'required|email',
                        'password' => 'required',

                    )
                );

                if ($manualValidator->fails()) {

                    $error_messages = implode(',',$manualValidator->messages()->all());

                    throw new Exception($error_messages, 101);
                
                }

                /*validate manual login fields*/

                $emailValidator = Validator::make($request->all(),
                    array(
                        'email' => 'exists:users,email',
                    )
                );

                if ($emailValidator->fails()) {

                    $error_messages = implode(',',$emailValidator->messages()->all());

                    throw new Exception($error_messages, 101);
                
                }

                $user = User::where('email', '=', $request->email)->first();
                
                if(!$user->is_activated) {

                    throw new Exception(Helper::get_error_message(905));

                }

                if (!$user->is_verified) {

                    if (Setting::get('email_verify_control')) {

                        Helper::check_email_verification("" , $user->id, $error);

                        throw new Exception(Helper::get_error_message(3001), 3001);

                    } else {

                        $user->is_verified = USER_EMAIL_VERIFIED;

                    }

                }

                if(Hash::check($request->password, $user->password)){


                } else {

                    throw new Exception(Helper::get_error_message(105), 105);
                    
                }

                $sub_profile = SubProfile::where('user_id', $user->id)->first();

                if ($sub_profile) {

                    $sub_profile_id = $sub_profile->id;

                } else {

                    $sub_profile = new SubProfile;

                    $sub_profile->user_id = $user->id;

                    $sub_profile->name = $user->name;

                    $sub_profile->status = DEFAULT_TRUE;

                    $sub_profile->picture = $user->picture;

                    if ($sub_profile->save()) {

                        $sub_profile_id = $sub_profile->id;

                        $user->no_of_account += 1;

                        $user->save();

                    } else {

                        throw new Exception(tr('sub_profile_not_save'));
                        
                    }
                }

                if ($user->email != DEMO_USER) {

                    if ($user->no_of_account >= $user->logged_in_account) {

                        $model = UserLoggedDevice::where("user_id",$user->id)->get();

                        foreach ($model as $key => $value) {

                            if ($value->token_expiry > time()) {


                            } else {

                               if ($value->delete()) {

                                    $user->logged_in_account -= 1;

                                    $user->save();

                                }

                            }

                        }
                    }

                } else {

                    $user->logged_in_account = 0;

                    $user->save();

                }

                 
                $user->token_expiry = Helper::generate_token_expiry();

                // Save device details

                $user->device_token = $request->device_token;

                $user->device_type = $request->device_type;

                $user->login_by = $request->login_by;

                if ($user->save()) {

                    $payment_mode_status = $user->payment_mode ? $user->payment_mode : 0;

                    $logged_device = new UserLoggedDevice();

                    $logged_device->user_id = $user->id;

                    $logged_device->token_expiry = Helper::generate_token_expiry();

                    $logged_device->status = DEFAULT_TRUE;

                    $logged_device->save();

                    $user->logged_in_account += 1;

                    $user->save();

                    $user_details = User::where('id' , $user->id)->CommonResponse()->first();

                    $data = $user_details->toArray();

                    $data['card_last_four_number'] = "";

                    $data['sub_profile_id'] = $sub_profile_id;

                    $data['payment_subscription'] = $data['appstore_update_status'] = (int) Setting::get('ios_payment_subscription_status');

                    $email_verify_control = Setting::get('email_verify_control');

                    $data['verification_control'] = $email_verify_control;

                    $message = tr('login_success');
                    
                    $response_array = ['success' => true, 'message'=> $message ,'data' => $data];

                } else {

                    throw new Exception(tr('no_of_logged_in_device'));
                    
                }
                   
               
            }
           
            DB::commit();

            // $response = response()->json($response_array, 200, [],JSON_NUMERIC_CHECK);

            return response()->json($response_array, 200);

        } catch(Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return response()->json($response_array);

        }
    
    }


    /**
     * @method user_details()
     * 
     * @uses get the user details 
     *
     * @created Anjana H
     * 
     * @updated Anjana H
     *
     * @param 
     * 
     * @return JSON Response
     *
     */
    public function profile(Request $request) {
        
        try {
            
            $user_details = User::where('id' , $request->id)->CommonResponse()->first();

            if (!$user_details) { 

                throw new Exception(Helper::get_error_message(3000), 3000);
            }

            // Sub profile details

            $sub_profile_details = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_SUB_PROFILE)->first();

            $sub_profile_id =  $sub_profile_details ? $sub_profile_details->id : 0 ;

            $card_details = Card::find($user_details->card_id);

            $card_last_four_number = $card_details ? $card_details->last_four : "";

            $data = $user_details->toArray();

            $data['card_last_four_number'] = $card_last_four_number;

            $data['sub_profile_id'] = $sub_profile_id;
            
            $response_array = ['success' => true, 'data' => $data];

            return response()->json($response_array, 200);

        } catch(Exception $e) {

            $error = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages '=> $error, 'error_code' => $error_code];

            return response()->json($response_array);
        }
    }


    /**
     * @method profile_update()
     *
     * @uses To update the user details
     *
     * @created Anjana H
     * 
     * @updated Vidhya R
     *
     * @param objecct $request
     *
     * @return JSON Response
     */
    public function profile_update(Request $request) {

        try {

            DB::beginTransaction();
            
            $validator = Validator::make(
                $request->all(),
                array(
                    'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                    'email' => 'email|unique:users,email,'.$request->id.'|max:255',
                    'mobile' => 'digits_between:4,16',
                    'picture' => 'mimes:jpeg,bmp,png',
                    'device_token' => '',
                ));

            if ($validator->fails()) {

                $error = implode(',',$validator->messages()->all());

                throw new Exception($error, 101);                
            } 

            $user_details = User::find($request->id);

            if (!$user_details) { 

                throw new Exception(Helper::get_error_message(3000), 3000);
            }
            
            $user_details->name = $request->name ?: $user_details->name;
            
            if($request->has('email')) {

                $user_details->email = $request->email;
            }

            $user_details->mobile = $request->mobile ?: $user_details->mobile;

            $user_details->gender = $request->gender ?: $user_details->gender;

            $user_details->address = $request->address ?: $user_details->address;

            $user_details->description = $request->description ? $request->description : $user_details->address;

            // Upload picture

            if ($request->hasFile('picture') != "") {

                Helper::delete_picture($user_details->picture, "/uploads/images/"); // Delete the old pic

                $user_details->picture = Helper::normal_upload_picture($request->file('picture'), '/uploads/images/');
            }

            if ($user_details->save()) {

                // Sub profile details

                $sub_profile_details = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_SUB_PROFILE)->first();

                $sub_profile_id =  $sub_profile_details ? $sub_profile_details->id : 0 ;

                // Card details

                $card_details = Card::find($user_details->card_id);

                $card_last_four_number = $card_details ? $card_details->last_four : "";

                $data = User::CommonResponse()->find($user_details->id);

                $data = $data->toArray();

                $data['card_last_four_number'] = $card_last_four_number;

                $data['sub_profile_id'] = $sub_profile_id;

                $response_array = ['success' => true ,  'message' => Helper::get_message(130), 'data' => $data];

            } else {

                throw new Exception(Helper::get_error_message(170), 170);
            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error, 'error_code' => $error_code];

            return response()->json($response_array);
        }
    
    }

    /**
     * @method history_index()
     *  
     * @uses To get all the history details based on logged in user id
     *
     * @created Anjana H
     * 
     * @updated Vithya R
     *
     * @param object $request - User Profile details
     *
     * @return Response with list of details
     */     
    public function history_index(Request $request) {

        try {

            $validator = Validator::make($request->all(),['skip' => 'required|numeric']);

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages);                
            } 

            $sub_profile_id = $request->sub_profile_id;

            $histories = VideoHelper::history_videos($request);

            // foreach ($histories as $key => $history_details) {

            //     $history_details->is_spam = check_flag_video($history_details->admin_video_id, $sub_profile_id)

            // }

            $response_array = ['success' => true, 'data' => $histories , 'total' => count($histories)];

            return response()->json($response_array, 200);

        } catch (Exception $e) {


            $error = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error, 'error_code' => $code];

            return response()->json($response_array);
        }   
    
    }

        /**
     * @method history_delete()
     *
     * @uses To delete history based on login id
     *
     * @created Vithya R
     * 
     * @updated vithya R
     *
     * @param Object $request - History Id
     *
     * @return Json object based on history
     */
    public function history_delete(Request $request) {

        try {

            DB::beginTransaction();
            
            $validator = Validator::make($request->all(),
                [
                    'admin_video_id' => $request->clear_all_status ? 'integer|exists:admin_videos,id' : 'required|integer|exists:admin_videos,id',
                    'sub_profile_id' => 'required|integer|exists:sub_profiles,id',
                ],[
                    'exists' => 'The :attribute doesn\'t exists please add to history',
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);                
            }

        
            if($request->clear_all_status == YES) {

                $history = UserHistory::where('user_id', $request->sub_profile_id)->delete();

            } else {

                $history = UserHistory::where('admin_video_id' ,  $request->admin_video_id)
                                ->where('user_id', $request->sub_profile_id)
                                ->delete();
            }

            $response_array = ['success' => true, 'message' => tr('delete_history_success')];

            DB::commit();

            return response()->json($response_array, 200);

        } catch(Exception $e) {

            DB::rollback();

            $response_array = ['success' => false, 'error_messages' => $e->getMessage(), 'error_code' => $e->getCode()];

            return response()->json($response_array, 200);

        }
    
    }

    /**
     * @method videos_like()
     * 
     * @uses Like videos in each single video based on logged in user id
     *
     * @created Anjana H
     *
     * @updated Anjana H 
     *
     * @param object $request - video id & sub profile id
     * 
     * @return resposne of success/failure message with count of like and dislike
     */
    public function videos_like(Request $request) {

        try {

            $validator = Validator::make($request->all() , [
                'admin_video_id' => 'required|exists:admin_videos,id,status,'.VIDEO_PUBLISHED.',is_approved,'.VIDEO_APPROVED,
                'sub_profile_id'=>'required|exists:sub_profiles,id',
                ], array(
                    'exists' => 'The :attribute doesn\'t exists',
                ));

            if ($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);          
            }

            $like_dislike_video_details = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                            ->where('user_id',$request->id)
                            ->where('sub_profile_id',$request->sub_profile_id)
                            ->first();

            $like_count = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                            ->where('like_status', DEFAULT_TRUE)
                            ->count();

            $dislike_count = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                            ->where('dislike_status', DEFAULT_TRUE)
                            ->count();

            if (!$like_dislike_video_details) {

                $like_dislike_video_details = new LikeDislikeVideo;

                $like_dislike_video_details->admin_video_id = $request->admin_video_id;

                $like_dislike_video_details->user_id = $request->id;

                $like_dislike_video_details->sub_profile_id = $request->sub_profile_id;

                $like_dislike_video_details->like_status = DEFAULT_TRUE;

                $like_dislike_video_details->dislike_status = DEFAULT_FALSE;

                if( $like_dislike_video_details->save() ) {

                    $data = new \stdClass;

                    $data->like_count = $like_count+1;

                    $data->dislike_count = $dislike_count;

                    $data->delete = DEFAULT_FALSE;
                        
                    $response_array = ['success' => true, 'data' => $data];

                } else {

                    throw new Exception(tr('something_error'), 101);
                }


            } else {

                if( $like_dislike_video_details->dislike_status ) {

                    $like_dislike_video_details->like_status = DEFAULT_TRUE;

                    $like_dislike_video_details->dislike_status = DEFAULT_FALSE;

                    if( $like_dislike_video_details->save() ) {

                        $data = new \stdClass;

                        $data->like_count = $like_count+1;

                        $data->dislike_count = $dislike_count-1;
                            
                        $response_array = ['success' => true, 'data' => $data];


                    } else {

                        throw new Exception(tr('something_error'), 101);
                    }

                } else {

                    if( $like_dislike_video_details->delete()) {

                        $data = new \stdClass;

                        $data->like_count = $like_count-1;

                        $data->dislike_count = $dislike_count;

                        $data->delete = DEFAULT_TRUE;
                            
                        $response_array = ['success' => true, 'data' => $data];
                   
                    } else {

                        throw new Exception(tr('something_error'), 101);
                    }
                }
            }

            DB::commit();

            return response()->json($response_array, 200);  

        } catch (Exception $e) {
            
            DB::rollback();

            $message = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $message, 'error_code' => $error_code];

            return response()->json($response_array);
        }

    }

    /**
     * @method videos_dislike()
     * 
     * @uses DisLike videos in each single video based on logged in user id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param object $request - video id & sub profile id
     * 
     * @return resposne of success/failure message with count of like and dislike
     */
    public function videos_dislike(Request $request) {

        try {

            $validator = Validator::make($request->all() , [
                'admin_video_id' => 'required|exists:admin_videos,id,status,'.VIDEO_PUBLISHED.',is_approved,'.VIDEO_APPROVED,
                'sub_profile_id'=>'required|exists:sub_profiles,id',
                ], array(
                    'exists' => 'The :attribute doesn\'t exists',
                ));

            if ($validator->fails()) {

                $error = implode(',', $validator->messages()->all());
                
                throw new Exception($error, 101);
            }

            $like_dislike_video_details = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                    ->where('user_id',$request->id)
                    ->where('sub_profile_id',$request->sub_profile_id)
                    ->first();
           
            $like_count = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                ->where('like_status', DEFAULT_TRUE)
                ->count();

            $dislike_count = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                ->where('dislike_status', DEFAULT_TRUE)
                ->count();

            if (!$like_dislike_video_details) {

                $like_dislike_video_details = new LikeDislikeVideo;

                $like_dislike_video_details->admin_video_id = $request->admin_video_id;

                $like_dislike_video_details->user_id = $request->id;

                $like_dislike_video_details->sub_profile_id = $request->sub_profile_id;

                $like_dislike_video_details->like_status = DEFAULT_FALSE;

                $like_dislike_video_details->dislike_status = DEFAULT_TRUE;
                
                if( $like_dislike_video_details->save() ) {

                    $data = new \stdClass;

                    $data->like_count = $like_count;

                    $data->dislike_count = $dislike_count+1;

                    $data->delete = DEFAULT_FALSE;
                        
                    $response_array = ['success' => true, 'data' => $data];
                        
                } else {

                    throw new Exception(tr('something_error'), 101);
                }

            } else {

                if($like_dislike_video_details->like_status) {

                    $like_dislike_video_details->like_status = DEFAULT_FALSE;

                    $like_dislike_video_details->dislike_status = DEFAULT_TRUE;

                    if( $like_dislike_video_details->save() ) {
                        
                        $data = new \stdClass;

                        $data->like_count = $like_count-1;

                        $data->dislike_count = $dislike_count+1;
                            
                        $response_array = ['success' => true, 'data' => $data];


                    } else {

                        throw new Exception(tr('something_error'), 101);
                    }

                } else {

                    if( $like_dislike_video_details->delete() ) {

                        $data = new \stdClass;

                        $data->like_count = $like_count;

                        $data->dislike_count = $dislike_count-1;

                        $data->delete = DEFAULT_TRUE;
                            
                        $response_array = ['success' => true, 'data' => $data];                          

                    } else {

                        throw new Exception(tr('something_error'), 101);
                    }
                }
            }

            DB::commit();

            return response()->json($response_array, 200); 
            
        } catch (Exception $e) {
            
            DB::rollback();

            $message = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $message, 'error_code' => $error_code];

            return response()->json($response_array);
        }

    }

    /**
     * @method categories_list()
     * 
     * @uses Get categories and split into chunks (6)
     *
     * @created Vidhya R
     *
     * @updated 
     *
     * @param object $request - As of now no attribute
     *
     * @return array of array category
     */
    
    public function categories_list(Request $request) {

        $skip = $request->skip ?: 0;

        $take = $request->take ?: (Setting::get('admin_take_count') ?: 12);
        
        $categories = Category::where('categories.is_approved' , 1)
                    ->select('categories.id as category_id' , 'categories.name' , 'categories.picture' ,
                        'categories.is_series' ,'categories.status' , 'categories.is_approved')
                    ->leftJoin('admin_videos' , 'categories.id' , '=' , 'admin_videos.category_id')
                    ->where('admin_videos.status' , 1)
                    ->where('admin_videos.is_approved' , 1)
                    ->groupBy('admin_videos.category_id')
                    ->havingRaw("COUNT(admin_videos.id) > 0")
                    ->orderBy('name' , 'ASC')
                    ->skip($skip)
                    ->take($take)
                    ->get();

        $response_array = ['success' => true, 'data' => $categories];

        return response()->json($response_array, 200);
   
    }

    /**
     * @method cast_crews_videos()
     *
     * @uses To load videos based on cast & crews
     *
     * @created Vithya R
     *
     * @updated
     *
     * @param object $request - user & crews details
     *
     * @return response of json details
     */
   public function cast_crews_videos(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                    'skip' => 'required|numeric',
                    'cast_crew_id'=>'required|exists:cast_crews,id'
                ],
                [

                    'cast_crew_id.exists'=>tr('cast_crew_not_found')

                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            }
            
            $cast_crews_videos = VideoHelper::cast_crews_videos($request);
            
            $response_array = ['success' => true, 'data' => $cast_crews_videos , 'total' => count($cast_crews_videos)];

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return response()->json($response_array);

        }

   }
    
    /**
     * @method subscriptions_payment()
     *
     * @uses subscription payment based on the payment mode
     *
     * @created Vithya R
     * 
     * @updated vithya R
     *
     * @param Object $request - History Id
     *
     * @return Json object based on history
     */
    public function subscriptions_payment(Request $request) {

        try {

            DB::beginTransaction();
            
            $validator = Validator::make($request->all(),
                [
                    'subscription_id' => 'required|integer|exists:subscriptions,id,status,'.APPROVED,
                    'coupon_code'=>'exists:coupons,coupon_code',
                    'payment_mode' => 'required|in:'.PAYPAL.','.CARD,
                    'payment_id' => 'required_if:payment_mode,'.PAYPAL
                ],
                [
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'subscription_id.exists' => tr('subscription_not_exists'),
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);                
            }

            $subscription_details = Subscription::find($request->subscription_id);

            $user_details = User::find($request->id);

            if(!$subscription_details) {

                throw new Exception(Helper::get_error_message(154), 154);
            }

            // Initial detault values

            $total = $subscription_details->amount; 

            $coupon_amount = 0.00;
           
            $coupon_reason = ""; 

            $is_coupon_applied = COUPON_NOT_APPLIED;

            // Check the coupon code

            if($request->coupon_code) {
                
                $coupon_code_response = PaymentRepo::check_coupon_code($request, $user_details, $subscription_details->amount);

                $coupon_amount = $coupon_code_response['coupon_amount'];

                $coupon_reason = $coupon_code_response['coupon_reason'];

                $is_coupon_applied = $coupon_code_response['is_coupon_applied'];

                $total = $coupon_code_response['total'];

            }

            // Update the coupon details and total to the request

            $request->coupon_amount = $coupon_amount ?: 0.00;

            $request->coupon_reason = $coupon_reason ?: "";

            $request->is_coupon_applied = $is_coupon_applied;

            $request->total = $total ?: 0.00;

            // If total greater than zero, do the stripe payment

            if($request->total > 0 && $request->payment_mode == CARD) {

                // Check the card details

                $check_card_exists = User::where('users.id' , $request->id)->leftJoin('cards' , 'users.id','=','cards.user_id')->where('cards.id' , $user_details->card_id)->where('cards.is_default' , DEFAULT_TRUE);

                if($check_card_exists->count() == 0) {
                        
                    throw new Exception(Helper::get_error_message(901), 901);

                }

                $user_card_details = $check_card_exists->first();

                $stripe_secret_key = Setting::get('stripe_secret_key');


                if($stripe_secret_key) {

                    \Stripe\Stripe::setApiKey($stripe_secret_key);

                } else {

                    throw new Exception(Helper::get_error_message(902), 902);

                }

                try {

                    $customer_id = $user_card_details->customer_id;

                    $total = number_format((float)$request->total, 2, '.', '');

                    $payment_data = [
                        "amount" => $total * 100,
                        "currency" => Setting::get('currency_code', 'USD'),
                        "customer" => $customer_id,
                    ];

                    $stripe_subscription_payment =  \Stripe\Charge::create($payment_data);

                    $request->payment_id = $stripe_subscription_payment->id;

                    $request->total = $stripe_subscription_payment->amount/100;

                    $paid_status = $stripe_subscription_payment->paid;

                    if($paid_status) {

                        // No need

                    }

                // }  catch(\Stripe\Error\RateLimit | \Stripe\Error\Card | \Stripe\Error\InvalidRequest | \Stripe\Error\Authentication | \Stripe\Error\ApiConnection | \Stripe\Error\Base | Exception $e) {

                } catch(Exception $e) {

                    $error_message = $e->getMessage(); $error_code = $e->getCode();

                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                    // Update the failure to payments table @todo

                    return response()->json($response_array);
                } 

            }

            $response_array = PaymentRepo::subscriptions_payment_save($request, $subscription_details, $user_details);

            DB::commit();

            return response()->json($response_array, 200);

        } catch(Exception $e) {

            DB::rollback();

            $response_array = ['success' => false, 'error_messages' => $e->getMessage(), 'error_code' => $e->getCode()];

            return response()->json($response_array, 200);

        }
    
    }

    /**
     * @method ppv_payment()
     *
     * @uses PPV payment based on the payment mode
     *
     * @created Vithya R
     * 
     * @updated vithya R
     *
     * @param Object $request - History Id
     *
     * @return Json object based on history
     */
    public function ppv_payment(Request $request) {

        try {

            DB::beginTransaction();
            
            $validator = Validator::make($request->all(),
                [
                    'admin_video_id' => 'required|integer|exists:admin_videos,id',
                    'coupon_code'=>'exists:coupons,coupon_code',
                    'payment_mode' => 'required|in:'.PAYPAL.','.CARD,
                    'payment_id' => 'required_if:payment_mode,'.PAYPAL
                ],
                [
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'admin_video_id.exists' => Helper::get_error_message(157),
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);                
            }

            $admin_video_details = AdminVideo::where('admin_videos.id', $request->admin_video_id)
                                        ->where('status', VIDEO_PUBLISHED)
                                        ->where('is_approved', VIDEO_APPROVED)
                                        ->first();

            if(!$admin_video_details) {

                throw new Exception(Helper::get_error_message(157), 157);
                
            }

            if($admin_video_details->is_pay_per_view == PPV_DISABLED) {

                throw new Exception(Helper::get_error_message(171), 171);
                
            }

            $user_details = User::find($request->id);

            if(!$user_details) {

                throw new Exception(Helper::get_error_message(154), 154);
            }

            // Initial detault values

            $total = $admin_video_details->amount; 

            $coupon_amount = 0.00; $coupon_reason = ""; $is_coupon_applied = COUPON_NOT_APPLIED;

            // Check the coupon code

            if($request->coupon_code) {
                
                $coupon_code_response = PaymentRepo::check_coupon_code($request, $user_details, $admin_video_details->amount);

                $coupon_amount = $coupon_code_response['coupon_amount'];

                $coupon_reason = $coupon_code_response['coupon_reason'];

                $is_coupon_applied = $coupon_code_response['is_coupon_applied'];

                $total = $coupon_code_response['total'];

            }

            // Update the coupon details and total to the request

            $request->coupon_amount = $coupon_amount ?: 0.00;

            $request->coupon_reason = $coupon_reason ?: "";

            $request->is_coupon_applied = $is_coupon_applied;

            $request->total = $total ?: 0.00;

            // If total greater than zero, do the stripe payment

            if($request->total > 0 && $request->payment_mode == CARD) {

                // Check the card details

                $check_card_exists = User::where('users.id' , $request->id)->leftJoin('cards' , 'users.id','=','cards.user_id')->where('cards.id' , $user_details->card_id)->where('cards.is_default' , DEFAULT_TRUE);

                if($check_card_exists->count() == 0) {
                        
                    throw new Exception(Helper::get_error_message(901), 901);

                }

                $user_card_details = $check_card_exists->first();

                $stripe_secret_key = Setting::get('stripe_secret_key');


                if($stripe_secret_key) {

                    \Stripe\Stripe::setApiKey($stripe_secret_key);

                } else {

                    throw new Exception(Helper::get_error_message(902), 902);

                }

                try {

                    $customer_id = $user_card_details->customer_id;

                    $total = number_format((float)$request->total, 2, '.', '');

                    $payment_data = [
                        "amount" => $total * 100,
                        "currency" => Setting::get('currency_code', 'USD'),
                        "customer" => $customer_id,
                    ];

                    $stripe_payment =  \Stripe\Charge::create($payment_data);

                    $request->payment_id = $stripe_payment->id;

                    $request->total = $stripe_payment->amount/100;

                    $paid_status = $stripe_payment->paid;

                    if($paid_status) {

                        // No need

                    }

                }  
                // catch(\Stripe\Error\RateLimit | \Stripe\Error\Card | \Stripe\Error\InvalidRequest | \Stripe\Error\Authentication | \Stripe\Error\ApiConnection | \Stripe\Error\Base | Exception $e) {

                // } 
                catch(Exception $e) {

                    $error_message = $e->getMessage(); $error_code = $e->getCode();

                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                    // Update the failure to payments table @todo

                    return response()->json($response_array);
                } 

            }

            $response_array = PaymentRepo::ppv_payment_save($request, $admin_video_details, $user_details);

            DB::commit();

            return response()->json($response_array, 200);

        } catch(Exception $e) {

            DB::rollback();

            $response_array = ['success' => false, 'error_messages' => $e->getMessage(), 'error_code' => $e->getCode()];

            return response()->json($response_array, 200);

        }
    
    }
}
