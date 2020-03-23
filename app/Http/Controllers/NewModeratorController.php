<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Jobs\StreamviewCompressVideo;

use App\Repositories\VideoRepository as VideoRepo;

use App\Helpers\Helper;

use Log;

use Auth;

use Validator;

use Hash;

use Mail;

use DB;

use Exception;

use Redirect;

use Setting;

use App\AdminVideo;

use App\AdminVideoImage;

use App\CastCrew;

use App\Category;

use App\Genre;

use App\Moderator;

use App\PayPerView;

use App\SubCategory;

use App\SubCategoryImage;

use App\Settings;

use App\VideoCastCrew;

use App\Redeem;

use App\RedeemRequest;

class NewModeratorController extends Controller
{
    protected $ModeratorAPI;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ModeratorApiController $api) {

        $this->ModeratorAPI = $api;

        $this->middleware('moderator');
    }

	/**
	 *
	 * Function Name: dahsboard()
	 *
	 * @uses show analytics for moderator
	 *
	 * @created vidhya R
	 *
	 * @updated vidhya R
	 *
	 * @param
	 *
	 * @return view page
	 */

    public function dashboard(Request $request) {

        $moderator_details = Moderator::find(\Auth::guard('moderator')->user()->id);

        $moderator_details->token = Helper::generate_token();

        $moderator_details->token_expiry = Helper::generate_token_expiry();

        $moderator_details->save();
        
        $today_videos = AdminVideo::where('uploaded_by', $moderator_details->id)->count();

        $total_revenue = $moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->total_moderator_amount : "0.00";

        $watch_count_revenue = redeem_amount($moderator_details->id);

        $ppv_revenue = total_moderator_video_revenue($moderator_details->id);

        return view('moderator.dashboard.dashboard')
                    ->with('page', 'dashboard')
                    ->with('sub_page','')
                    ->with('today_videos' , $today_videos)
                    ->with('total_revenue', $total_revenue)
                    ->with('watch_count_revenue', $watch_count_revenue)
                    ->with('ppv_revenue', $ppv_revenue);

    }

    /**
	 *
	 * Function Name: profile()
	 *
	 * @uses moderator profile page
	 *
	 * @created vidhya R
	 *
	 * @updated vidhya R
	 *
	 * @param
	 *
	 * @return view page
	 */

    public function profile() {

        $moderator_details = Auth::guard('moderator')->user();

        return view('moderator.account.profile')
                    ->with('page', 'profile')
                    ->with('sub_page','')
                    ->with('moderator_details', $moderator_details);

    }

    /**
     *
     * Function Name: profile()
     *
     * @uses Save any changes to the provider profile
     *
     * @created vidhya R
     *
     * @updated vidhya R
     *
     * @param
     *
     * @return view page
     */

    public function profile_save(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make( $request->all(),
                [
                    'name' => 'regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                    'email' => 'email|max:255',
                    'mobile' => 'digits_between:4,16',
                    'address' => 'max:300',
                    'picture' => 'mimes:jpeg,jpg,png'
                ]);

            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);
    
            }

            $moderator_details = Moderator::find($request->id);
            
            $moderator_details->name = $request->name ?: $moderator_details->name;

            $moderator_details->email = $request->email ?: $moderator_details->email;

            $moderator_details->mobile = $request->mobile ?: $moderator_details->mobile;

            $moderator_details->gender = $request->gender ?: $moderator_details->gender;

            $moderator_details->address = $request->address ?: $moderator_details->address;

            if($request->hasFile('picture')) {
                $moderator_details->picture = Helper::normal_upload_picture($request->file('picture'));
            }
                
            $moderator_details->remember_token = Helper::generate_token();

            $moderator_details->is_activated = 1;

            if($moderator_details->save()) {

                DB::commit();

                return back()->with('flash_success', tr('moderator_not_profile'));

            } else {

                return back()->with('flash_error', tr('moderator_profile_save_error'));
            }

        } catch(Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->withInput()->with('flash_error', $error);

        }
    
    }

    /**
     *
     * Function Name: change_password()
     *
     * @uses moderator update the password
     *
     * @created vidhya R
     *
     * @updated vidhya R
     *
     * @param
     *
     * @return view page
     */

    public function change_password(Request $request) {

        try {

            DB::beginTransaction();
        
            $validator = Validator::make($request->all(), [              
                'password' => 'required|min:6|confirmed',
                'old_password' => 'required',
                'id' => 'required|exists:moderators,id'
            ]);

            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);
    
            }

            $moderator_details = Moderator::find(Auth::guard('moderator')->user()->id);

            if(Hash::check($request->old_password,$moderator_details->password)) {

                $moderator_details->password = Hash::make($request->new_password);

                $moderator_details->save();

                DB::commit();

                return back()->with('flash_success', tr('moderator_password_change_success'));
                
            } else {

                throw new Exception(tr('moderator_password_mismatch'), 101);
                

            }
  
        } catch(Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->withInput()->with('flash_error', $error);

        }
       
    }

    /**
     * Function Name: admin_videos_index()
     *
     * @uses list of videos by loggedin moderator
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */

    public function admin_videos_index(Request $request) {

        $admin_videos = AdminVideo::leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
                    ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
                    ->leftJoin('genres' , 'admin_videos.genre_id' , '=' , 'genres.id')
                   ->select('admin_videos.id as video_id' ,'admin_videos.title' , 
                             'admin_videos.description' , 'admin_videos.ratings' , 
                             'admin_videos.reviews' , 'admin_videos.created_at as video_date' ,
                             'admin_videos.default_image',
                             'admin_videos.amount',
                             'admin_videos.user_amount',
                             'admin_videos.type_of_user',
                             'admin_videos.type_of_subscription',
                             'admin_videos.category_id as category_id',
                             'admin_videos.sub_category_id',
                             'admin_videos.genre_id',
                             'admin_videos.compress_status',
                             'admin_videos.trailer_compress_status',
                             'admin_videos.redeem_amount',
                             'admin_videos.watch_count',
                             'admin_videos.unique_id',
                             'admin_videos.status','admin_videos.uploaded_by',
                             'admin_videos.edited_by','admin_videos.is_approved',
                             'admin_videos.video_subtitle',
                             'admin_videos.trailer_subtitle',
                             'categories.name as category_name' , 'sub_categories.name as sub_category_name' ,
                             'genres.name as genre_name')
                    ->orderBy('admin_videos.created_at' , 'desc')
                    ->where('uploaded_by', Auth::guard('moderator')->user()->id)
                    ->get();

        return view('moderator.videos.index')
                    ->with('page', 'videos')
                    ->with('sub_page','videos-view')
                    ->with('admin_videos' , $admin_videos);
   
    }

    /**
     * Function Name: admin_videos_create()
     *
     * @uses video upload view page
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */
    public function admin_videos_create(Request $request) {

        $categories = Category::where('categories.is_approved' , DEFAULT_TRUE)
                ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                    'categories.is_series' ,'categories.status' , 'categories.is_approved')
                ->leftJoin('sub_categories' , 'categories.id' , '=' , 'sub_categories.category_id')
                ->groupBy('sub_categories.category_id')
                ->havingRaw("COUNT(sub_categories.id) > 0")
                ->orderBy('categories.name' , 'asc')
                ->get();

        $model = new AdminVideo;

        $model->trailer_video_resolutions = [];

        $model->video_resolutions = [];

        $video_cast_crews = [];

        $videoimages = [];

        $cast_crews = CastCrew::select('id', 'name')->get();

        return view('moderator.videos.upload')->with('page', 'videos')
            ->with('categories', $categories)
            ->with('sub_page', 'videos-create')
            ->with('model', $model)
            ->with('videoimages', $videoimages)
            ->with('cast_crews', $cast_crews)
            ->with('video_cast_crews',$video_cast_crews);
    }

    /**
     * Function Name : admin_videos_edit()
     *
     * To display a upload video form
     *
     * @created_by - Shobana Chandrasekar
     *
     * @updated_by - - 
     *
     * @param object $request - - 
     *
     * @return response of html page with details
     */

    /**
     * Function Name: admin_videos_edit()
     *
     * @uses video edit view page
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */
    public function admin_videos_edit(Request $request) {

        try {

            $admin_video_details = AdminVideo::where('admin_videos.id' , $request->id)->first();

            if (!$admin_video_details) {

                throw new Exception(tr('moderator_video_found'), 101);

            }

            $categories = Category::where('categories.is_approved' , DEFAULT_TRUE)
                                ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                                    'categories.is_series' ,'categories.status' , 'categories.is_approved')
                                ->leftJoin('sub_categories' , 'categories.id' , '=' , 'sub_categories.category_id')
                                ->groupBy('sub_categories.category_id')
                                ->havingRaw("COUNT(sub_categories.id) > 0")
                                ->orderBy('categories.name' , 'asc')
                                ->get();

            $sub_categories = get_sub_categories($admin_video_details->category_id);

            $admin_video_details->publish_time = $admin_video_details->publish_time ? date('d-m-Y H:i:s', strtotime($admin_video_details->publish_time)) : $admin_video_details->publish_time;

            $videoimages = get_video_image($admin_video_details->id);

            $admin_video_details->video_resolutions = $admin_video_details->video_resolutions ? explode(',', $admin_video_details->video_resolutions) : [];

            $admin_video_details->trailer_video_resolutions = $admin_video_details->trailer_video_resolutions ? explode(',', $admin_video_details->trailer_video_resolutions) : [];



            $video_cast_crews = VideoCastCrew::select('cast_crew_id')
                    ->where('admin_video_id', $request->id)
                    ->get()->pluck('cast_crew_id')->toArray();
         
            $cast_crews = CastCrew::select('id', 'name')->get();

            return view('moderator.videos.upload')
                        ->with('page', 'videos')
                        ->with('sub_page', 'videos-create')
                        ->with('cast_crews',$cast_crews)
                        ->with('video_cast_crews', $video_cast_crews)
                        ->with('categories', $categories)
                        ->with('sub_categories', $sub_categories)
                        ->with('model', $admin_video_details)
                        ->with('videoimages', $videoimages);

        } catch(Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            return back()->with('flash_error', $error_messages);
        
        }
    
    }

    /**
     * Function Name: admin_videos_save()
     *
     * @uses save/update the video details
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */
    public function admin_videos_save(Request $request) {

        try {

            // Call video save method of common function video repo

            $response = VideoRepo::video_save($request)->getData();

            return ['response' => $response ];

        } catch(Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages, 'error_code' => $error_code];

            return ['response' => $response_array ];
        }
    
    }

    /**
     * Function Name: admin_videos_view()
     *
     * @uses save/update the video PPV details
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */

    public function admin_videos_view(Request $request) {

        $validator = Validator::make($request->all() , [
                'id' => 'required|exists:admin_videos,id'
            ]);

        if($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            return back()->with('flash_error', $error_messages);
        } else {
            $videos = AdminVideo::where('admin_videos.id' , $request->id)
                    ->leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
                    ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
                    ->leftJoin('genres' , 'admin_videos.genre_id' , '=' , 'genres.id')
                    ->select('admin_videos.id as video_id' ,'admin_videos.title' , 
                             'admin_videos.description' , 'admin_videos.ratings' , 
                             'admin_videos.reviews' , 'admin_videos.created_at as video_date' ,
                             'admin_videos.video','admin_videos.trailer_video',
                             'admin_videos.default_image',
                             'admin_videos.redeem_amount',
                             'admin_videos.admin_amount',
                             'admin_videos.user_amount',
                             'admin_videos.type_of_user',
                             'admin_videos.type_of_subscription',
                             'admin_videos.watch_count',
                             'admin_videos.category_id as category_id',
                             'admin_videos.sub_category_id',
                             'admin_videos.genre_id',
                             'admin_videos.video_type',
                             'admin_videos.video_upload_type',
                             'admin_videos.duration',
                             'admin_videos.compress_status',
                             'admin_videos.trailer_compress_status',
                             'admin_videos.main_video_compress_status',
                             'admin_videos.video_resolutions',
                             'admin_videos.video_resize_path',
                             'admin_videos.trailer_resize_path',
                             'admin_videos.unique_id',
                             'admin_videos.video_subtitle',
                             'admin_videos.trailer_subtitle',
                             'admin_videos.trailer_video_resolutions',
                             'categories.name as category_name' , 'sub_categories.name as sub_category_name' ,
                             'genres.name as genre_name',
                             'admin_videos.trailer_duration',
                             'admin_videos.publish_time',
                             'admin_videos.video_gif_image',
                             'admin_videos.is_approved',
                             'admin_videos.status',
                             'admin_videos.is_pay_per_view',
                             'admin_videos.amount',
                             'admin_videos.age')
                    ->orderBy('admin_videos.created_at' , 'desc')
                    ->first();

            $videoPath = $video_pixels = $trailer_video_path = $trailer_pixels = $trailerstreamUrl = $videoStreamUrl = '';

        $ios_trailer_video = $videos->trailer_video;

        $ios_video = $videos->video;

        if ($videos->video_type == 1) {


            if(check_valid_url($videos->trailer_video)) {

                if(Setting::get('streaming_url'))
                    $trailerstreamUrl = Setting::get('streaming_url').get_video_end($videos->trailer_video);

                if(Setting::get('HLS_STREAMING_URL'))
                    $ios_trailer_video = Setting::get('HLS_STREAMING_URL').get_video_end($videos->trailer_video);
            }

            if(check_valid_url($videos->video)) {

                if(Setting::get('streaming_url'))
                    $videoStreamUrl = Setting::get('streaming_url').get_video_end($videos->video);

                if(Setting::get('HLS_STREAMING_URL'))
                    $ios_video = Setting::get('HLS_STREAMING_URL').get_video_end($videos->video);
            }

            if (\Setting::get('streaming_url')) {
                $trailerstreamUrl = \Setting::get('streaming_url').get_video_end($videos->trailer_video);
                $videoStreamUrl = \Setting::get('streaming_url').get_video_end($videos->video);
                if ($videos->is_approved == 1) {
                    if($videos->trailer_video_resolutions) {
                        $trailerstreamUrl = Helper::web_url().'/uploads/smil/'.get_video_end_smil($videos->trailer_video).'.smil';
                    } 
                    if ($videos->video_resolutions) {
                        $videoStreamUrl = Helper::web_url().'/uploads/smil/'.get_video_end_smil($videos->video).'.smil';
                    }
                }
            } else {

                $videoPath = $videos->video_resize_path ? $videos->video.','.$videos->video_resize_path : $videos->video;
                $video_pixels = $videos->video_resolutions ? 'original,'.$videos->video_resolutions : 'original';
                $trailer_video_path = $videos->trailer_resize_path ? $videos->trailer_video.','.$videos->trailer_resize_path : $videos->trailer_video;
                $trailer_pixels = $videos->trailer_video_resolutions ? 'original,'.$videos->trailer_video_resolutions : 'original';
            }

            $trailerstreamUrl = $trailerstreamUrl ? $trailerstreamUrl : "";
            $videoStreamUrl = $videoStreamUrl ? $videoStreamUrl : "";

        } else {
            $trailerstreamUrl = $videos->trailer_video;
            $videoStreamUrl = $videos->video;

            
             if($videos->video_type == VIDEO_TYPE_YOUTUBE) {

                $videoStreamUrl = $ios_video = get_youtube_embed_link($videos->video);

                $trailerstreamUrl =  $ios_trailer_video = get_youtube_embed_link($videos->trailer_video);
                

            }
        }

        $admin_video_images = AdminVideoImage::where('admin_video_id' , $request->id)
                                ->orderBy('is_default' , 'desc')
                                ->get();

        // Load Video Cast & crews

        $video_cast_crews = VideoCastCrew::select('cast_crew_id', 'name')
                    ->where('admin_video_id', $request->id)
                    ->leftjoin('cast_crews', 'cast_crews.id', '=', 'video_cast_crews.cast_crew_id')
                    ->get()->pluck('name')->toArray();



        return view('moderator.videos.view')->with('video' , $videos)
                    ->with('video_images' , $admin_video_images)
                    ->withPage('videos')
                    ->with('sub_page','videos-view')
                    ->with('videoPath', $videoPath)
                    ->with('video_pixels', $video_pixels)
                    ->with('trailer_video_path', $trailer_video_path)
                    ->with('trailer_pixels', $trailer_pixels)
                    ->with('ios_trailer_video', $ios_trailer_video)
                    ->with('ios_video', $ios_video)
                    ->with('videoStreamUrl', $videoStreamUrl)
                    ->with('trailerstreamUrl', $trailerstreamUrl)
                    ->with('video_cast_crews',$video_cast_crews);
        }
    
    }  

     /**
     * Function Name: admin_videos_ppv_add()
     *
     * @uses save/update the video PPV details
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */  
    public function admin_videos_ppv_add(Request $request) {

        try {

            DB::beginTransaction();

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if(!$admin_video_details) {

                throw new Exception(tr('moderator_video_found'), 101);
                
            }

            if($request->amount <= 0) {

                throw new Exception(tr('add_ppv_amount'), 101);
                
            }

            $admin_video_details->type_of_subscription = $request->type_of_subscription ?: $admin_video_details->type_of_subscription;
            $admin_video_details->type_of_user = $request->type_of_user ?: $admin_video_details->type_of_user;

            $admin_video_details->amount = $request->amount ?: $admin_video_details->amount;

            $admin_video_details->is_pay_per_view = PPV_ENABLED;

            if($admin_video_details->save()) {

                DB::commit();

                return back()->with('flash_success', tr('payment_added'));

            } else {

                throw new Exception(tr('moderator_video_ppv_add_failed'), 101);
                
            }                

        } catch(Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            return back()->with('flash_error', $error_messages);
        }
    
    }
  
    /**
     * Function Name: admin_videos_ppv_remove()
     *
     * @uses remove the video PPV option
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */
    public function admin_videos_ppv_remove(Request $request) {

        try {

            DB::beginTransaction();

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if(!$admin_video_details) {

                throw new Exception(tr('moderator_video_found'), 101);
                
            }

            $admin_video_details->type_of_subscription = $admin_video_details->type_of_user = $admin_video_details->amount = 0;

            $admin_video_details->is_pay_per_view = PPV_DISABLED;

            if($admin_video_details->save()) {

                DB::commit();

                return back()->with('flash_success', tr('removed_pay_per_view'));

            } else {

                throw new Exception(tr('moderator_video_ppv_add_failed'), 101);
                
            }

        } catch(Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            return back()->with('flash_error', $error_messages);
        }
    
    }

    /**
     * Function Name: revenues()
     *
     * @uses list the redeem details
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */
    public function revenues(Request $request) {

        try {

            $moderator_details = Auth::guard('moderator')->user();

            if(!$moderator_details) {

                throw new Exception(tr('moderator_video_found'));

            }

            $redeem_details = Redeem::where('moderator_id', $moderator_details->id)->first();

            if(!$redeem_details) {

                $redeem_details = New Redeem;

                $redeem_details->moderator_id = $moderator_details->id;

                $redeem_details->total = 0;

                $redeem_details->save();
            }
                
            $payments = AdminVideo::where('admin_videos.uploaded_by', $moderator_details->id)->get();


            $total_revenue = $moderator_details->moderatorRedeem ? $moderator_details->moderatorRedeem->total_moderator_amount : "0.00";

            $watch_count_revenue = redeem_amount($moderator_details->id);

            $ppv_revenue = total_moderator_video_revenue($moderator_details->id);
           
            return view('moderator.payments.revenues')
                        ->with('page', 'revenues')
                        ->with('sub_page','revenues-dashboard')
                        ->with('redeem_details' , $redeem_details)
                        ->with('payments' , $payments)
                        ->with('total_revenue' , $total_revenue)
                        ->with('watch_count_revenue' , $watch_count_revenue)
                        ->with('ppv_revenue' , $ppv_revenue);

        } catch(Exception $e) {

            $error_messages = $e->getMessage();

            return back()->with('flash_error', $error_messages);
        }
    }

    /**
     * Function Name: redeems()
     *
     * @uses list the redeem details
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */

    public function redeems(Request $request) {

        try {

            $moderator_details = Auth::guard('moderator')->user();

            if(!$moderator_details) {

                throw new Exception(tr('moderator_video_found'));

            }

            $redeem_details = Redeem::where('moderator_id', $moderator_details->id)->first();

            if(!$redeem_details) {

                $redeem_details = New Redeem;

                $redeem_details->moderator_id = $moderator_details->id;

                $redeem_details->total = 0;

                $redeem_details->save();
            }

            $redeem_requests = RedeemRequest::where('moderator_id', $moderator_details->id)->paginate(10);

            return view('moderator.payments.redeems')
                        ->with('page', 'revenues')
                        ->with('sub_page', 'revenues-redeems')
                        ->with('redeem_details', $redeem_details)
                        ->with('redeem_requests', $redeem_requests);

        } catch(Exception $e) {

            $error_messages = $e->getMessage();

            return back()->with('flash_error', $error_messages);
        }

    }

    /**
     * Function Name: revenues_ppv_payments()
     *
     * @uses list the redeem details
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */

    public function revenues_ppv_payments() {

        $moderator_id = Auth::guard('moderator')->user()->id;

        $ppv_payments = PayPerView::orderBy('created_at' , 'desc')
                        ->select('pay_per_views.*')
                        ->leftJoin('admin_videos', 'admin_videos.id', '=', 'video_id')
                        ->where('admin_videos.uploaded_by', $moderator_id)
                        ->get();

        $total_moderator_video_revenue = Setting::get('currency').total_moderator_video_revenue($moderator_id);
        
        return view('moderator.payments.ppv_payments')
                        ->with('page','revenues')
                        ->with('sub_page','revenues-ppv_payments')
                        ->with('ppv_payments' , $ppv_payments)
                        ->with('total_moderator_video_revenue' , $total_moderator_video_revenue); 
    }

     /**
     * Function Name: redeems_request_send()
     *
     * @uses list the redeem details
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */

    public function redeems_request_send(Request $request) {

        try {

            $request->request->add([ 
                'id' => \Auth::guard('moderator')->user()->id,
                'token' => \Auth::guard('moderator')->user()->token,
                'device_token' => \Auth::guard('moderator')->user()->device_token
            ]);

            DB::beginTransaction();

            $response = $this->ModeratorAPI->send_redeem_request($request)->getData();

            if($response->success) {

                DB::commit();

                return back()->with('flash_success', tr('send_redeem_request_success'));

            } else {

                throw new Exception($response->error_messages);
                
            }

            throw new Exception(Helper::get_error_message(146));

        } catch(Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            return back()->with('flash_error', $error_messages);
        }

    }

    /**
     * Function Name: redeem_request_cancel()
     *
     * @uses list the redeem details
     *
     * @created Vithya R
     *
     * @updated Vithya R
     *
     * @param
     *
     * @return view page
     */
    public function redeems_request_cancel(Request $request) {

        try {

            $request->request->add([ 
                'id' => \Auth::guard('moderator')->user()->id,
                'token' => \Auth::guard('moderator')->user()->token,
                'device_token' => \Auth::guard('moderator')->user()->device_token,
                'redeem_request_id' => $request->redeem_request_id,
            ]);

            DB::beginTransaction();

            $response = $this->ModeratorAPI->redeem_request_cancel($request)->getData();

            if($response->success) {

                DB::commit();

                return back()->with('flash_success', tr('send_redeem_request_success'));

            } else {

                throw new Exception($response->error_messages);
            }

            throw new Exception(Helper::get_error_message(146));

        } catch(Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            return back()->with('flash_error', $error_messages);
        }

    }
}
