<?php

namespace App\Http\Controllers;

use App\Repositories\AdminRepository as AdminRepo;

use App\Repositories\VideoRepository as VideoRepo;

use App\Repositories\PushNotificationRepository as PushRepo;

use Illuminate\Http\Request;

use App\Requests;

use App\Moderator;

use App\User;

use App\UserPayment;

use App\Subscription;

use App\PayPerView;

use App\Admin;

use App\Redeem;

use App\SubProfile;

use App\Notification;

use App\Category;

use App\RedeemRequest;

use App\SubCategory;

use App\SubCategoryImage;

use App\Genre;

use App\AdminVideo;

use App\AdminVideoImage;

use App\UserHistory;

use App\Wishlist;

use App\UserRating;

use App\Language;

use App\Settings;

use App\Page;

use App\Helpers\Helper;

use App\Helpers\EnvEditorHelper;

use App\Flag;

use App\Coupon;

use Validator;

use Hash;

use Mail;

use DB;

use DateTime;

use Auth;

use Exception;

use Redirect;

use Setting;

use Log;

use App\Jobs\StreamviewCompressVideo;

// use App\Jobs\NormalPushNotification;

use App\Jobs\SendVideoMail;

use App\EmailTemplate;

use App\Jobs\SendMailCamp;

use App\CastCrew;

use App\VideoCastCrew;

use App\UserLoggedDevice;

use App\UserCoupon;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('admin');  
    }

    public function check_role(Request $request) {

        if(Auth::guard('admin')->check()) {

            $admin_details = Auth::guard('admin')->user();

            if($admin_details->role == ADMIN) {

                return redirect()->route('admin.dashboard');
            }

            if($admin_details->role == SUBADMIN) {

                return redirect()->route('subadmin.dashboard');
            }

        } else {

            return redirect()->route('admin.login');

        }

    }

    /**
     * Function: login()
     * 
     * @uses used to display the login page
     *
     * @created vidhya R
     *
     * @edited vidhya R
     *
     * @param - 
     *
     * @return login view page
     */

    public function login() {

        return view('admin.login')->withPage('admin-login')->with('sub_page','');
    }

    /**
     * Function: dashboard()
     * 
     * @uses used to display analytics of the website
     *
     * @created vidhya R
     *
     * @edited vidhya R
     *
     * @param - 
     *
     * @return view page
     */

    public function dashboard() {

        $id = Auth::guard('admin')->user()->id;

        $admin = Admin::find($id);

        $admin->token = Helper::generate_token();

        $admin->token_expiry = Helper::generate_token_expiry();

        $admin->save();
        
        $user_count = User::count();

        $provider_count = Moderator::count();

        $video_count = AdminVideo::count();
        
        $recent_videos = Helper::recently_added();

        $get_registers = get_register_count();

        $recent_users = get_recent_users();

        $total_revenue = total_revenue();

        $view = last_days(10);

        if (Setting::get('track_user_mail')) {

            user_track("StreamHash - New Visitor");

        }

        return view('admin.dashboard.dashboard')->withPage('dashboard')
                    ->with('sub_page','')
                    ->with('user_count' , $user_count)
                    ->with('video_count' , $video_count)
                    ->with('provider_count' , $provider_count)
                    ->with('get_registers' , $get_registers)
                    ->with('view' , $view)
                    ->with('total_revenue' , $total_revenue)
                    ->with('recent_users' , $recent_users)
                    ->with('recent_videos' , $recent_videos);
    
    }

    /**
     * Function: profile()
     * 
     * @uses admin profile details 
     *
     * @created vidhya R
     *
     * @edited vidhya R
     *
     * @param - 
     *
     * @return view page
     */
    public function profile() {

        $id = Auth::guard('admin')->user()->id;

        $admin = Admin::find($id);

        return view('admin.account.profile')->with('admin' , $admin)->withPage('profile')->with('sub_page','');
    }

    /**
     * Function: profile_save()
     * 
     * @uses save admin updated profile details
     *
     * @created vidhya R
     *
     * @edited vidhya R
     *
     * @param - 
     *
     * @return view page
     */

    public function profile_save(Request $request) {

        $validator = Validator::make( $request->all(),array(
                'name' => 'regex:/^[a-zA-Z]*$/|max:100',
                'email' => $request->id ? 'email|max:255|unique:admins,email,'.$request->id : 'required|email|max:255|unique:admins,email,NULL',
                'mobile' => 'digits_between:4,16',
                'address' => 'max:300',
                'id' => 'required|exists:admins,id',
                'picture' => 'mimes:jpeg,jpg,png'
            )
        );
        
        if($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            return back()->with('flash_error', $error_messages);
        } else {
            
            $admin = Admin::find($request->id);
            
            $admin->name = $request->has('name') ? $request->name : $admin->name;

            $admin->email = $request->has('email') ? $request->email : $admin->email;

            $admin->mobile = $request->has('mobile') ? $request->mobile : $admin->mobile;

            $admin->gender = $request->has('gender') ? $request->gender : $admin->gender;

            $admin->address = $request->has('address') ? $request->address : $admin->address;

            if($request->hasFile('picture')) {
                Helper::delete_picture($admin->picture, "/uploads/");
                $admin->picture = Helper::normal_upload_picture($request->picture);
            }
                
            $admin->remember_token = Helper::generate_token();
            $admin->is_activated = 1;
            $admin->save();

            return back()->with('flash_success', tr('admin_not_profile'));
            
        }
    
    }

    /**
     * Function: change_password()
     * 
     * @uses change the admin password 
     *
     * @created vidhya R
     *
     * @edited vidhya R
     *
     * @param - 
     *
     * @return redirect with success/ error message
     */

    public function change_password(Request $request) {

        $old_password = $request->old_password;
        $new_password = $request->password;
        $confirm_password = $request->confirm_password;
        
        $validator = Validator::make($request->all(), [              
                'password' => 'required|min:6',
                'old_password' => 'required',
                'confirm_password' => 'required|min:6',
                'id' => 'required|exists:admins,id'
            ]);

        if($validator->fails()) {

            $error_messages = implode(',',$validator->messages()->all());

            return back()->with('flash_error', $error_messages);

        } else {

            $admin = Admin::find($request->id);

            if(Hash::check($old_password,$admin->password))
            {
                $admin->password = Hash::make($new_password);
                $admin->save();

                return back()->with('flash_success', tr('password_change_success'));
                
            } else {
                return back()->with('flash_error', tr('password_mismatch'));
            }
        }

        $response = response()->json($response_array,$response_code);

        return $response;
    
    }

    /**
     * Function: users()
     * 
     * @uses used to list the users
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return users management view page
     */

    public function users() {

        $users = User::orderBy('created_at','desc')->paginate(10);

        return view('admin.users.users')->withPage('users')
                        ->with('users' , $users)
                        ->with('sub_page','view-user');
    }

    /**
     * Function: users_create()
     * 
     * @uses used to list the users
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return view page
     */

    public function users_create() {

        return view('admin.users.add-user')->with('page' , 'users')->with('sub_page','add-user');
    }

    /**
     * Function: users_edit()
     * 
     * @uses used to display the edit page for the selected user
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return view page
     */

    public function users_edit(Request $request) {

        $user = User::find($request->id);

        if(count($user) == 0) {

            return redirect()->route('admin.users')->with('flash_error' , tr('user_not_found'));
        }

        return view('admin.users.edit-user')->withUser($user)->with('sub_page','view-user')->with('page' , 'users');
    }

    /**
     * Function: users_save()
     * 
     * @uses used to add /update the user details
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return redirect to user view page
     */

    public function users_save(Request $request) {

        if($request->id != '') {

            $validator = Validator::make( $request->all(), array(
                        'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                        'email' => 'required|email|max:255|unique:users,email,'.$request->id,
                        'mobile' => 'required|digits_between:4,16',
                    )
                );
        
        } else {
            $validator = Validator::make( $request->all(), array(
                    'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                    'email' => 'required|email|max:255|unique:users,email',
                    'mobile' => 'required|digits_between:4,16',
                    'password' => 'required|min:6|confirmed',
                )
            );
        
        }
       
        if($validator->fails())
        {
            $error_messages = implode(',', $validator->messages()->all());
            
            return back()->with('flash_error', $error_messages);
        } else {

            $new_user = 0;

            if($request->id != '') {

                $user = User::find($request->id);

                $message = tr('admin_not_user');

                if($request->hasFile('picture')) {
                    Helper::delete_picture($user->picture, "/uploads/images/"); // Delete the old pic
                    $user->picture = Helper::normal_upload_picture($request->file('picture'));

                }


            } else {

                $new_user = 1;

                //Add New User

                $user = new User;
                
                $new_password = $request->password;
                $user->password = Hash::make($new_password);
                $message = tr('admin_add_user');
                $user->login_by = 'manual';
                $user->device_type = 'web';

                $user->picture = asset('placeholder.png');
            }            

            $user->timezone = $request->has('timezone') ? $request->timezone : '';

            $user->name = $request->has('name') ? $request->name : '';
            $user->email = $request->has('email') ? $request->email: '';
            $user->mobile = $request->has('mobile') ? $request->mobile : '';
            
            $user->token = Helper::generate_token();
            $user->token_expiry = Helper::generate_token_expiry();
            $user->is_activated = 1;   

            $user->no_of_account = 1;

            $user->status = 1;   

            if($request->id == '') {
                
                $email_data['name'] = $user->name;
                $email_data['password'] = $new_password;
                $email_data['email'] = $user->email;
                $email_data['template_type'] = ADMIN_USER_WELCOME;

               // $subject = tr('user_welcome_title').' '.Setting::get('site_name');
                $page = "emails.admin_user_welcome";
                $email = $user->email;
                Helper::send_email($page,$subject = null,$email,$email_data);
            }

            $user->save();

            if ($new_user) {

                $sub_profile = new SubProfile;

                $sub_profile->user_id = $user->id;

                $sub_profile->name = $user->name;

                $sub_profile->picture = $user->picture;

                $sub_profile->status = DEFAULT_TRUE;

                $sub_profile->save();

            } else {

                $sub_profile = SubProfile::where('user_id', $request->id)->first();

                if (!$sub_profile) {

                    $sub_profile = new SubProfile;

                    $sub_profile->user_id = $user->id;

                    $sub_profile->name = $user->name;

                    $sub_profile->picture = $user->picture;

                    $sub_profile->status = DEFAULT_TRUE;

                    $sub_profile->save();

                }

            }

                $user->is_verified = 1;      

                $user->save();

            // Check the default subscription and save the user type 
            if ($request->id == '') {
                
                user_type_check($user->id);

            }

            if($user) {

                $moderator = Moderator::where('email', $user->email)->first();

                // If the user already registered as moderator, atuomatically the status will update.
                if($moderator && $user) {

                    $user->is_moderator = DEFAULT_TRUE;
                    $user->moderator_id = $moderator->id;
                    $user->save();

                    $moderator->is_activated = DEFAULT_TRUE;
                    $moderator->is_user = DEFAULT_TRUE;
                    $moderator->save();

                }

                register_mobile('web');

                if (Setting::get('track_user_mail')) {

                    user_track("StreamHash - New User Created");

                }

                return redirect()->route('admin.users.view' ,$user->id)->with('flash_success', $message);

            } else {
                return back()->with('flash_error', tr('admin_not_error'));
            }

        }
    
    }

    /**
     * Function: users_delete()
     * 
     * @uses used to delete the selected user details
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return redirect to users management page with success/error response
     */

    public function users_delete(Request $request) {
        
        if($user = User::where('id',$request->id)->first()) {

            // Check User Exists or not

            if ($user) {

                if ($user->device_type) {

                    // Load Mobile Registers

                    subtract_count($user->device_type);
                }

                if($user->picture)

                    Helper::delete_picture($user->picture, "/uploads/images/"); // Delete the old pic

                // After reduce the count from mobile register model delete the user

                if($user->is_moderator){    

                    $moderator = Moderator::where('email',$user->email)->first();
                    
                    if($moderator){

                        $moderator->is_user = 0;

                        $moderator->save(); 
                    }
                }

                if ($user->delete()) {

                    return back()->with('flash_success',tr('admin_not_user_del'));   
                }
            
            }
       
        }

        return redirect()->route('admin.users')->with('flash_error' , tr('user_not_found'));
    
    }

    /**
     * Function: users_status_change()
     * 
     * @uses used to approve/decline the selected user details
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return redirect to users management page with success/error response
     */
    public function users_status_change(Request $request) {

        $user_details = User::find($request->id);

        if(count($user_details) == 0) {

            return redirect()->route('admin.users')->with('flash_error' , tr('user_not_found'));
        }

        $user_details->is_activated = $user_details->is_activated ? DEFAULT_FALSE : DEFAULT_TRUE;

        $user_details->save();

        if($user_details->is_activated) {

            $message = tr('user_approve_success');

        } else {

            $message = tr('user_decline_success');
        }

        return back()->with('flash_success', $message);
    }

    /**
     * @uses Email verify for the user
     *
     * @param $user_id
     *
     * @return redirect back page with status of the email verification
     */

    /**
     * Function: users_verify_status()
     * 
     * @uses Email verify for the user
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param $user_id
     *
     * @return redirect to users management page with success/error response
     */

    public function users_verify_status($id) {

        if($data = User::find($id)) {

            $data->is_verified  = $data->is_verified ? 0 : 1;

            $data->save();

            return back()->with('flash_success' , $data->is_verified ? tr('user_verify_success') : tr('user_unverify_success'));

        } else {

            return back()->with('flash_error',tr('admin_not_error'));
            
        }
    }


    public function users_view($id) {

        if($user = User::find($id)) {

            return view('admin.users.user-details')
                        ->with('user' , $user)
                        ->withPage('users')
                        ->with('sub_page','users');

        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    public function users_upgrade($id) {

        if($user = User::find($id)) {

            // Check the user is exists in moderators table

            if(!$moderator = Moderator::where('email' , $user->email)->first()) {

                $moderator_user = new Moderator;
                $moderator_user->name = $user->name;
                $moderator_user->email = $user->email;
                if($user->login_by == "manual") {
                    $moderator_user->password = $user->password;  
                    $new_password = tr('user_login_password');

                } else {
                    $new_password = time();
                    $new_password .= rand();
                    $new_password = sha1($new_password);
                    $new_password = substr($new_password, 0, 8);
                    $moderator_user->password = Hash::make($new_password);
                }

                $moderator_user->picture = $user->picture;
                $moderator_user->mobile = $user->mobile;
                $moderator_user->address = $user->address;
                $moderator_user->save();

                $email_data = array();

               //  $subject = tr('user_welcome_title').' '.Setting::get('site_name');
                $page = "emails.moderator_welcome";
                $email = $user->email;
                $email_data['template_type'] = MODERATOR_WELCOME;
                $email_data['name'] = $moderator_user->name;
                $email_data['email'] = $moderator_user->email;
                $email_data['password'] = $new_password;

                Helper::send_email($page,$subject = null,$email,$email_data);

                $moderator = $moderator_user;

            }

            if($moderator) {
                $user->is_moderator = 1;
                $user->moderator_id = $moderator->id;
                $user->save();

                $moderator->is_activated = 1;
                $moderator->is_user = 1;
                $moderator->save();

                return back()->with('flash_success',tr('admin_user_upgrade'));

            } else  {

                return back()->with('flash_error',tr('admin_not_error'));    
            }

        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }

    }

    public function users_upgrade_disable(Request $request) {

        if($moderator = Moderator::find($request->moderator_id)) {

            if($user = User::find($request->id)) {
                $user->is_moderator = 0;
                $user->save();
            }

            $moderator->is_activated = 0;

            $moderator->save();

            return back()->with('flash_success',tr('admin_user_upgrade_disable'));

        } else {

            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    public function view_history($sub_profile_id) {

       if($sub_profile_details = SubProfile::find($sub_profile_id)) {

            $user_history = UserHistory::where('user_id' , $sub_profile_id)
                            ->leftJoin('users' , 'user_histories.user_id' , '=' , 'users.id')
                            ->leftJoin('admin_videos' , 'user_histories.admin_video_id' , '=' , 'admin_videos.id')
                            ->select(
                                'users.name as username' , 
                                'users.id as user_id' , 
                                'user_histories.admin_video_id',
                                'user_histories.id as user_history_id',
                                'admin_videos.title',
                                'user_histories.created_at as date'
                                )
                            ->paginate(10);
                            
            return view('admin.users.user-history')
                        ->with('data' , $user_history)
                        ->with('sub_profile_details', $sub_profile_details)
                        ->withPage('users')
                        ->with('sub_page','users');

        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    public function delete_history($id) {

        if($user_history = UserHistory::find($id)) {

            $user_history->delete();

            return back()->with('flash_success',tr('admin_not_history_del'));

        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    public function view_wishlist($id) {

        if($user = SubProfile::find($id)) {

            $user_wishlist = Wishlist::where('user_id' , $id)
                            ->leftJoin('users' , 'wishlists.user_id' , '=' , 'users.id')
                            ->leftJoin('admin_videos' , 'wishlists.admin_video_id' , '=' , 'admin_videos.id')
                            ->select(
                                'users.name as username' , 
                                'users.id as user_id' , 
                                'wishlists.admin_video_id',
                                'wishlists.id as wishlist_id',
                                'admin_videos.title',
                                'wishlists.created_at as date'
                                )
                            ->paginate(10);

            return view('admin.users.user-wishlist')
                        ->with('data' , $user_wishlist)
                        ->with('user', $user)
                        ->withPage('users')
                        ->with('sub_page','users');

        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    public function delete_wishlist($id) {

        if($user_wishlist = Wishlist::find($id)) {

            $user_wishlist->delete();

            return back()->with('flash_success',tr('admin_not_wishlist_del'));

        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    /**
     * Function: moderators()
     * 
     * @uses used to list the moderators
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return moderators management view page
     */

    public function moderators() {

        $moderators = Moderator::orderBy('created_at','desc')->paginate(10);

        return view('admin.moderators.moderators')->with('moderators' , $moderators)->withPage('moderators')->with('sub_page','view-moderator');
    }

    public function add_moderator() {
        return view('admin.moderators.add-moderator')->with('page' ,'moderators')->with('sub_page' ,'add-moderator');
    }

    public function edit_moderator($id) {

        $moderator = Moderator::find($id);

        return view('admin.moderators.edit-moderator')->with('moderator' , $moderator)->with('page' ,'moderators')->with('sub_page' ,'edit-moderator');
    }

    public function add_moderator_process(Request $request) {

        if($request->id != '') {

            $validator = Validator::make( $request->all(), array(
                        'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                        'email' => 'required|email|max:255|unique:moderators,email,'.$request->id,
                        'mobile' => 'required|digits_between:4,16',
                    )
                );
        } else {

            $validator = Validator::make( $request->all(), array(
                    'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                    'email' => 'required|email|max:255|unique:moderators,email,NULL',
                    'mobile' => 'required|digits_between:4,16',
                    'password' => 'required|min:6|confirmed',
                )
            );
        
        }
       
        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());
            return back()->with('flash_error', $error_messages);

        } else {

            $changed_email = DEFAULT_FALSE;

            $email = "";

            if($request->id != '') {
                $moderator = Moderator::find($request->id);
                $message = tr('admin_not_moderator');

                if ($moderator->email != $request->email) {

                    $changed_email = DEFAULT_TRUE;

                    $email = $moderator->email;

                }

            } else {
                $message = tr('admin_add_moderator');
                //Add New moderator
                $moderator = new Moderator;
                /*$new_password = time();
                $new_password .= rand();
                $new_password = sha1($new_password);
                $new_password = substr($new_password, 0, 8);*/
                $new_password = $request->password;

                // print_r(Hash::make($new_password));


                $moderator->password = Hash::make($new_password);


                $moderator->is_activated = 1;

            }

            $moderator->picture = asset('placeholder.png');

            $moderator->timezone = $request->has('timezone') ? $request->timezone : '';
            $moderator->name = $request->has('name') ? $request->name : '';
            $moderator->email = $request->has('email') ? $request->email: '';
            $moderator->mobile = $request->has('mobile') ? $request->mobile : '';
            
            $moderator->token = Helper::generate_token();
            $moderator->token_expiry = Helper::generate_token_expiry();
                               

            if($request->id == ''){
                $email_data['name'] = $moderator->name;
                $email_data['password'] = $new_password;
                $email_data['email'] = $moderator->email;
                $email_data['template_type'] = MODERATOR_WELCOME;
               // $subject = tr('moderator_welcome_title').Setting::get('site_name');
                $page = "emails.moderator_welcome";
                $email = $moderator->email;
                Helper::send_email($page,$subject = null,$email,$email_data);

            }

            $moderator->save();

            if($moderator) {

                $user = User::where('email', $moderator->email)->first();

                // if the moderator already exists in user table, the status will change automatically
                if($moderator && $user) {

                    $user->is_moderator = DEFAULT_TRUE;
                    $user->moderator_id = $moderator->id;
                    $user->save();

                    $moderator->is_activated = DEFAULT_TRUE;
                    $moderator->is_user = DEFAULT_TRUE;
                    $moderator->save();

                }

                if ($changed_email) {

                    if ($email) {

                        $email_data = array();

                       //  $subject = tr('user_welcome_title').' '.Setting::get('site_name');
                        $page = "emails.moderator_update_profile";
                        $email_data['template_type'] = MODERATOR_UPDATE_MAIL;
                        $email_data['name'] = $moderator->name;
                        $email_data['email'] = $moderator->email;

                        Helper::send_email($page,$subject = null,$email,$email_data);
                    }

                }



                if (Setting::get('track_user_mail')) {

                    user_track("StreamHash - Moderator Created");

                }

                return redirect('/admin/view/moderator/'.$moderator->id)->with('flash_success', $message);
            } else {
                return back()->with('flash_error', tr('admin_not_error'));
            }

        }
    
    }

    public function delete_moderator(Request $request) {

        if($moderator = Moderator::find($request->id)) {

            if($moderator->picture) {

                Helper::delete_picture($moderator->picture , '/uploads/images/');

            }

            if($moderator->is_user){

                $user = User::where('email',$moderator->email)->first();

                if($user){

                    $user->is_moderator = 0;

                    $user->save();
                }

            }

            $moderator->delete();

            if($moderator->id){

                $videos = AdminVideo::where('uploaded_by',$moderator->id)->first();

                if($videos){
                    
                    $videos->delete();  
                }         

            }

            return back()->with('flash_success',tr('admin_not_moderator_del'));

        } else {

            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    public function moderator_approve(Request $request) {

        $moderator = Moderator::find($request->id);

        $moderator->is_activated = 1;

        $moderator->save();

        if($moderator->is_activated ==1) {

            $message = tr('admin_not_moderator_approve');

        } else {

            $message = tr('admin_not_moderator_decline');
        }

        return back()->with('flash_success', $message);
    }

    public function moderator_decline(Request $request) {
        
        if($moderator = Moderator::find($request->id)) {
            
            $moderator->is_activated = 0;

            $moderator->save(); 

            $message = tr('admin_not_moderator_decline');
        
            return back()->with('flash_success', $message);  
        } else {
            return back()->with('flash_error' , tr('admin_not_error'));
        }
            
    }

    public function moderator_view_details($id) {

        if($moderator = Moderator::find($id)) {

            return view('admin.moderators.moderator-details')
                        ->with('moderator' , $moderator)
                        ->withPage('moderators')
                        ->with('sub_page','view-moderator');
        } else {

            return back()->with('flash_error',tr('admin_not_error'));
        }
    }



    /**
     * Function: categories()
     * 
     * @uses used to list the categories
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return categories management view page
     */

    public function categories() {

        $categories = Category::select('categories.id',
                            'categories.name' , 
                            'categories.picture',
                            'categories.is_series',
                            'categories.status',
                            'categories.is_approved',
                            'categories.created_by'
                        )
                        ->orderBy('categories.created_at', 'desc')
                        ->distinct('categories.id')
                        ->paginate(10);

        return view('admin.categories.categories')->with('categories' , $categories)->withPage('categories')->with('sub_page','view-categories');
    }

    public function add_category() {
        return view('admin.categories.add-category')->with('page' ,'categories')->with('sub_page' ,'add-category');
    }

    public function edit_category($id) {

        $category = Category::find($id);

        return view('admin.categories.edit-category')->with('category' , $category)->with('page' ,'categories')->with('sub_page' ,'edit-category');
    }

    public function add_category_process(Request $request) {

        if($request->id != '') {
            $validator = Validator::make( $request->all(), array(
                        'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                        'picture' => 'mimes:jpeg,jpg,bmp,png',
                    )
                );
        } else {
            $validator = Validator::make( $request->all(), array(
                    'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100|unique:categories,name',
                    'picture' => 'required|mimes:jpeg,jpg,bmp,png',
                )
            );
        
        }
       
        if($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            return back()->with('flash_error', $error_messages);

        } else {

            if($request->id != '') {
                $category = Category::find($request->id);
                $message = tr('admin_not_category');
                if($request->hasFile('picture')) {
                    Helper::delete_picture($category->picture, "/uploads/images/");
                }
            } else {
                $message = tr('admin_add_category');
                //Add New User
                $category = new Category;
                $category->is_approved = DEFAULT_TRUE;
                $category->created_by = ADMIN;
            }

            $category->name = $request->has('name') ? $request->name : '';
            $category->is_series = $request->has('is_series') ? $request->is_series : 0;
            $category->status = 1;
            
            if($request->hasFile('picture') && $request->file('picture')->isValid()) {
                $category->picture = Helper::normal_upload_picture($request->file('picture'));
            }

            $category->save();

            if($category) {

                if (Setting::get('track_user_mail')) {

                    user_track("StreamHash - Category Created");

                }

                return back()->with('flash_success', $message);
            } else {
                return back()->with('flash_error', tr('admin_not_error'));
            }

        }
    
    }

    public function approve_category(Request $request) {

        $category = Category::find($request->id);

        $category->is_approved = $request->status;

        $category->save();

        // ($category->subCategory) ? $category->subCategory()->update(['is_approved' => $request->status]) : '';

        if ($request->status == 0) {
            foreach($category->subCategory as $sub_category)
            {                
                $sub_category->is_approved = $request->status;
                $sub_category->save();
            } 

            foreach($category->adminVideo as $video)
            {                
                $video->is_approved = $request->status;
                $video->save();
            } 

            foreach($category->genre as $genre)
            {                
                $genre->is_approved = $request->status;
                $genre->save();
            } 
        }

        $message = tr('admin_not_category_decline');

        if($category->is_approved == DEFAULT_TRUE){

            $message = tr('admin_not_category_approve');
        }

        return back()->with('flash_success', $message);
    
    }

    public function delete_category(Request $request) {
        
        $category = Category::where('id' , $request->category_id)->first();

        if($category) {  

            Helper::delete_picture($category->picture, "/uploads/images/");
            
            $category->delete();

            return redirect()->route('admin.categories')->with('flash_success',tr('admin_not_category_del'));

        } else {

            return back()->with('flash_error',tr('admin_not_error'));

        }
    }

    /**
     * Function: categories_view()
     * 
     * @uses to display category details based on category id
     *
     * @created Anjana H
     *
     * @edited Anjana H
     *
     * @param 
     *
     * @return view page
     */
    public function categories_view(Request $request) {

        try {

            $category_details = Category::find($request->category_id);
            
            if( count($category_details) == 0 ) {

                throw new Exception(tr('admin_category_not_found'), 101);
                
            } else {

                 return view('admin.categories.view')
                        ->with('page' ,'categories')
                        ->with('sub_page' ,'categories-view')
                        ->with('category_details' ,$category_details);

            }

        } catch (Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.categories')->with('flash_error',$error);

        }

    }

    /**
     * Function: sub_categories()
     * 
     * @uses used to list the sub_categories
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return sub_categories management view page
     */

    public function sub_categories($category_id) {

        $category = Category::find($category_id);

        $sub_categories = SubCategory::where('category_id' , $category_id)
                        ->select(
                                'sub_categories.id as id',
                                'sub_categories.name as sub_category_name',
                                'sub_categories.description',
                                'sub_categories.is_approved',
                                'sub_categories.created_by'
                                )
                        ->orderBy('sub_categories.created_at', 'desc')
                        ->paginate(10);

        return view('admin.categories.subcategories.sub-categories')
                ->with('category' , $category)
                ->with('data' , $sub_categories)
                ->withPage('categories')
                ->with('sub_page','view-categories');
    }

    public function add_sub_category($category_id) {

        $category = Category::find($category_id);
    
        return view('admin.categories.subcategories.add-sub-category')->with('category' , $category)->with('page' ,'categories')->with('sub_page' ,'add-category');
    }

    public function edit_sub_category(Request $request) {

        $category = Category::find($request->category_id);

        $sub_category = SubCategory::find($request->sub_category_id);

        $sub_category_images = SubCategoryImage::where('sub_category_id' , $request->sub_category_id)
                                    ->orderBy('position' , 'ASC')->get();

        $genres = Genre::where('sub_category_id' , $request->sub_category_id)
                        ->orderBy('position' , 'asc')
                        ->get();

        return view('admin.categories.subcategories.edit-sub-category')
                ->with('category' , $category)
                ->with('sub_category' , $sub_category)
                ->with('sub_category_images' , $sub_category_images)
                ->with('genres' , $genres)
                ->with('page' ,'categories')
                ->with('sub_page' ,'');
    }

    public function add_sub_category_process(Request $request) {

        if($request->id != '') {
            $validator = Validator::make( $request->all(), array(
                        'category_id' => 'required|integer|exists:categories,id',
                        'id' => 'required|integer|exists:sub_categories,id',
                        'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                        'picture1' => 'mimes:jpeg,jpg,bmp,png',
                       // 'picture2' => 'mimes:jpeg,jpg,bmp,png',
                       // 'picture3' => 'mimes:jpeg,jpg,bmp,png',
                    )
                );
        } else {
            $validator = Validator::make( $request->all(), array(
                    'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                    'description' => 'required|max:255',
                    'picture1' => 'required|mimes:jpeg,jpg,bmp,png',
                    //'picture2' => 'required|mimes:jpeg,jpg,bmp,png',
                    //'picture3' => 'required|mimes:jpeg,jpg,bmp,png',
                )
            );
        
        }
       
        if($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            return back()->with('flash_error', $error_messages);

        } else {

            if($request->id != '') {

                $sub_category = SubCategory::find($request->id);

                $message = tr('admin_not_sub_category');

                if($request->hasFile('picture1')) {
                    Helper::delete_picture($sub_category->picture1, "/uploads/images/");
                }


            } else {
                $message = tr('admin_add_sub_category');
                //Add New User
                $sub_category = new SubCategory;

                $sub_category->is_approved = DEFAULT_TRUE;
                $sub_category->created_by = ADMIN;
            }

            $sub_category->category_id = $request->has('category_id') ? $request->category_id : '';
            
            if($request->has('name')) {
                $sub_category->name = $request->name;
            }

            if($request->has('description')) {
                $sub_category->description =  $request->description;   
            }

            $sub_category->save(); // Otherwise it will save empty values

           /* if($request->has('genre')) {

                foreach ($request->genre as $key => $genres) {
                    $genre = new Genre;
                    $genre->category_id = $request->category_id;
                    $genre->sub_category_id = $sub_category->id;
                    $genre->name = $genres;
                    $genre->status = DEFAULT_TRUE;
                    $genre->is_approved = DEFAULT_TRUE;
                    $genre->created_by = ADMIN;
                    $genre->position = $key+1;
                    $genre->save();
                }
            }*/
            
            if($request->hasFile('picture1')) {
                sub_category_image($request->file('picture1') , $sub_category->id,1);
            }

            if($request->hasFile('picture2')) {
                sub_category_image($request->file('picture2'), $sub_category->id , 2);
            }

            if($request->hasFile('picture3')) {
                sub_category_image($request->file('picture3'), $sub_category->id , 3);
            }

            if($sub_category) {

                if (Setting::get('track_user_mail')) {

                    user_track("StreamHash - Sub category Created");

                }

                return back()->with('flash_success', $message);
            } else {
                return back()->with('flash_error', tr('admin_not_error'));
            }

        }
    
    }

    public function approve_sub_category(Request $request) {

        $sub_category = SubCategory::find($request->id);

        $sub_category->is_approved = $request->status;

        $sub_category->save();

        if ($request->status == 0) {

            foreach($sub_category->adminVideo as $video)
            {                
                $video->is_approved = $request->status;
                $video->save();
            } 

            foreach($sub_category->genres as $genre)
            {                
                $genre->is_approved = $request->status;
                $genre->save();
            } 

        }

        $message = tr('admin_not_sub_category_decline');

        if($sub_category->is_approved == DEFAULT_TRUE){

            $message = tr('admin_not_sub_category_approve');
        }

        return back()->with('flash_success', $message);
    
    }

    public function delete_sub_category(Request $request) {

        $sub_category = SubCategory::where('id' , $request->id)->first();

        if($sub_category) {

            Helper::delete_picture($sub_category->picture1, "/uploads/images/");

            $sub_category->delete();

            return back()->with('flash_success',tr('admin_not_sub_category_del'));
        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    /**
     * Function: sub_categories_view()
     * 
     * @uses to display Sub Category details based on Sub Category id
     *
     * @created Anjana H
     *
     * @edited Anjana H
     *
     * @param 
     *
     * @return view page
     */
    public function sub_categories_view(Request $request) {

        try {

            $sub_category_details = SubCategory::find($request->sub_category_id);
            
            if( count($sub_category_details) == 0 ) {

                throw new Exception(tr('admin_sub_category_not_found'), 101);
                
            } else {

                 return view('admin.categories.subcategories.view')
                        ->with('page' ,'categories')
                        ->with('sub_page' ,'categories-view')
                        ->with('sub_category_details' ,$sub_category_details);

            }

        } catch (Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.sub_categories',['category_id' => $request->category_id] )->with('flash_error',$error);

        }

    }

    public function add_genre($sub_category) {

        $subcategory = SubCategory::find($sub_category);

        if ($subcategory) {

            // $genres = Genre::where('sub_category_id', $subcategory->id)->where('updated_at', 'desc')->first();

            $genre = new Genre;

            // $genre->position = $genres ? $genres->position + 1 : 1;
        
            return view('admin.categories.subcategories.genres.create')->with('subcategory' , $subcategory)->with('page' ,'categories')->with('sub_page' ,'add-category')->with('genre', $genre);

        } else {

            return back()->with('flash_error', tr('sub_category_not_found'));
        }

    }

    /**
     * Function: genres_edit()
     * 
     * @uses used to store or update the genre details
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return redirect to view page
     */

    public function genres_save(Request $request) {

        $validator = Validator::make( $request->all(), array(
                'category_id' => 'required|integer|exists:categories,id',
                'sub_category_id' => 'required|integer|exists:sub_categories,id',
                'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                'video'=> ($request->id) ? 'mimes:mkv,mp4,qt' : 'required|mimes:mkv,mp4,qt',
                'image'=> ($request->id) ? 'mimes:jpeg,jpg,bmp,png' : 'required|mimes:jpeg,jpg,bmp,png',
            )
        );


        if($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());
            return back()->with('flash_error', $error_messages);

        } else {


            $genre = $request->id ? Genre::find($request->id) : new Genre;

            if ($genre->id) {

                $position = $genre->position;

            } else {

                // To order the position of the genres
                $position = 1;

                if($check_position = Genre::where('sub_category_id' , $request->sub_category_id)->orderBy('position' , 'desc')->first()) {
                    $position = $check_position->position +1;
                } 

            }

            $genre->category_id = $request->category_id;
            $genre->sub_category_id = $request->sub_category_id;
            $genre->name = $request->name;

            $genre->position = $position;
            $genre->status = DEFAULT_TRUE;
            $genre->is_approved = DEFAULT_TRUE;
            $genre->created_by = ADMIN;


            if($request->hasFile('video')) {

                if ($genre->id) {

                    if ($genre->video) {

                        Helper::delete_picture($genre->video, '/uploads/videos/original/');  

                    }  
                }

                $video = Helper::video_upload($request->file('video'), 1);


                $genre->video = $video['db_url'];  
            }

            if($request->hasFile('image')) {

                if ($genre->id) {

                    if ($genre->image) {

                        Helper::delete_picture($genre->image,'/uploads/images/');  

                    }  
                }

                $genre->image =  Helper::normal_upload_picture($request->file('image'), '/uploads/images/');
            }



            if($request->hasFile('subtitle')) {

                if ($genre->id) {

                    if ($genre->subtitle) {

                        Helper::delete_picture($genre->subtitle, "/uploads/subtitles/");  

                    }  
                }

                $genre->subtitle =  Helper::subtitle_upload($request->file('subtitle'));

            }

            $genre->save();

            $message = ($request->id) ? tr('admin_edit_genre') : tr('admin_add_genre');

            if($genre) {

                if(!$request->id) {

                    $genre->unique_id = $genre->id;

                    $genre->save();
                }

                if (Setting::get('track_user_mail')) {

                    user_track("StreamHash - Genre Created");

                }
                return back()->with('flash_success', $message);
            } else {
                return back()->with('flash_error', tr('admin_not_error'));
            }
        }
    
    }

    /**
     * Function: genres_edit()
     * 
     * @uses used to display the edit page for the selected genre
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return view page
     */

    public function genres_edit($sub_category_id, $genre_id) {

        $subcategory = SubCategory::find($sub_category_id);

        $genre = Genre::find($genre_id);
    
        return view('admin.categories.subcategories.genres.edit')->with('subcategory' , $subcategory)->with('page' ,'categories')->with('sub_page' ,'add-category')->with('genre', $genre);
    }

    /**
     * Function: genres()
     * 
     * @uses used to list the genres
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return genres management view page
     */

    public function genres($sub_category) {

        $subcategory = SubCategory::find($sub_category);

        $genres = Genre::where('sub_category_id' , $sub_category)
                        ->leftjoin('sub_categories', 'sub_categories.id', '=', 'genres.sub_category_id')
                        ->leftjoin('categories', 'categories.id', '=', 'genres.category_id')
                        ->select(
                                'genres.id as genre_id',
                                'categories.name as category_name',
                                'sub_categories.name as sub_category_name',
                                'genres.name as genre_name',
                                'genres.video',
                                'genres.subtitle',
                                'genres.image',
                                'genres.is_approved',
                                'genres.created_at',
                                'sub_categories.id as sub_category_id',
                                'sub_categories.category_id as category_id',
                                'genres.position as position'
                                )
                        ->orderBy('genres.created_at', 'desc')
                        ->paginate(10);

        return view('admin.categories.subcategories.genres.index')
                        ->with('sub_category' , $subcategory)
                        ->with('data' , $genres)
                        ->withPage('categories')
                        ->with('sub_page','view-categories');
    
    }

    public function approve_genre(Request $request) {

        try {

            DB::beginTransaction();

            $genre = Genre::find($request->id);

            if ($genre) {

                $genre->is_approved = $request->status;

                //$genre->save();

                $position = $genre->position;

                $sub_category_id = $genre->sub_category_id;

                if ($request->status == 0) {

                    foreach($genre->adminVideo as $video) {

                        $video->is_approved = $request->status;

                        $video->save();

                    }

                    $next_genres = Genre::where('sub_category_id', $sub_category_id)
                                    ->where('position', '>', $position)
                                    ->orderBy('position', 'asc')
                                    ->where('is_approved', DEFAULT_TRUE)
                                    ->get();

                    if (count($next_genres) > 0) {

                        foreach ($next_genres as $key => $value) {
                            
                            $value->position = $value->position - 1;

                            if ($value->save()) {


                            } else {

                                throw new Exception(tr('genre_not_saved'));
                                
                            }

                        }

                    }

                    $genre->position = 0;

                } else {

                    $get_genre_position = Genre::where('sub_category_id', $sub_category_id)
                                    ->orderBy('position', 'desc')
                                    ->where('is_approved', DEFAULT_TRUE)
                                    ->first();

                    if($get_genre_position) {

                        $genre->position = $get_genre_position->position + 1;

                    }

                }

                if ($genre->save()) {


                } else {

                    throw new Exception(tr('genre_not_saved'));

                }

                $message = tr('admin_not_genre_decline');

                if($genre->is_approved == DEFAULT_TRUE){

                    $message = tr('admin_not_genre_approve');
                }

                DB::commit();

                return back()->with('flash_success', $message);

            } else {

                throw new Exception(tr('genre_not_found'));
                
            }

        } catch (Exception $e) {

            DB::rollback();

            return back()->with('flash_error', $e->getMessage());
        }
    
    }

    /**
     * Function Name : genres_view()
     *
     * Used to display the selected genre details
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return view page
     */

    public function genres_view($id) {

        $genre = Genre::where('genres.id' , $id)
                    ->leftJoin('categories' , 'genres.category_id' , '=' , 'categories.id')
                    ->leftJoin('sub_categories' , 'genres.sub_category_id' , '=' , 'sub_categories.id')
                    ->select('genres.id as genre_id' ,'genres.name as genre_name' , 
                             'genres.position' , 'genres.status' , 
                             'genres.is_approved' , 'genres.created_at as genre_date' ,
                             'genres.created_by',
                                'genres.video',
                            'genres.image',
                             'genres.category_id as category_id',
                             'genres.sub_category_id',
                             'categories.name as category_name',
                             'genres.unique_id',
                             'genres.subtitle',
                             'sub_categories.name as sub_category_name')
                    ->orderBy('genres.position' , 'asc')
                    ->first();

        if($genre) {

            return view('admin.categories.subcategories.genres.view-genre')->with('genre' , $genre)
                    ->withPage('categories')
                    ->with('sub_page','view-categories');

        } else {
            return redirect()->route('admin.categories')->with('flash_error' , tr('genre_not_found'));
        }
        
    }

    /**
     * Function Name : genres_delete()
     *
     * Used to delete the selected genre
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return view page
     */

    public function genres_delete(Request $request) {

        try {

            DB::beginTransaction();
        
            if($genre = Genre::where('id',$request->id)->first()) {

                Helper::delete_picture($genre->image,'/uploads/images/'); 

                if ($genre->video) {

                    Helper::delete_picture($genre->video, '/uploads/videos/original/');   

                }

                if ($genre->subtitle) {

                    Helper::delete_picture($genre->subtitle, "/uploads/subtitles/");

                }  

                $position = $genre->position;

                $sub_category_id = $genre->sub_category_id;

                if ($genre->delete()) {

                    $next_genres = Genre::where('sub_category_id', $sub_category_id)
                            ->where('position', '>', $position)
                            ->orderBy('position', 'asc')
                            ->where('is_approved', DEFAULT_TRUE)
                            ->get();

                    if (count($next_genres) > 0) {

                        foreach ($next_genres as $key => $value) {
                            
                            $value->position = $value->position - 1;

                            $value->save();

                        }

                    }

                } else {

                    throw new Exception(tr('genre_not_saved'));

                }

            } else {

                throw new Exception(tr('genre_not_found'));
                
            }

            DB::commit();

            return back()->with('flash_success', tr('admin_not_genre_del'));

        } catch (Exception $e) {

            DB::rollback();

            return back()->with('flash_error', $e->getMessage());
        }
    
    }

    /**
    * Function Name: videos()
    *
    * @uses: get the videos list
    *
    * @created vidhya R
    *
    * @edited Vidhya R
    *
    * @param Get the video list in table
    *
    * @return Videos list
    */

    public function videos(Request $request) {

        $query = AdminVideo::leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
                    ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
                    ->leftJoin('genres' , 'admin_videos.genre_id' , '=' , 'genres.id')
                    ->select('admin_videos.id as video_id' ,'admin_videos.title' , 
                             'admin_videos.description' , 'admin_videos.ratings' , 
                             'admin_videos.reviews' , 'admin_videos.created_at as video_date' ,
                             'admin_videos.default_image',
                             'admin_videos.banner_image',
                             'admin_videos.amount',
                             'admin_videos.admin_amount',
                             'admin_videos.user_amount',
                             'admin_videos.unique_id',
                             'admin_videos.type_of_user',
                             'admin_videos.type_of_subscription',
                             'admin_videos.category_id as category_id',
                             'admin_videos.sub_category_id',
                             'admin_videos.genre_id',
                             'admin_videos.is_home_slider',
                             'admin_videos.watch_count',
                             'admin_videos.compress_status',
                             'admin_videos.trailer_compress_status',
                             'admin_videos.main_video_compress_status',
                             'admin_videos.status','admin_videos.uploaded_by',
                             'admin_videos.edited_by','admin_videos.is_approved',
                             'admin_videos.video_subtitle',
                             'admin_videos.trailer_subtitle',
                             'categories.name as category_name' , 'sub_categories.name as sub_category_name' ,
                             'genres.name as genre_name',
                             'admin_videos.is_banner',
                             'admin_videos.position')
                    ->orderBy('admin_videos.created_at' , 'desc')
                    ->withCount('offlineVideos');

        if ($request->banner == BANNER_VIDEO) {

            $query->where('is_banner', BANNER_VIDEO);

            $sub_page = 'view-banner-videos';

        } else {

            $sub_page = 'view-videos';
        }

        $category = $sub_category = $genre = $moderator_details = "";

        if($request->category_id) {

            $query->where('admin_videos.category_id', $request->category_id);

            $category = Category::find($request->category_id);
        }

        if($request->sub_category_id) {

            $query->where('admin_videos.sub_category_id', $request->sub_category_id);

            $sub_category = SubCategory::find($request->sub_category_id);
        }

        if($request->genre_id) {

            $query->where('admin_videos.genre_id', $request->genre_id);

            $genre = Genre::find($request->genre_id);
        }

        if($request->moderator_id) {

            $query->where('admin_videos.uploaded_by', $request->moderator_id);

            $moderator_details = Moderator::find($request->moderator_id);
        }

        $videos = $query->paginate(10);

        return view('admin.videos.videos')->with('videos' , $videos)
                    ->withPage('videos')
                    ->with('sub_page',$sub_page)
                    ->with('category' , $category)
                    ->with('sub_category' , $sub_category)
                    ->with('genre' , $genre)
                    ->with('moderator_details' , $moderator_details);
   
    }

    /**
    * Function Name : moderator_videos()
    *
    * Description: Display the moderator videos list
    *
    * @param Moderator id
    *
    * @return Moderator video list details
    */
    public function moderator_videos($id) {

        $videos = AdminVideo::leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
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
                             'admin_videos.main_video_compress_status',
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
                    ->where('uploaded_by',$id)
                    ->paginate(10);

        return view('admin.videos.videos')
                    ->with('videos' , $videos)
                     ->with('category' , [])
                    ->with('sub_category' , [])
                    ->with('genre' , [])
                    ->withPage('videos')
                    ->with('sub_page','view-videos');
   
    }

    public function view_video(Request $request) {

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
                             'admin_videos.default_image','admin_videos.banner_image','admin_videos.is_banner','admin_videos.video_type',
                             'admin_videos.video_upload_type',
                             'admin_videos.amount',
                             'admin_videos.type_of_user',
                             'admin_videos.type_of_subscription',
                             'admin_videos.category_id as category_id',
                             'admin_videos.sub_category_id',
                             'admin_videos.genre_id',
                             'admin_videos.video_type',
                             'admin_videos.uploaded_by',
                             'admin_videos.ppv_created_by',
                             'admin_videos.details',
                             'admin_videos.watch_count',
                             'admin_videos.admin_amount',
                             'admin_videos.user_amount',
                             'admin_videos.video_upload_type',
                             'admin_videos.duration',
                             'admin_videos.redeem_amount',
                             'admin_videos.compress_status',
                             'admin_videos.trailer_compress_status',
                             'admin_videos.main_video_compress_status',
                             'admin_videos.video_resolutions',
                             'admin_videos.video_resize_path',
                             'admin_videos.trailer_resize_path',
                             'admin_videos.is_approved',
                             'admin_videos.unique_id',
                             'admin_videos.video_subtitle',
                             'admin_videos.trailer_subtitle',
                             'admin_videos.trailer_duration',
                             'admin_videos.trailer_video_resolutions',
                             'admin_videos.publish_time',
                             'categories.name as category_name' , 'sub_categories.name as sub_category_name' ,
                             'genres.name as genre_name',
                             'admin_videos.video_gif_image',
                             'admin_videos.is_banner',
                             'admin_videos.is_pay_per_view',
                             'admin_videos.age',
                             'admin_videos.status'
                         )
                    ->orderBy('admin_videos.created_at' , 'desc')
                    ->first();

        $videoPath = $video_pixels = $trailer_video_path = $trailer_pixels = $trailerstreamUrl = $videoStreamUrl = '';

        $ios_trailer_video = $videos->trailer_video;

        $ios_video = $videos->video;

        if ($videos->video_type == VIDEO_TYPE_UPLOAD && $videos->video_upload_type == VIDEO_UPLOAD_TYPE_DIRECT) {

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

        $page = 'videos';

        $sub_page = 'admin_videos_view';

        if($videos->is_banner == 1) {

            $sub_page = 'view-banner-videos';
        }

        // Load Video Cast & crews

        $video_cast_crews = VideoCastCrew::select('cast_crew_id', 'name')
                    ->where('admin_video_id', $request->id)
                    ->leftjoin('cast_crews', 'cast_crews.id', '=', 'video_cast_crews.cast_crew_id')
                    ->get()->pluck('name')->toArray();

        return view('admin.videos.view-video')->with('video' , $videos)
                    ->with('video_images' , $admin_video_images)
                    ->withPage($page)
                    ->with('sub_page',$sub_page)
                    ->with('videoPath', $videoPath)
                    ->with('video_pixels', $video_pixels)
                    ->with('ios_trailer_video', $ios_trailer_video)
                    ->with('ios_video', $ios_video)
                    ->with('trailer_video_path', $trailer_video_path)
                    ->with('trailer_pixels', $trailer_pixels)
                    ->with('videoStreamUrl', $videoStreamUrl)
                    ->with('trailerstreamUrl', $trailerstreamUrl)
                    ->with('video_cast_crews', $video_cast_crews);
        }
    }

    public function approve_video($id) {

        try {

            DB::beginTransaction();

            $video = AdminVideo::find($id);

            $video->is_approved = DEFAULT_TRUE;

            if (empty($video->publish_time) || $video->publish_time == '0000-00-00 00:00:00') {

                $video->publish_time = date('Y-m-d H:i:s');

            }

            // Check the video has genre type or not

            if ($video->genre_id > 0) {

                /*
                 * Check is there any videos present in same genre, 
                 * if it is, assign the position with increment of 1
                 */

                $get_video_position = AdminVideo::where('genre_id', $video->genre_id)
                                ->orderBy('position', 'desc')
                                ->where('is_approved', DEFAULT_TRUE)
                                ->where('status', DEFAULT_TRUE)
                                ->first();

                if($get_video_position) {

                    $video->position = $get_video_position->position + 1;

                }

            }

            // Uncommented by vidhya. with below code the response will error

            if($video->is_approved == DEFAULT_TRUE) {

                // Notification::save_notification($video->id);
                
                $message = tr('admin_not_video_approve');
            } else {
                $message = tr('admin_not_video_decline');
            } 

            $video->save();

            DB::commit();

            return back()->with('flash_success', $message);

        }catch(Exception $e) {

            DB::rollback();

            return back()->with('flash_error', $e->getMessage());
        }
    }


    /**
     * Function Name : publish_video()
     * To Publish the video for user
     *
     * @param int $id : Video id
     *
     * @return Flash Message
     */
    public function publish_video($id) {
        // Load video based on Auto increment id
        $video = AdminVideo::find($id);
        // Check the video present or not
        if ($video) {
            $video->status = DEFAULT_TRUE;
            $video->publish_time = date('Y-m-d H:i:s');
            // Save the values in DB
            if ($video->save()) {
                return back()->with('flash_success', tr('admin_published_video_success'));
            }
        }
        return back()->with('flash_error', tr('admin_published_video_failure'));
    }


    public function decline_video($id) {

        try {
        
            $video = AdminVideo::find($id);

            $video->is_approved = DEFAULT_FALSE;

            // Check the video has genre type or not
                   
            if ($video->genre_id > 0) {

                /*
                 * Check is there any videos present in same genre, 
                 * if it is, assign the position with decrement of 1.(for all videos)
                 */

                $next_videos = AdminVideo::where('genre_id', $video->genre_id)
                                ->where('position', '>', $video->position)
                                ->orderBy('position', 'asc')
                                ->where('is_approved', DEFAULT_TRUE)
                                ->where('status', DEFAULT_TRUE)
                                ->get();

                if (count($next_videos) > 0) {

                    foreach ($next_videos as $key => $value) {
                        
                        $value->position = $value->position - 1;

                        if ($value->save()) {


                        } else {

                            throw new Exception(tr('video_not_saved'));
                            
                        }

                    }

                }

                $video->position = 0;

            }

            if($video->is_approved == DEFAULT_TRUE){

                $message = tr('admin_not_video_approve');

            } else {

                $message = tr('admin_not_video_decline');

            }

            DB::commit();

            $video->save();

            return back()->with('flash_success', $message);

        } catch (Exception $e) {

            DB::rollback();

            return back()->with('flash_error', $e->getMessage());

        }
    }

    public function delete_video($id) {

        try {

            DB::beginTransaction();

            if($video = AdminVideo::where('id' , $id)->first())  {

                $main_video = $video->video;

                $subtitle = $video->subtitle;

                $banner_image = $video->banner_image;

                $default_image = $video->default_image;

                $video_resize_path = $video->video_resize_path;

                $trailer_resize_path = $video->trailer_resize_path;

                $position = $video->position;

                $genre_id = $video->genre_id;

                if ($video->delete()) {

                    if ($genre_id > 0) {

                        $next_videos = AdminVideo::where('genre_id', $genre_id)
                                ->where('position', '>', $position)
                                ->orderBy('position', 'asc')
                                ->where('is_approved', DEFAULT_TRUE)
                                ->where('status', DEFAULT_TRUE)
                                ->get();

                        if (count($next_videos) > 0) {

                            foreach ($next_videos as $key => $value) {
                                
                                $value->position = $value->position - 1;

                                if ($value->save()){


                                } else {

                                    throw new Exception(tr('video_not_saved'));
                                    
                                }

                            }

                        }

                    }

                    Helper::delete_picture($main_video, "/uploads/videos/original/");

                    Helper::delete_picture($subtitle, "/uploads/subtitles/"); 

                    if ($banner_image) {

                        Helper::delete_picture($banner_image, "/uploads/images/");
                    }

                    Helper::delete_picture($default_image, "/uploads/images/");

                    if ($video_resize_path) {

                        $explode = explode(',', $video_resize_path);

                        if (count($explode) > 0) {

                            foreach ($explode as $key => $exp) {

                                Helper::delete_picture($exp, "/uploads/videos/original/");

                            }

                        }    

                    }

                    if($trailer_resize_path) {

                        $explode = explode(',', $trailer_resize_path);

                        if (count($explode) > 0) {


                            foreach ($explode as $key => $exp) {


                                Helper::delete_picture($exp, "/uploads/videos/original/");

                            }

                        }    

                    }


                } else {

                    throw new Exception(tr('video_delete_failure'));
                    
                }
            
            }

            DB::commit();

            return back()->with('flash_success', 'Video deleted successfully');

        } catch (Exception $e) {

            DB::rollback();

            return back()->with('flash_error', $e->getMessage());
        }
    }

    public function slider_video($id) {

        $video = AdminVideo::where('is_home_slider' , 1 )->update(['is_home_slider' => 0]); 

        $video = AdminVideo::where('id' , $id)->update(['is_home_slider' => 1] );

        return back()->with('flash_success', tr('slider_success'));
    
    }

    /**
     * Function: banner_videos()
     * 
     * @uses used to list the banner videos
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return banner_videos management view page
     */

    public function banner_videos(Request $request) {

        $videos = AdminVideo::leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
                    ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
                    ->leftJoin('genres' , 'admin_videos.genre_id' , '=' , 'genres.id')
                    ->where('admin_videos.is_banner' , 1 )
                    ->select('admin_videos.id as video_id' ,'admin_videos.title' , 
                             'admin_videos.description' , 'admin_videos.ratings' , 
                             'admin_videos.reviews' , 'admin_videos.created_at as video_date' ,
                             'admin_videos.default_image',
                             'admin_videos.banner_image',

                             'admin_videos.category_id as category_id',
                             'admin_videos.sub_category_id',
                             'admin_videos.genre_id',
                             'admin_videos.is_home_slider',

                             'admin_videos.status','admin_videos.uploaded_by',
                             'admin_videos.edited_by','admin_videos.is_approved',

                             'categories.name as category_name' , 'sub_categories.name as sub_category_name' ,
                             'genres.name as genre_name')
                    ->orderBy('admin_videos.created_at' , 'desc')
                    ->paginate(10);

        return view('admin.banner_videos.banner-videos')->with('videos' , $videos)
                    ->withPage('banner-videos')
                    ->with('sub_page','view-banner-videos');
   
    }

    public function add_banner_video(Request $request) {

        $categories = Category::where('categories.is_approved' , 1)
                        ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                            'categories.is_series' ,'categories.status' , 'categories.is_approved')
                        ->leftJoin('sub_categories' , 'categories.id' , '=' , 'sub_categories.category_id')
                        ->groupBy('sub_categories.category_id')
                        ->havingRaw("COUNT(sub_categories.id) > 0")
                        ->orderBy('categories.name' , 'asc')
                        ->get();

        return view('admin.banner_videos.banner-video-upload')
                ->with('categories' , $categories)
                ->with('page' ,'banner-videos')
                ->with('sub_page' ,'add-banner-video');

    }

    public function change_banner_video($id) {

        $video = AdminVideo::find($id);

        $video->is_banner = 0 ;

        $video->save();

        $message = tr('change_banner_video_success');
       
        return back()->with('flash_success', $message);
    }

    /**
     * Function: user_ratings()
     * 
     * @uses used to list the user_ratings
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return user_ratings management view page
     */

    public function user_ratings() {
            
            $user_reviews = UserRating::leftJoin('users', 'user_ratings.user_id', '=', 'users.id')
                ->select('user_ratings.id as rating_id', 'user_ratings.rating', 
                         'user_ratings.comment', 
                         'users.first_name as user_first_name', 
                         'users.last_name as user_last_name', 
                         'users.id as user_id', 'user_ratings.created_at')
                ->orderBy('user_ratings.id', 'ASC')
                ->paginate(10);
            return view('admin.reviews')->with('name', 'User')->with('reviews', $user_reviews);
    }

    public function delete_user_ratings(Request $request) {

        if($user = UserRating::find($request->id)) {
            $user->delete();
        }

        return back()->with('flash_success', tr('admin_not_ur_del'));
    }

    /**
     * Function: user_payments()
     * 
     * @uses used to list the user_payments
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return user_payments management view page
     */

    public function user_payments() {

        $payments = UserPayment::orderBy('created_at' , 'desc')->paginate(10);

        $payment_count = UserPayment::count();

        return view('admin.payments.user-payments')->with('data' , $payments)->with('page','payments')->with('sub_page','user-payments')->with('payment_count', $payment_count); 
    }

    public function email_settings() {

        $admin_id = \Auth::guard('admin')->user()->id;

        $result = EnvEditorHelper::getEnvValues();

        \Auth::guard('admin')->loginUsingId($admin_id);

        return view('admin.email-settings')->with('result',$result)->withPage('email-settings')->with('sub_page',''); 
    }


    public function email_settings_process(Request $request) {

        $email_settings = ['MAIL_DRIVER' , 'MAIL_HOST' , 'MAIL_PORT' , 'MAIL_USERNAME' , 'MAIL_PASSWORD' , 'MAIL_ENCRYPTION' , 'MAILGUN_DOMAIN' , 'MAILGUN_SECRET'];

        $admin_id = \Auth::guard('admin')->user()->id;

        if($email_settings){

            foreach ($email_settings as $key => $data) {

                if($request->$data){ 

                    \Enveditor::set($data,$request->$data);

                } else{

                    \Enveditor::set($data,$request->$data);
                }
            }
        }
    
        $result = EnvEditorHelper::getEnvValues();

        return redirect(route('clear-cache'))->with('result' , $result)->with('flash_success' , tr('email_settings_success'));

    }

    /** 
     * Function Name: mobile_settings_save()
     *
     * @uses used to update the mobile app related details
     *
     * @created Vidhya R
     *
     * @edited
     *
     * @param form data
     *
     * @return redirect to success page
     *
     */

    public function mobile_settings_save(Request $request) {

        $mobile_settings = ['FCM_SENDER_ID' , 'FCM_SERVER_KEY' , 'FCM_PROTOCOL'];

        $admin_id = \Auth::guard('admin')->user()->id;

        if($mobile_settings){

            foreach ($mobile_settings as $key => $data) {

                if($request->$data){ 

                    \Enveditor::set($data,$request->$data);

                } else{

                    \Enveditor::set($data,$request->$data);
                }
            }
        
        }

        if($request->has('playstore')) {
            Settings::where('key' , 'playstore')->update(['value' => $request->playstore]);
        }

        if($request->has('appstore')) {
            Settings::where('key' , 'appstore')->update(['value' => $request->appstore]);
        }
    
        $result = EnvEditorHelper::getEnvValues();

        return redirect(route('clear-cache'))->with('result' , $result)->with('flash_success' , tr('mobile_settings_success'));

    }

    public function other_settings(Request $request){

            $settings = Settings::where('key', 'token_expiry_hour')->first();

            if ($settings) {

                $settings->value = $request->token_expiry_hour;

                $settings->save();

            }

       

            $settings = Settings::where('key','custom_users_count')->first();

            if($settings){

                $settings->value = $request->custom_users_count;

                $settings->save();

            }    
         

            $settings = Settings::where('key', 'email_notification')->first();

            if ($settings) {

                $settings->value = $request->email_notification ? $request->email_notification : DEFAULT_FALSE;

                $settings->save();

            }

            $settings = Settings::where('key', 'google_analytics')->first();

            if ($settings) {

                $settings->value = $request->google_analytics ? $request->google_analytics : "";

                $settings->save();

            }


            $settings = Settings::where('key', 'header_scripts')->first();

            if ($settings) {

                $settings->value = $request->header_scripts ? $request->header_scripts : "";

                $settings->save();

            }


            $settings = Settings::where('key', 'body_scripts')->first();

            if ($settings) {

                $settings->value = $request->body_scripts ? $request->body_scripts : "";

                $settings->save();

            }

        return redirect(route('clear-cache'))->with('flash_success' , tr('email_settings_success'));
    }

    public function settings() {

        $settings = array();

        $result = EnvEditorHelper::getEnvValues();

        $languages = Language::where('status', DEFAULT_TRUE)->get();

        return view('admin.settings.settings')
            ->with('settings' , $settings)
            ->with('result', $result)
            ->withPage('settings')
            ->with('sub_page','site_settings')
            ->with('languages' , $languages); 
    
    }

    /**
    * Functiont Name: home_page_settings()
    * 
    * @uses: User home page content with image uploading page settings
    * 
    * @created Maheswari
    *
    * @editd Maheswari
    *
    * @param Get the route of home page setting option
    *
    * @return Html page
    */

    public function home_page_settings(){

        return view('admin.settings.home_page')
                ->with('page','settings')
                ->with('sub_page','home_page_settings');

    }

    public function payment_settings() {

        $settings = array();

        return view('admin.payment-settings')->with('settings' , $settings)->withPage('payment-settings')->with('sub_page',''); 
    }

    public function theme_settings() {

        $settings = array();

        $settings[] =  Setting::get('theme');

        if(Setting::get('theme')!= 'default') {
            $settings[] = 'default';
        }

        if(Setting::get('theme')!= 'teen') {
            $settings[] = 'teen';
        }

        return view('admin.theme.theme-settings')->with('settings' , $settings)->withPage('theme-settings')->with('sub_page',''); 
    }

    public function settings_process(Request $request) {

        $settings = Settings::all();

        $check_streaming_url = "";

        $refresh = "";

        if($settings) {

            foreach ($settings as $setting) {

                $key = $setting->key;
               
                if($setting->key == 'site_icon') {

                    if($request->hasFile('site_icon')) {
                        
                        if($setting->value) {
                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('site_icon'));
                    
                    }
                    
                } else if($setting->key == 'site_logo') {

                    if($request->hasFile('site_logo')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('site_logo'));
                    }

                } else if($setting->key == 'home_page_bg_image') {

                    if($request->hasFile('home_page_bg_image')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('home_page_bg_image'));
                    }

                } else if($setting->key == 'common_bg_image') {

                    if($request->hasFile('common_bg_image')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('common_bg_image'));
                    }

                } else if($setting->key == 'streaming_url') {

                    if($request->has('streaming_url') && $request->streaming_url != $setting->value) {

                        if(check_nginx_configure()) {
                            $setting->value = $request->streaming_url;
                        } else {
                            $check_streaming_url = " !! ====> Please Configure the Nginx Streaming Server.";
                        }
                    }  

                } else if($setting->key == "theme") {

                    if($request->has('theme')) {
                        change_theme($setting->value , $request->$key);
                        $setting->value = $request->theme;
                    }

                } else if($setting->key == 'default_lang') {

                    if ($request->default_lang != $setting->value) {

                        $refresh = $request->default_lang;

                    }

                    $setting->value = $request->$key;

                } else if($setting->key == "admin_commission") {

                    $setting->value = $request->has('admin_commission') ? ($request->admin_commission < 100 ? $request->admin_commission : 100) : $setting->value;

                    $user_commission = $setting->value < 100 ? 100 - $setting->value : 0;

                    $user_commission_details = Settings::where('key' , 'user_commission')->first();

                    if($user_commission_details) {

                        $user_commission_details->value = $user_commission;


                        $user_commission_details->save();
                    }


                } else if($setting->key == 'site_name') {

                    if($request->has('site_name')) {

                        $site_name  = preg_replace("/[^A-Za-z0-9]/", "", $request->site_name);

                        \Enveditor::set("SITENAME", $site_name);

                        $setting->value = $request->site_name;

                    }

                } elseif($setting->key == 'home_browse_mobile_image'){

                    if($request->hasFile('home_browse_mobile_image')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('home_browse_mobile_image'));
                    }

                } elseif ($setting->key == 'home_cancel_image') {

                    if($request->hasFile('home_cancel_image')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('home_cancel_image'));
                    }
                   
                } elseif ($setting->key == 'home_browse_desktop_image') {
                  
                    if($request->hasFile('home_browse_desktop_image')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('home_browse_desktop_image'));
                    }

                } elseif ($setting->key == 'home_browse_tv_image') {

                    if($request->hasFile('home_browse_tv_image')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('home_browse_tv_image'));
                    }

                } else {

                    if (isset($_REQUEST[$key])) {

                        $setting->value = $request->$key;

                    }

                }

                $setting->save();
            
            }

        }

        // if($request->has('app_url')) {

        //      \Enveditor::set("ANGULAR_SITE_URL",$request->app_url);

        // }

        if ($refresh) {
            $fp = fopen(base_path() .'/config/new_config.php' , 'w');
            fwrite($fp, "<?php return array( 'locale' => '".$refresh."', 'fallback_locale' => '".$refresh."');?>");
            fclose($fp);
            \Log::info("Key : ".config('app.locale'));
            
        }
        
        
        $message = "Settings Updated Successfully"." ".$check_streaming_url;
        
        return redirect(route('clear-cache'))->with('setting', $settings)->with('flash_success', $message);    
    
    }

    public function help() {
        return view('admin.static.help')->withPage('help')->with('sub_page' , "");
    }

    /**
     * Function: pages()
     * 
     * @uses used to list the pages
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return pages management view page
     */

    public function pages() {

        $data = Page::orderBy('created_at' , 'desc')->paginate(10);

        return view('admin.pages.index')->with('page',"viewpages")->with('sub_page','view_pages')->with('data',$data);
    }

    /**
     * function page_create()
     *
     * Used to create page
     *
     */

    public function page_create() {

        // $pages = count(Page::get()) > 0 ? Page::get()->pluck('type')->toArray() : [];

        // $page_ids = $pages ? explode(',',implode(',', $pages)) : [];

        return view('admin.pages.create')->with('page' , 'viewpages')->with('sub_page',"add_page");
    }

    public function page_edit($id) {

        $data = Page::find($id);

        if($data) {
            return view('admin.pages.edit')->withPage('viewpage')->with('sub_page',"view_pages")
                    ->with('data',$data);
        } else {
            return back()->with('flash_error',tr('something_error'));
        }
    }

    public function page_view(Request $request) {

        $data = Page::find($request->id);

        if($data) {
            return view('admin.pages.view')->withPage('viewpage')->with('sub_page',"view_pages")
                    ->with('data',$data);
        } else {
            return back()->with('flash_error',tr('something_error'));
        }
    }

    

    public function page_save(Request $request) {

        if($request->has('id')) {
            $validator = Validator::make($request->all() , array(
               // 'title' => '',
                'heading' => 'required',
                'description' => 'required'
            ));
        } else {
            $validator = Validator::make($request->all() , array(
                'type' => 'required',
               // 'title' => 'required|max:255|unique:pages,deleted_at,NULL',
                'heading' => 'required|max:255',
                'description' => 'required',
            ));
        }

        if($validator->fails()) {
            $error = implode(',',$validator->messages()->all());
            return back()->with('flash_error',$error);
        } else {

            if($request->has('id')) {
                $pages = Page::find($request->id);

            } else {
                if(Page::count() < Setting::get('no_of_static_pages')) {

                    if($request->type != 'others') {
                        $check_page_type = Page::where('type',$request->type)->first();
                        if($check_page_type){
                            return back()->with('flash_error',"You have already created $request->type page");
                        }
                    }
                    
                    $pages = new Page;
                    
                }else {
                    return back()->with('flash_error',tr('you_cannot_create_more'));
                }
                
            }

            if($pages) {

                $pages->type = $request->type ? $request->type : $pages->type;
               //  $pages->title = $request->title ? $request->title : $pages->title;
                $pages->heading = $request->heading ? $request->heading : $pages->heading;
                $pages->description = $request->description ? $request->description : $pages->description;
                $pages->save();
            }
            if($pages) {
                return back()->with('flash_success',tr('page_create_success'));
            } else {
                return back()->with('flash_error',tr('something_error'));
            }
        }
    }

    public function page_delete($id) {

        $page = Page::where('id',$id)->delete();

        if($page) {
            return back()->with('flash_success',tr('page_delete_success'));
        } else {
            return back()->with('flash_error',tr('something_error'));
        }
    }


    public function custom_push() {

        return view('admin.static.push')->with('title' , "Custom Push")->with('page' , "custom-push");

    }

    public function custom_push_process(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array( 'message' => 'required')
        );

        if( $validator->fails() ) {

            $error = $validator->messages()->all();

            return back()->with('flash_error',$error);

        } else {

            $message = $request->message;

            $title = Setting::get('site_name');

            $message = $message;
            
            $id = 'all';

            $android_register_ids = User::where('is_activated' , USER_APPROVED)->where('device_token' , '!=' , "")->where('device_type' , DEVICE_ANDROID)->where('push_status' , ON)->pluck('device_token')->toArray();

            // Log::info("android_register_ids".print_r($android_register_ids , true));

            PushRepo::push_notification_android($android_register_ids , $title , $message);

            $ios_register_ids = User::where('is_activated' , USER_APPROVED)->where('device_type' , 'DEVICE_IOS')->where('push_status' , ON)->select('device_token' , 'id as user_id')->get();

            PushRepo::push_notification_ios($ios_register_ids , $title , $message);

            return back()->with('flash_success' , tr('push_send_success'));
        }
    }

    /**
     * Function Name : spam_videos()
     *
     * Description: Load all the videos from flag table
     *
     * @created Maheswari
     *
     * @edited vidhya R
     *
     * @param Get the flag details in groupby video_id
     *
     * @return all the spam videos
     */
    public function spam_videos() {

        // Load all the videos from flag table
        $model = Flag::groupBy('video_id')->paginate(10);

        // Return array of values
        return view('admin.spam_videos.spam_videos')->with('model' , $model)
                        ->with('page' , 'videos')
                        ->with('sub_page' , 'spam_videos');
    }

    /**
     * Function Name : spam_videos_user_reports()
     *
     * Description: Load the flags based on the video id
     *
     * @create Maheswari
     *
     * @edited Maheswari
     *
     * @param integer $id Video id
     *
     * @return all the spam videos in user reports
    */
    public function spam_videos_user_reports($id) {

        if(!$id) {
           
            return redirect()->route('admin.spam-videos')->with('flash_error',tr('spam_video_id_error'));
        }

        $video_details = AdminVideo::find($id);

        if($video_details) {

            // Load all the users based on the selected 

            $model = Flag::where('video_id', $id)->paginate(10);

            // Return array of values

            return view('admin.spam_videos.user_report')
                        ->with('model' , $model)
                        ->with('video_details' , $video_details)
                        ->with('page' , 'videos')
                        ->with('sub_page' , 'spam_videos');
        } else {
            return redirect()->route('admin.spam-videos')->with('flash_error',tr('spam_video_id_error'));
        }
       
    }

    /**
    * Function:delete_spam() 
    *
    * Description: Delete the spam details
    *
    * @created Maheswari
    *
    *@edited Maheswari
    *
    * @param Flag id integer 
    *
    * @return html page with delete success message
    */   
    public function delete_spam($id){

        if($id){

                $flag_detail = Flag::find($id);

                if($flag_detail){

                    $flag_detail->delete();

                    return back()->with('flash_success',tr('spam_deleted'));

                } else{

                    return back()->with('flash_error',tr('spam_details_not_found'));
                }
        } else{
            return back()->with('flash_error',tr('spam_video_id_error'));
        }
    }
    
    public function revenue_system() {

        $total_sub_revenue = UserPayment::sum('amount');

        $total_revenue = $total_sub_revenue ? $total_sub_revenue : 0;

        // Video Payments

        $live_video_amount = PayPerView::sum('amount');

        $video_amount = $live_video_amount ? $live_video_amount : 0;

        $live_user_amount = PayPerView::sum('moderator_amount');

        $user_amount = $live_user_amount ? $live_user_amount : 0;

        $final = PayPerView::where('admin_amount', '=', 0)->where('moderator_amount', '=', 0)->sum('amount');

        $live_admin_amount = PayPerView::sum('admin_amount') ;

        $admin_amount = $live_admin_amount + $final;

        $video_amount = $live_video_amount;

        return view('admin.payments.revenue-dashboard')
                ->with('total_revenue',$total_revenue)
                ->with('video_amount', $video_amount)
                ->with('user_amount', $user_amount)
                ->with('admin_amount', $admin_amount ? $admin_amount : 0)
                ->with('page', 'payments')
                ->with('sub_page', 'revenue_system');
    }

    /**
     * Function Name : video_payments()
     *
     * To get payments based on the video subscription
     * 
     * @created Shobana C
     *
     * @edited Vidhya R
     *
     * @return array of payments
     */

    public function video_payments() {

        $payments = PayPerView::orderBy('created_at' , 'desc')->paginate(10);


        $payment_count = PayPerView::count();
      
        return view('admin.payments.video-payments')
                    ->with('payment_count',$payment_count)
                    ->with('data' , $payments)
                    ->withPage('payments')
                    ->with('sub_page','video-subscription'); 
    }

    /**
     * Function: save_video_payment
     *
     * @uses : To save the payment details
     *
     * @param integer $id Video Id
     *
     * @param object  $request Object (Post Attributes)
     *
     * @return flash message
     */

    public function save_video_payment($id, Request $request){

        if($request->amount > 0) {

            $model = AdminVideo::find($id);

            // dd($request->all(),$model);

            // Get post attribute values and save the values
            if ($model) {

                $request->request->add([
                    'is_pay_per_view' => PPV_ENABLED
                ]);

                if ($data = $request->all()) {
                    // Update the post
                    if (AdminVideo::where('id', $id)->update($data)) {
                        // Redirect into particular value
                        return back()->with('flash_success', tr('payment_added'));       
                    } 
                }
            }
            return back()->with('flash_error', tr('admin_published_video_failure'));

        } else {

            return back()->with('flash_error',tr('add_ppv_amount'));
        }
    }

    /**
     * Function : save_common_settings
     *
     * @descritpion: Save the values in env file
     *
     * @created
     *
     * @edited vidhya R
     *
     * @param object $request Post Attribute values
     * 
     * @return settings values
     */
    
    public function save_common_settings(Request $request) {

        $admin_id = \Auth::guard('admin')->user()->id;

        foreach ($request->all() as $key => $data) {

            \Enveditor::set($key,$data);
        }

        $settings = Settings::all();

        $check_streaming_url = "";

        $refresh = "";

        if($settings) {

            foreach ($settings as $setting) {

                $key = $setting->key;
               
                if($setting->key == 'site_icon') {

                    if($request->hasFile('site_icon')) {
                        
                        if($setting->value) {
                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('site_icon'));
                    
                    }
                    
                } else if($setting->key == 'site_logo') {

                    if($request->hasFile('site_logo')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('site_logo'));
                    }

                } else if($setting->key == 'home_page_bg_image') {

                    if($request->hasFile('home_page_bg_image')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('home_page_bg_image'));
                    }

                } else if($setting->key == 'common_bg_image') {

                    if($request->hasFile('common_bg_image')) {

                        if($setting->value) {

                            Helper::delete_picture($setting->value, "/uploads/");
                        }

                        $setting->value = Helper::normal_upload_picture($request->file('common_bg_image'));
                    }

                } else if($setting->key == "theme") {

                    if($request->has('theme')) {
                        change_theme($setting->value , $request->$key);
                        $setting->value = $request->theme;
                    }

                } else if($setting->key == 'default_lang') {

                        if ($request->default_lang != $setting->value) {

                            $refresh = $request->default_lang;

                        }

                    $setting->value = $request->$key;

                } else if($setting->key == "admin_commission") {

                    $setting->value =  $request->has('admin_commission') ? ($request->admin_commission < 100 ? $request->admin_commission : 100) : $setting->value;

                    $user_commission = $setting->value < 100 ? 100 - $setting->value : 0;

                    $user_commission_details = Settings::where('key' , 'user_commission')->first();

                    if(count($user_commission_details) > 0) {

                        $user_commission_details->value = $user_commission;


                        $user_commission_details->save();
                    }


                } else {

                    if (isset($_REQUEST[$key])) {

                        $setting->value = $request->$key;

                    }

                }

                $setting->save();
            
            }

        }

        return redirect(route('clear-cache'))->with('setting', $settings);
    
    }

    /**
     * Function : remove_payper_view
     *
     * @descritpion: To remove pay per view
     *
     * @created
     *
     * @edited vidhya R
     *
     * @param object $request Post Attribute values
     * 
     * @return falsh success
     */

    public function remove_payper_view($id) {
        
        // Load video model using auto increment id of the table
        $model = AdminVideo::find($id);
        if ($model) {
            $model->amount = DEFAULT_FALSE;
            $model->type_of_subscription = DEFAULT_FALSE;
            $model->type_of_user = DEFAULT_FALSE;
            $model->is_pay_per_view = PPV_DISABLED;
            $model->save();
            if ($model) {
                return back()->with('flash_success' , tr('removed_pay_per_view'));
            }
        }
        return back()->with('flash_error' , tr('admin_published_video_failure'));
    }

    public function subscriptions() {

        $data = Subscription::orderBy('created_at','desc')->whereNotIn('status', [DELETE_STATUS])->paginate(10);

        return view('admin.subscriptions.index')->withPage('subscriptions')
                        ->with('data' , $data)
                        ->with('sub_page','view-subscription');        

    }

    public function user_subscriptions($id) {

        $data = Subscription::orderBy('created_at','desc')->whereNotIn('status', [DELETE_STATUS])->get();

         $payments = []; 

        if($id) {

            $payments = UserPayment::orderBy('created_at' , 'desc')
                        ->where('user_id' , $id)->get();

        }


        return view('admin.subscriptions.user_plans')->withPage('users')
                        ->with('subscriptions' , $data)
                        ->with('id', $id)
                        ->with('sub_page','users')->with('payments', $payments);        

    }

    public function user_subscription_save($s_id, $u_id) {

        // Load 

        // $load = UserPayment::where('user_id', $u_id)->orderBy('created_at', 'desc')->first();

        $load = UserPayment::where('user_id' , $u_id)->where('status', DEFAULT_TRUE)->orderBy('id', 'desc')->first();

        $payment = new UserPayment();

        $payment->subscription_id = $s_id;

        $payment->user_id = $u_id;

        $payment->subscription_amount = ($payment->subscription) ? $payment->subscription->amount  : 0;

        $payment->amount = ($payment->subscription) ? $payment->subscription->amount  : 0;

        $payment->payment_id = ($payment->amount > 0) ? uniqid(str_replace(' ', '-', 'PAY')) : 'Free Plan'; 

        /*if ($load) {
            $payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$payment->subscription->plan} months", strtotime($load->expiry_date)));
        } else {
            $payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$payment->subscription->plan} months"));
        }*/


        if ($load) {

            if (strtotime($load->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

             $payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$payment->subscription->plan} months", strtotime($load->expiry_date)));

            } else {

                $payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$payment->subscription->plan} months"));

            }

        } else {

            $payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$payment->subscription->plan} months"));

        }


        $payment->status = DEFAULT_TRUE;

        if ($payment->save())  {

            $payment->user->user_type = DEFAULT_TRUE;

            if ($payment->user->save()) {

                return back()->with('flash_success', tr('subscription_applied_success'));

            }

        }

         return back()->with('flash_error', tr('went_wrong'));

    }

    public function subscription_create() {

        return view('admin.subscriptions.create')->with('page' , 'subscriptions')
                    ->with('sub_page','subscriptions-add');
    }

    public function subscription_edit($unique_id) {

        $data = Subscription::where('unique_id' ,$unique_id)->first();

        return view('admin.subscriptions.edit')->withData($data)
                    ->with('sub_page','subscriptions-view')
                    ->with('page' , 'subscriptions ');

    }

    public function subscription_save(Request $request) {

        $validator = Validator::make($request->all(),[
                'title' => 'required|max:255',
                'plan' => 'required|numeric|min:1|max:12',
                'amount' => 'required|numeric',
                'no_of_account'=>'required|numeric|min:1',
        ]);
        
        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return back()->with('flash_error', $error_messages);

        } else {

            if($request->popular_status) {
                Subscription::where('popular_status' , 1)->update(['popular_status' => 0]);
            }


            if($request->id != '') {

                $model = Subscription::find($request->id);

                $model->update($request->all());

            } else {
                $model = Subscription::create($request->all());
                $model->status = 1;
                $model->popular_status = $request->popular_status ? 1 : 0;
                $model->unique_id = $model->title;
                $model->no_of_account = $request->no_of_account;
                $model->save();
            }
        
            if($model) {
                return redirect(route('admin.subscriptions.view', $model->unique_id))->with('flash_success', $request->id ? tr('subscription_update_success') : tr('subscription_create_success'));

            } else {
                return back()->with('flash_error',tr('admin_not_error'));
            }
        }
    
        
    }

    /** 
     * 
     * Subscription View
     *
     */

    public function subscription_view($unique_id) {

        if($data = Subscription::where('unique_id' , $unique_id)->first()) {

            $earnings = $data->userSubscription()->where('status' , 1)->sum('amount');

            $total_subscribers = $data->userSubscription()->where('status' , 1)->count();

            return view('admin.subscriptions.view')
                        ->with('data' , $data)
                        ->withPage('subscriptions')
                        ->with('total_subscribers', $total_subscribers)
                        ->with('earnings', $earnings)
                        ->with('sub_page','subscriptions-view');

        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
   
    }


    public function subscription_delete(Request $request) {

        if($data = Subscription::where('id',$request->id)->first()) {

            $data->status = DELETE_STATUS;

            $data->save();

            return back()->with('flash_success',tr('subscription_delete_success'));

        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
        
    }

    /** 
     * Subscription status change
     * 
     *
     */

    public function subscription_status($unique_id) {

        if($data = Subscription::where('unique_id' , $unique_id)->first()) {

                $data->status  = $data->status ? 0 : 1;

                $data->save();

                return back()->with('flash_success' , $data->status ? tr('subscription_approve_success') : tr('subscription_decline_success'));
        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    /** 
     * Subscription Popular status change
     * 
     *
     */

    public function subscription_popular_status($unique_id) {

        if($data = Subscription::where('unique_id' , $unique_id)->first()) {

            Subscription::where('popular_status' , 1)->update(['popular_status' => 0]);

            $data->popular_status  = $data->popular_status ? 0 : 1;

            $data->save();

            return back()->with('flash_success' , $data->popular_status ? tr('subscription_popular_success') : tr('subscription_remove_popular_success'));
                
        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

    /** 
     * View list of users based on the selected Subscription
     *
     */

    public function subscription_users($id) {
        
        $user_ids = [];

        $users = UserPayment::where('subscription_id' , $id)->select('user_id')->get();

        foreach ($users as $key => $value) {

            $user_ids[] = $value->user_id;
        }

        $subscription = Subscription::find($id);

        $data = User::whereIn('id' , $user_ids)->orderBy('created_at','desc')->paginate(10);

        return view('admin.users.users')
                    ->withPage('users')
                    ->with('users' , $data)
                    ->with('sub_page','view-user')
                    ->with('subscription' , $subscription);
    }

    /**
     * Function : users_sub_profiles
     *
     * @descritpion: list the sub profiles based on the selected user
     *
     * @created Shobana C
     *
     * @edited vidhya R
     *
     * @param -
     * 
     * @return list of sub profiles page
     */

    public function users_sub_profiles($id) {

        if(!$id) {
            return redirect()->route('admin.users')->with('flash_error' , tr('user_id_not_found'));
        }

        $user_details = User::find($id);

        if(count($user_details) == 0) {

            return redirect()->route('admin.users')->with('flash_error' , tr('user_not_found'));

        }

        $sub_profiles = SubProfile::where('user_id', $id)->orderBy('created_at','desc')->paginate(10);

        return view('admin.users.sub_profiles')
                        ->withPage('users')
                        ->with('sub_page','view-user')
                        ->with('sub_profiles' , $sub_profiles)
                        ->with('user_details' , $user_details);


    }

    public function moderators_redeem_requests(Request $request) {

        $base_query = RedeemRequest::orderBy('updated_at' , 'desc');

        $moderator = [];

        if($request->id) {

            $base_query = $base_query->where('moderator_id' , $request->id);

            $moderator = Moderator::find($request->id);
        }

        $data = $base_query->get();

        return view('admin.moderators.redeems')->withPage('redeems')->with('sub_page' , 'redeems')->with('data' , $data)->with('moderator' , $moderator);
    
    }

    /**
     * Function: moderators_redeems_payout_direct()
     * 
     * @uses used to payout for the selected redeem request with direct payment
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return redirect to view page with success/failure message
     */

    public function moderators_redeems_payout_direct(Request $request) {

        $validator = Validator::make($request->all() , [
            'redeem_request_id' => 'required|exists:redeem_requests,id',
            'paid_amount' => 'required', 
            ]);

        if($validator->fails()) {

            return redirect()->route('admin.moderators.redeems')->with('flash_error' , $validator->messages()->all())->withInput();

        } else {

            $redeem_request_details = RedeemRequest::find($request->redeem_request_id);

            if($redeem_request_details) {

                if($redeem_request_details->status == REDEEM_REQUEST_PAID ) {

                    return redirect()->route('admin.moderators.redeems')->with('flash_error' , tr('redeem_request_status_mismatch'));

                } else {

                    $redeem_request_details->paid_amount = $redeem_request_details->paid_amount + $request->paid_amount;

                    $redeem_request_details->status = REDEEM_REQUEST_PAID;

                    $redeem_request_details->payment_mode = "direct";

                    $redeem_request_details->save();

                
                    $redeem = Redeem::where('moderator_id', $redeem_request_details->moderator_id)->first();

                    $redeem->paid += $request->paid_amount;

                    $redeem->remaining = $redeem->total_moderator_amount - $redeem->paid;

                    $redeem->save();

                    if ($redeem_request_details->moderator) {

                        $redeem_request_details->moderator->paid_amount += $request->paid_amount;

                        $redeem_request_details->moderator->remaining_amount = $redeem->total_moderator_amount - $redeem->paid;

                        $redeem_request_details->moderator->save();
                    
                    }

                    return redirect()->route('admin.moderators.redeems')->with('flash_success' , tr('action_success'));

                }

            } else {
                return redirect()->route('admin.moderators.redeems')->with('flash_error' , tr('something_error'));
            }
        }

    }

    /**
     * Function: moderators_payout_invoice()
     * 
     * @uses used to list the categories
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return redirect to view page with success/failure message
     */

    public function moderators_redeems_payout_invoice(Request $request) {

        $validator = Validator::make($request->all() , [
            'redeem_request_id' => 'required|exists:redeem_requests,id',
            'paid_amount' => 'required', 
            'moderator_id' => 'required'
            ]);

        if($validator->fails()) {

            return redirect()->route('admin.moderators.redeems')
                            ->with('flash_error' , $validator->messages()->all())
                            ->withInput();

        } else {

            $redeem_request_details = RedeemRequest::find($request->redeem_request_id);

            if($redeem_request_details) {

                if($redeem_request_details->status == REDEEM_REQUEST_PAID ) {

                    return redirect()->route('admin.moderators.redeems')->with('flash_error' , tr('redeem_request_status_mismatch'));

                } else {

                    $invoice_data['moderator_details'] = $moderator_details = Moderator::find($request->moderator_id);

                    $invoice_data['redeem_request_id'] = $request->redeem_request_id;

                    $invoice_data['redeem_request_status'] = $redeem_request_details->status;

                    $invoice_data['moderator_id'] = $request->moderator_id;

                    $invoice_data['item_name'] = Setting::get('site_name')." - Checkout to"."$moderator_details ? $moderator_details->name : -";

                    $invoice_data['payout_amount'] = $request->paid_amount;

                    $data = json_decode(json_encode($invoice_data));

                    return view('admin.moderators.payout')->with('data' , $data)->withPage('moderators')->with('sub_page' , 'moderators');

                }
            
            } else {
                return redirect()->route('admin.moderators.redeems')->with('flash_error' , tr('redeem_not_found'));

            }
        }

    }

    /**
     * Function: moderators_redeems_payout_response()
     * 
     * @uses used to get the response from paypal checkout
     *
     * @created vidhya R
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return redirect to view page with success/failure message
     */

    public function moderators_redeems_payout_response(Request $request) {

        $validator = Validator::make($request->all() , [
            'redeem_request_id' => 'required|exists:redeem_requests,id',
            ]);

        if($validator->fails()) {

            return redirect()->route('admin.moderators.redeems')->with('flash_error' , $validator->messages()->all())->withInput();

        } else {

            if($request->success == false) {

                return redirect()->route('admin.moderators.redeems')->with('flash_error' , tr('redeem_paypal_cancelled'));

            }

            $redeem_request_details = RedeemRequest::find($request->redeem_request_id);

            if($redeem_request_details) {

                if($redeem_request_details->status == REDEEM_REQUEST_PAID ) {

                    return redirect()->route('admin.moderators.redeems')->with('flash_error' , tr('redeem_request_status_mismatch'));

                } else {

                    $redeem_request_details->paid_amount = $redeem_request_details->paid_amount + $request->paid_amount;

                    $redeem_request_details->status = REDEEM_REQUEST_PAID;

                    $redeem_request_details->payment_mode = PAYPAL;

                    $redeem_request_details->save();

                
                    $redeem = Redeem::where('moderator_id', $redeem_request_details->moderator_id)->first();

                    $redeem->paid += $request->paid_amount;

                    $redeem->remaining = $redeem->total_moderator_amount - $redeem->paid;

                    $redeem->save();

                    if ($redeem_request_details->moderator) {

                        $redeem_request_details->moderator->paid_amount += $request->paid_amount;

                        $redeem_request_details->moderator->remaining_amount = $redeem->total_moderator_amount - $redeem->paid;

                        $redeem_request_details->moderator->save();
                    
                    }

                    return redirect()->route('admin.moderators.redeems')->with('flash_success' , tr('action_success'));

                }
            
            } else {
                return redirect()->route('admin.moderators.redeems')->with('flash_error' , tr('redeem_not_found'));

            }
        }

    }

    /**
     * Function Name : genre_position()
     *
     * Change position of the genre
     *
     * @param object $request - Genre id & position of the genre
     *
     * @created - Shobana Chandrasekar
     *
     * @updated - Shobana Chandrasekar
     *
     * @return response of success/failure message
     */
    public function genre_position(Request $request) {

        try {

            DB::beginTransaction();

            $model = Genre::find($request->genre_id);

            if ($model) {

                $changing_row_position = $model->position;

                $change_genre = Genre::where('position', $request->position)->where('sub_category_id', $model->sub_category_id)->where('is_approved', DEFAULT_TRUE)->first();

                if ($change_genre) {

                    $new_row_position = $change_genre->position;

                    $model->position = $new_row_position;

                    if ($model->save()) {

                        $change_genre->position = $changing_row_position;

                        if ($change_genre->save()) {


                        } else {

                            throw new Exception(tr('genre_not_saved'));

                        }

                    } else {

                        throw new Exception(tr('genre_not_saved'));
                        
                    }

                } else {

                    throw new Exception( tr('given_position_not_exits'));
                }

            } else {

                throw new Exception( tr('genre_not_found'));
                
            }

            DB::commit();

            return back()->with('flash_success', tr('genre_position_updated_success'));

        } catch (Exception $e) {

            DB::rollback();

            return back()->with('flash_error', $e->getMessage());

        }

    }

    /**
     * Function Name : video_position()
     *
     * Change position of the video based on genres
     *
     * @param object $request - Genre id & position of the genre
     *
     * @return response of success/failure message
     */
    public function video_position(Request $request) {

        try {

            DB::beginTransaction();

            $model = AdminVideo::find($request->video_id);

            if ($model) {

                $changing_row_position = $model->position;

                $change_video = AdminVideo::where('position', $request->position)
                    ->where('genre_id', $model->genre_id)
                    ->where('is_approved', DEFAULT_TRUE)
                    ->where('status', DEFAULT_TRUE)
                    ->first();

                if ($change_video) {

                    $new_row_position = $change_video->position;

                    $model->position = $new_row_position;

                    if ($model->save()) {

                        $change_video->position = $changing_row_position;

                        if ($change_video->save()) {


                        } else {

                            throw new Exception(tr('video_not_saved'));

                        }

                    } else {

                        throw new Exception(tr('video_not_saved'));
                        
                    }

                } else {

                    throw new Exception( tr('given_position_not_exits'));
                }

            } else {

                throw new Exception( tr('video_not_found'));
                
            }

            DB::commit();

            return back()->with('flash_success', tr('video_position_updated_success'));

        } catch (Exception $e) {

            DB::rollback();

            return back()->with('flash_error', $e->getMessage());

        }

    }

    /**
     * Function Name : templates()
     *
     * To display a templates of the page
     *
     * @param object $request - -
     *
     * @return response of list page
     */
    public function templates(Request $request) {

        $templates = EmailTemplate::orderBy('created_at', 'desc')->get();

        return view('admin.email_templates.index')
            ->with('templates', $templates)
            ->with('page', 'email_templates')
            ->with('sub_page', 'email_templates');

    }

    /**
     * Function Name : edit_template()
     *
     * To display a edit template page
     *
     * @param object $request - id
     *
     * @return response of view page
     */
    public function edit_template(Request $request) {

        $template = EmailTemplate::find($request->id);

        $template_types = [USER_WELCOME => tr('user_welcome_email'), 
                            ADMIN_USER_WELCOME => tr('admin_created_user_welcome_mail'), 
                            FORGOT_PASSWORD => tr('forgot_password'), 
                            MODERATOR_WELCOME=>tr('moderator_welcome'), 
                            PAYMENT_EXPIRED=>tr('payment_expired'), 
                            PAYMENT_GOING_TO_EXPIRY=>tr('payment_going_to_expiry'), 
                            NEW_VIDEO=>tr('new_video'), 
                            EDIT_VIDEO=>tr('edit_video')];

        if($template) {

            return view('admin.email_templates.template')
                ->with('template', $template)
                ->with('template_types', $template_types)
                ->with('page', 'email_templates')
                ->with('sub_page', 'create_template');
        } else {

            return back()->with('flash_error', tr('template_not_found'));
        }
    } 

    /**
     * Function Name : view_template()
     *
     * To display a view template page
     *
     * @param object $request - id
     *
     * @return response of view page
     */
    public function view_template(Request $request) {

        $template = EmailTemplate::find($request->id);

        if($template) {

            return view('admin.email_templates.view')->with('model', $template)->with('page', 'email_templates')->with('sub_page', 'templates');
        } else {

            return back()->with('flash_error', tr('template_not_found'));
        }
    } 

    /**
     * Function Name : save_template()
     *
     * To save the template details
     *
     * @param object $request - id
     *
     * @return response of view page
     */
    public function save_template(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'template_type'=>'required|in:'.USER_WELCOME.','.ADMIN_USER_WELCOME.','.FORGOT_PASSWORD.','.MODERATOR_WELCOME.','. PAYMENT_EXPIRED.','.PAYMENT_GOING_TO_EXPIRY.','.NEW_VIDEO.','.EDIT_VIDEO,
                'subject'=>'required|max:255',
                'description'=>'required',
            ]);

            $template = $request->id ? EmailTemplate::find($request->id) : new EmailTemplate;

            if($template) {

                $template->subject = $request->subject;
                    
                $template->description = $request->description;

                $template->template_type = $request->template_type;

                $template->status = DEFAULT_TRUE;

                if ($template->save()) {


                } else {

                    throw new Exception(tr('template_not_saved'));

                }

            } else {


                throw new Exception(tr('template_not_found'));
            }

            DB::commit();

            return back()->with('flash_success', $request->id ? tr('template_update_success') : tr('template_create_success'));

        } catch(Exception $e) {

            DB::rollback();

            $message = $e->getMessage();

            return back()->with('flash_error', $message);

        }
    } 

    // Coupons

    /**
    * Function Name: coupon_create()
    *
    * Description: Get the coupon add form fields
    *
    * @created Maheswari
    *
    * @edited Maheswari
    *
    * @param Get the route of add coupon form
    *
    * @return Html form page
    */
    public function coupon_create(){

       return view('admin.coupons.create')
                ->with('page','coupons')
                ->with('sub_page','create');
    }

    /**
    * Function Name: coupon_save()
    *
    * Description: Save/Update the coupon details in database 
    *
    * @created Maheswari
    *
    * @edited Maheswari
    *
    * @param Request to all the coupon details
    *
    * @return add details for success message
    */
    public function coupon_save(Request $request){
        
        $validator = Validator::make($request->all(),[
            'id'=>'exists:coupons,id',
            'title'=>'required',
            'coupon_code'=>$request->id ? 'required|max:10|min:1|unique:coupons,coupon_code,'.$request->id : 'required|unique:coupons,coupon_code|min:1|max:10',
            'amount'=>'required|numeric|min:1|max:5000',
            'amount_type'=>'required',
            'expiry_date'=>'required|date_format:d-m-Y|after:today',
            'no_of_users_limit'=>'required|numeric|min:1|max:1000',
            'per_users_limit'=>'required|numeric|min:1|max:100',
        ]);

        if($validator->fails()){

            $error_messages = implode(',',$validator->messages()->all());

            return back()->with('flash_error',$error_messages);
        }
        if($request->id !=''){
                    
               
                $coupon_detail = Coupon::find($request->id); 

                $message=tr('coupon_update_success');

        } else {

            $coupon_detail = new Coupon;

            $coupon_detail->status = DEFAULT_TRUE;

            $message = tr('coupon_add_success');
        }

        // Check the condition amount type equal zero mean percentage
        if($request->amount_type == PERCENTAGE){

            // Amount type zero must should be amount less than or equal 100 only
            if($request->amount <= 100){

                $coupon_detail->amount_type = $request->has('amount_type') ? $request->amount_type :DEFAULT_FALSE;
 
                $coupon_detail->amount = $request->has('amount') ?  $request->amount : '';

            } else{

                return back()->with('flash_error',tr('coupon_amount_lessthan_100'));
            }

        } else{

            // This else condition is absoulte amount 

            // Amount type one must should be amount less than or equal 5000 only
            if($request->amount <= 5000){

                $coupon_detail->amount_type=$request->has('amount_type') ? $request->amount_type : DEFAULT_TRUE;

                $coupon_detail->amount=$request->has('amount') ?  $request->amount : '';

            } else{

                return back()->with('flash_error',tr('coupon_amount_lessthan_5000'));
            }
        }
        $coupon_detail->title=ucfirst($request->title);

        // Remove the string space and special characters
        $coupon_code_format  = preg_replace("/[^A-Za-z0-9\-]+/", "", $request->coupon_code);

        // Replace the string uppercase format
        $coupon_detail->coupon_code = strtoupper($coupon_code_format);

        // Convert date format year,month,date purpose of database storing
        $coupon_detail->expiry_date = date('Y-m-d',strtotime($request->expiry_date));
      
        $coupon_detail->description = $request->has('description')? $request->description : '' ;
         // Based no users limit need to apply coupons
        $coupon_detail->no_of_users_limit = $request->no_of_users_limit;

        $coupon_detail->per_users_limit = $request->per_users_limit;
        
        if($coupon_detail){

            $coupon_detail->save(); 

            return back()->with('flash_success',$message);

        } else {

            return back()->with('flash_error',tr('coupon_not_found_error'));
        }
        
    }

    /**
    * Function Name: coupon_index()
    *
    * Description: Get the coupon details for all 
    *
    * @created Maheswari
    *
    * @edited Maheswari
    *
    * @param Get the coupon list in table
    *
    * @return Html table from coupon list page
    */
    public function coupon_index(){

        $coupons = Coupon::orderBy('updated_at','desc')->paginate(10);

        if($coupons){

            return view('admin.coupons.index')
                ->with('coupons',$coupons)
                ->with('page','coupons')
                ->with('sub_page','view_coupons');
        } else{

            return back()->with('flash_error',tr('coupon_not_found_error'));
        }
    }

    /**
    * Function Name: coupon_edit() 
    *
    * Description: Edit the coupon details and get the coupon edit form for 
    *
    * @created Maheswari
    *
    * @edited Maheswari
    *
    * @param Coupon id
    *
    * @return Get the html form
    */
    public function coupon_edit($id){

        if($id){

            $edit_coupon = Coupon::find($id);

            if($edit_coupon){

                return view('admin.coupons.edit')
                        ->with('edit_coupon',$edit_coupon)
                        ->with('page','coupons')
                        ->with('sub_page','edit_coupons');

            } else{
                return back()->with('flash_error',tr('coupon_not_found_error'));
            }
        }else{

            return back()->with('flash_error',tr('coupon_id_not_found_error'));
        }
    }

    /**
    * Function Name: coupon_delete()
    *
    * Description: Delete the particular coupon detail
    *
    * @created Maheswari
    *
    * @edited Maheswari
    *
    * @param Coupon id
    *
    * @return Deleted Success message
    */
    public function coupon_delete($id){

        if($id){

            $delete_coupon = Coupon::find($id);

            if($delete_coupon){

                $delete_coupon->delete();

                return back()->with('flash_success',tr('coupon_delete_success'));
            } else{

                return back()->with('flash_error',tr('coupon_not_found_error'));
            }

        } else{

            return back()->with('flash_error',tr('coupon_id_not_found_error'));
        }
    }

    /**
    * Function Name: coupon_status_change()
    * 
    * Description: Coupon status for active and inactive update the status function
    *
    * @created Maheswari
    *
    * @edited Maheswari
    *
    * @param Request the coupon id
    *
    * @return Success message for active/inactive
    */
    public function coupon_status_change(Request $request){

        $coupon_status = Coupon::find($request->id);

        if($coupon_status) {

            $coupon_status->status = $request->status;

            $coupon_status->save();

        } else {

            return back()->with('flash_error',tr('coupon_not_found_error'));
        }

        if($request->status == DEFAULT_FALSE){

            $message = tr('coupon_decline_success');

        } else{

            $message = tr('coupon_approve_success');
        }
        return back()->with('flash_success',$message);
    }

    /**
    * Function Name: coupon_view()
    *
    * Description: Get the particular coupon details for view page content
    *
    * @created Maheswari
    *
    * @edited Maheswaari
    *
    * @param Coupon id
    *
    * @return Html view page with coupon detail
    */
    public function coupon_view($id){

        if($id){

            $view_coupon = Coupon::find($id);

            if($view_coupon){

                $user_coupon = UserCoupon::where('coupon_code', $view_coupon->coupon_code)->sum('no_of_times_used');

                return view('admin.coupons.view')
                    ->with('view_coupon',$view_coupon)
                    ->with('page','coupons')
                    ->with('user_coupon', $user_coupon)
                    ->with('sub_page','view_coupons');
            }

        } else{

            return back()->with('flash_error',tr('coupon_id_not_found_error'));
        }
    }

    // Mail Camp
    /**
    * Function Name: create_mailcamp
    *
    * Description: Get the mail camp form in this list
    *
    * @edited Maheswari
    *
    * @created Maheswari
    *
    * @return Html form
    */
    public function create_mailcamp(){

        $users_list = User::select('users.id','users.name','users.email','users.is_activated','users.is_verified','users.amount_paid')->where('is_activated',1)->where('email_notification', 1)->where('is_verified',1)->get();

        $moderator_list = Moderator::select('moderators.id','moderators.name','moderators.email','moderators.is_activated')->where('is_activated',1)->get();

         return view('admin.mail_camp')
        ->with('users_list',$users_list)
        ->with('moderator_list',$moderator_list)
        ->with('page','mail_camp');
    }

    /**
    * Function Name : email_send_process()
    *
    * Description : Get user list from based on to address
    *
    * @edited Maheswari
    *
    * @created Maheswari
    *
    * @param request the mail form fields
    *
    * @return Response is mail send successfull message
    */
    public function email_send_process(Request $request){    
        
        $validator = Validator::make($request->all(),[

            'to'=>'required|in:'.USERS.','.MODERATORS.','.CUSTOM_USERS,
            'users_type'=>'in:'.ALL_USER.','.NORMAL_USERS.','.PAID_USERS.','.SELECT_USERS.','.ALL_MODERATOR.','.SELECT_MODERATOR,
            'subject'=>'required|min:5|max:255',
            'content'=>'required|min:5',
        ]);

       
       if($validator->fails()){

            $error_messages = implode(',',$validator->messages()->all());

            return back()->with('flash_error',$error_messages);
       }
       
        if($request->to == USERS){
             
            if($request->users_type == ALL_USER){

                $user_email = User::select('users.id')->where('is_activated',1)
                                    ->where('is_verified',1)
                                    ->where('email_notification_status',1)
                                    ->pluck('users.id')
                                    ->toArray();

            } else if($request->users_type == NORMAL_USERS){

                $user_email = User::select('users.id')->where('is_activated',1)->where('is_verified',1)->where('email_notification_status',1)->where('user_type',0)->pluck('users.id')->toArray();
                
            } else if($request->users_type == PAID_USERS){
                
                $user_email = User::select('users.id')->where('is_activated',1)->where('user_type',1)->where('email_notification_status',1)->where('is_verified',1)->pluck('users.id')->toArray();
               
            } elseif ($request->users_type == SELECT_USERS) {

                $user_email = $request->select_user;

            } else { 

                return back()->with('flash_error',tr('user_type_not_found'));
            }

        } else if($request->to == MODERATORS) {

            if($request->users_type == ALL_MODERATOR){

                $user_email = Moderator::select('moderators.id')->where('is_activated',1)->pluck('moderators.id')->toArray();

            } else if($request->users_type == SELECT_MODERATOR) {

                $user_email = $request->select_moderator;

            } else{

                return back()->with('flash_error',tr('moderators_not_found_error'));
            }

        } else if($request->to == CUSTOM_USERS){

            $custom_user = $request->custom_user;
            
            if($custom_user !=''){

                $user_email = explode(',', $custom_user);
                
                if(Setting::get('custom_users_count') >= count($user_email)){

                    foreach ($user_email as $key => $value) {   

                    Log::info('Custom Mail list : '.$value);

                        if(!filter_var($value,FILTER_VALIDATE_EMAIL)){

                            //This variable is only for email validate messsage purpose only 
                            $validate_email=0;

                            $invalid_email[] = $value;

                            $message = tr('custom_email_invalid');

                            $invalid_email_address = implode(' , ' , $invalid_email);

                        } else {

                            //This variable is only for email validate messsage purpose only  using
                            $validate_email =1;

                            $subject = $request->subject;
                                
                            $content = $request->content;
                           
                            $page = "emails.send_mail";

                            $email = $value;

                            // Get the custom user name before @ symbol
                            $name =  substr($email, 0, strrpos($email, "@"));
                            
                            $email_data['name'] = $name;

                            $email_data['content']= $content;

                            $email_data['email'] = $value;

                            Helper::send_email($page,$subject,$email,$email_data);
                        }
                        
                    }

                    if($validate_email == 0){

                        return back()->with('flash_success',tr('mail_send_successfully'))->with('flash_error',$invalid_email_address . $message);

                    } else {

                        return back()->with('flash_success',tr('mail_send_successfully'));
                    }

                } else{

                    return back()->with('flash_error',tr('custom_user_count'));
               
                }

            } else {
                return back()->with('flash_error',tr('custom_user_field_required'));
            }
                
        } else { 

            return back()->with('flash_error',tr('user_not_found'));
        }
        
        if(count($user_email)>0) {

            $users_moderator_type = $request->to;

            $subject = $request->subject;
                    
            $content = $request->content;

            dispatch(new SendMailCamp($user_email,$subject,$content,$users_moderator_type));

            return back()->with('flash_success',tr('mail_send_successfully'));

        } else {

            return back()->with('flash_error',tr('details_not_found'));
        }
    } 

   /**
     * Function Name : user_subscription_pause
     *
     * To prevent automatic subscriptioon, user have option to cancel subscription
     *
     * @param object $request - USer details & payment details
     *
     * @return boolean response with message
     */
    public function user_subscription_pause(Request $request) {

        $user_payment = UserPayment::where('user_id', $request->id)->where('status', PAID_STATUS)->orderBy('created_at', 'desc')->first();

        if($user_payment) {

            $user_payment->is_cancelled = AUTORENEWAL_CANCELLED;

            $user_payment->cancel_reason = $request->cancel_reason;

            $user_payment->save();

            return back()->with('flash_success', tr('cancel_subscription_success'));

        } else {

            return back()->with('flash_error', Helper::get_error_message(163));

        }        

    }

    /**
     * Function Name : user_subscription_enable
     *
     * To prevent automatic subscriptioon, user have option to cancel subscription
     *
     * @created shobana Chandrasekar
     *
     * @edited
     *
     * @param object $request - USer details & payment details
     *
     * @return boolean response with message
     */
    public function user_subscription_enable(Request $request) {

        $user_payment = UserPayment::where('user_id', $request->id)->where('status', PAID_STATUS)->orderBy('created_at', 'desc')
            ->where('is_cancelled', AUTORENEWAL_CANCELLED)
            ->first();

        if($user_payment) {

            $user_payment->is_cancelled = AUTORENEWAL_ENABLED;

            $user_payment->save();

            return back()->with('flash_success', tr('autorenewal_enable_success'));

        } else {

            return back()->with('flash_error', Helper::error_message(163));

        }        

    }  

    /**
     * Function Name : admin_videos_create()
     *
     * To display a upload video form
     *
     * @created - Shobana Chandrasekar
     *
     * @updated - - 
     *
     * @param object $request - - 
     *
     * @return response of html page with details
     */
    public function admin_videos_create(Request $request) {

        $categories = Category::where('categories.is_approved' , DEFAULT_TRUE)
                ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                    'categories.is_series' ,'categories.status' , 'categories.is_approved')
                ->leftJoin('sub_categories' , 'categories.id' , '=' , 'sub_categories.category_id')
                ->groupBy('sub_categories.category_id')
                ->where("sub_categories.is_approved", SUB_CATEGORY_APPROVED)
                ->havingRaw("COUNT(sub_categories.id) > 0")
                ->orderBy('categories.name' , 'asc')
                ->get();

        $model = new AdminVideo;

        $model->trailer_video_resolutions = [];

        $model->video_resolutions = [];

        $videoimages = [];

        $video_cast_crews = [];

        $cast_crews = CastCrew::select('id', 'name')->get();

        return view('admin.videos.upload')->with('page', 'videos')
            ->with('categories', $categories)
            ->with('sub_page', 'admin_videos_create')
            ->with('model', $model)
            ->with('videoimages', $videoimages)
            ->with('cast_crews', $cast_crews)
            ->with('video_cast_crews', $video_cast_crews);
    }

    /**
     * Function Name : admin_videos_save()
     *
     * @uses To save a new video as well as updated video details
     *
     * @created: Shobana Chandrasekar
     *
     * @updated: 
     *
     * @param object $request - - 
     *
     * @return response of success/failure page
     */
    public function admin_videos_save(Request $request) {

        // Call video save method of common function video repo

        $response = VideoRepo::video_save($request)->getData();

        return ['response'=>$response];
    }


    /**
     * Function Name : admin_videos_edit()
     *
     * @uses To display a upload video form
     *
     * @created: Shobana Chandrasekar
     *
     * @updated: - 
     *
     * @param object $request - - 
     *
     * @return response of html page with details
     */
    public function admin_videos_edit(Request $request) {

        $model = AdminVideo::where('admin_videos.id' , $request->id)->first();

        if ($model) {

            $categories = Category::where('categories.is_approved' , DEFAULT_TRUE)
                ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                    'categories.is_series' ,'categories.status' , 'categories.is_approved')
                ->leftJoin('sub_categories' , 'categories.id' , '=' , 'sub_categories.category_id')
                ->groupBy('sub_categories.category_id')
                ->where('sub_categories.is_approved' , SUB_CATEGORY_APPROVED)
                ->havingRaw("COUNT(sub_categories.id) > 0")
                ->orderBy('categories.name' , 'asc')
                ->get();

            $sub_categories = SubCategory::where('category_id', '=', $model->category_id)
                            ->leftJoin('sub_category_images' , 'sub_categories.id' , '=' , 'sub_category_images.sub_category_id')
                            ->select('sub_category_images.picture' , 'sub_categories.*')
                            ->where('sub_category_images.position' , 1)
                            ->where('is_approved' , SUB_CATEGORY_APPROVED)
                            ->orderBy('name', 'asc')
                            ->get();

            $model->publish_time = $model->publish_time ? date('d-m-Y H:i:s', strtotime($model->publish_time)) : $model->publish_time;

            $videoimages = get_video_image($model->id);

            $model->video_resolutions = $model->video_resolutions ? explode(',', $model->video_resolutions) : [];

            $model->trailer_video_resolutions = $model->trailer_video_resolutions ? explode(',', $model->trailer_video_resolutions) : [];

            $video_cast_crews = VideoCastCrew::select('cast_crew_id')
                    ->where('admin_video_id', $request->id)
                    ->get()->pluck('cast_crew_id')->toArray();
           
            $cast_crews = CastCrew::select('id', 'name')->get();
           
            return view('admin.videos.upload')->with('page', 'videos')
                ->with('categories', $categories)
                ->with('model', $model)
                ->with('sub_categories', $sub_categories)
                ->with('sub_page', 'admin_videos_create')
                ->with('videoimages', $videoimages)
                ->with('cast_crews',$cast_crews)
                ->with('video_cast_crews', $video_cast_crews);


        } else {

            return back()->with('flash_error', tr('something_error'));

        }

    }

    /** 
     * Function Name : cast_crews_add()
     *
     * @uses To display a form to add a new cast
     *
     * @created: Shobana Chandrasekar
     *
     * @updated:
     *
     * @param  - -
     *
     * @return response of html page with details
     */
    public function cast_crews_add(Request $request) {

        $model = new CastCrew;

        return view('admin.cast_crews.create')->with('page', 'cast-crews')
            ->with('sub_page', 'cast-crew-add')->with('model', $model);

    }

    /**
     * Function Name : cast_crews_edit()
     *
     * @uses Display a form to edit a cast with existing details
     *
     * @created: Shobana Chandrasekar
     *
     * @updated:  
     *
     * @param  string $request - Unique id of the cast and crew
     *
     * @return response of html page with details
     */
    public function cast_crews_edit(Request $request) {

        $model = CastCrew::where('unique_id', $request->id)->first();

        if ($model) {

            return view('admin.cast_crews.edit')->with('page', 'cast-crews')
            ->with('sub_page', 'cast-crew-add')->with('model', $model);

        } else {

            return back()->with('flash_error', tr('cast_crew_not_found'));
        }

    }

    /**
     * Function Name : cast_crews_view()
     *
     * @uses To view the detaisl of cast and crew
     *
     * @created: Shobana Chandrasekar
     *
     * @updated:  
     *
     * @param  string $request - Unique id of the cast and crew
     *
     * @return response of html page with details
     */
    public function cast_crews_view(Request $request) {

        $model = CastCrew::where('unique_id', $request->id)->first();

        if ($model) {

            return view('admin.cast_crews.view')->with('page', 'cast-crews')
            ->with('sub_page', 'cast-crew-index')->with('model', $model);

        } else {

            return back()->with('flash_error', tr('cast_crew_not_found'));
        }

    }

    /**
     * Function Name : cast_crews_index()
     *
     * @uses To list out details of cast and crews
     *
     * @created: Shobana Chandrasekar
     *
     * @updated:
     *
     * @param  - -
     *
     * @return response of html page with details
     */
    public function cast_crews_index(Request $request) {

        $model = CastCrew::orderBy('created_at', 'desc')->paginate(10);
    
        return view('admin.cast_crews.index')->with('page', 'cast-crews')
            ->with('sub_page', 'cast-crew-index')->with('model', $model);

    }

    /**
     * Function Name : cast_crews_delete()
     *
     * @uses To delete a cast and crew based on the unique id 
     *
     * @created: Shobana Chandrasekar
     *
     * @updated: 
     *
     * @param string $request - Unique id of the cast and crew
     *
     * @return response of success/failure message
     */
    public function cast_crews_delete(Request $request) {

        $model = CastCrew::where('unique_id', $request->id)->first();

        if ($model) {

            $image = $model->image;

            if ($model->delete()) {

                if ($image) {

                    Helper::delete_picture($image,  '/uploads/cast_crews/');
                }

                return redirect(route('admin.cast_crews.index'))->with('flash_success', tr('cast_crew_delete_success'));

            }

        } else {

            return back()->with('flash_error', tr('cast_crew_not_found'));
        }

    }

    /**
     * Function Name : cast_crews_status()
     *
     * @uses To change the status of cast details based on cast id (Approve/Decline)
     *
     * @created: Shobana Chandrasekar
     *
     * @updated: 
     *
     * @param string $request - Unique id of the cast and crew
     *
     * @return response of success/failure message
     */
    public function cast_crews_status(Request $request) {

        $model = CastCrew::where('unique_id', $request->id)->first();

        if ($model) {

            $model->status = $model->status == CAST_APPROVED ? CAST_DECLINED : CAST_APPROVED;

            if ($model->save()) {

                if ($model->status == CAST_DECLINED) {

                    if (count($model->videoCastCrews) > 0) {

                        foreach($model->videoCastCrews as $value)
                        {

                            $value->delete();
                            
                        }

                    } 

                }

                return redirect(route('admin.cast_crews.index'))->with('flash_success', $model->status ? tr('cast_crew_approve_success') : tr('cast_crew_decline_success'));

            }

        } else {

            return back()->with('flash_error', tr('cast_crew_not_found'));
        }

    }

    /**
     * Function Name : cast_crews_save()
     *
     * @uses To save the details of the cast and crews
     *
     * @created: Shobana Chandrasekar
     *
     * @updated: Vidhya R 
     *
     * @param string $request - Unique id of the cast and crew
     *
     * @return response of success/failure message
     */
    public function cast_crews_save(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                'id'=>'exists:cast_crews,id',
                'name'=>'required|min:2|max:128',
                'image'=>$request->id ? 'mimes:jpeg,jpg,png' : 'required|mimes:jpeg,png,jpg',
                'description'=>'required'
            ]);

            if ($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error);

            } else {

                $model = $request->id ? CastCrew::where('id', $request->id)->first() : new CastCrew;

                $model->name = $request->name;

                $model->unique_id = $model->name;

                if ($request->hasFile('image')) {

                    if ($request->id) {

                        Helper::delete_picture($model->image, '/uploads/cast_crews/');
                     
                    }

                    $model->image = Helper::normal_upload_picture($request->file('image'), '/uploads/cast_crews/');

                }

                $model->description = $request->description;

                $model->status = DEFAULT_TRUE; // By default it will be 1, future it may vary

                if ($model->save()) {


                } else {

                    throw new Exception(tr('cast_crew_not_saving'));
                    
                }
               

            }

            return redirect(route('admin.cast_crews.view', ['id'=>$model->unique_id]))->with('flash_success', $request->id ? tr('cast_crew_update_success') : tr('cast_crew_create_success'));

        } catch (Exception $e) {

            return back()->with('flash_error', $e->getMessage());

        }
    }

    /**
     * Function Name : gif_generator()
     *
     * @uses Future, Not now - To create a gif based on 3 images
     *
     * @created: Shobana Chandrasekar
     *
     * @edited: Vidhya R
     *
     * @param integer $request - video id
     *
     * @return response of json details
     */
    public function gif_generator(Request $request) {

        $video = AdminVideo::find($request->id);

        if ($video) {

            // Gif Generation Based on three images

            $FFmpeg = new \FFmpeg;

            $FFmpeg
                ->setImage('image2')
                ->setFrameRate(1)
                ->input( public_path()."/uploads/images/video_{$request->video_id}_%03d.png")
                ->setAspectRatio("4:2")
                ->frameRate(30)
                ->output(public_path()."/uploads/gifs/video_{$request->video_id}.gif")
                ->ready();

            $video->video_gif_image = Helper::web_url()."/uploads/gifs/video_{$request->video_id}.gif";

            $video->save();

            return back()->with('flash_success', tr('gif_generate_success'));

        } else {

            return back()->with('flash_error', tr('gif_generate_failure'));

        }

    }


    /**
     * Function Name : clear_login
     *
     * @uses To clear all the logins from all devices
     *
     * @created: Shobana Chandrasekar
     *
     * @updated: Vidhya R
     *
     * @param object $request - User details
     *
     * @return response of success/failure message
     */
    public function clear_login(Request $request) {

        $user = User::find($request->id);

        if ($user) {

            // Delete all the records which is stored before

            UserLoggedDevice::where('user_id', $request->id)->delete();

            $user->logged_in_account = 0;

            $user->save();

            return back()->with('flash_success', tr('user_clear'));

        } else {

            return back()->with('flash_error', tr('user_not_found'));
        }


    }

    /**
     * Function Name : videos_compression_complete()
     *
     * @uses To complete the compressing videos
     *
     * @param integer video id - Video id
     *
     * @created: shobana chandrasekar
     *
     * @updated: Vidhya R
     *
     * @return response of success/failure message
     */
    public function videos_compression_complete(Request $request) {

        $video = AdminVideo::find($request->id);

        if ($video) {

            // Check the video has compress state or not

            if ($video->compress_status <= OVERALL_COMPRESS_COMPLETED) {

                $video->compress_status = COMPRESSION_NOT_HAPPEN;

                $video->trailer_compress_status = COMPRESS_COMPLETED;

                $video->main_video_compress_status = COMPRESS_COMPLETED;

                if($video->save()){
                       
                    return back()->with('flash_success', tr('video_compress_success'));

                } else {

                    return back()->with('flash_error', tr('video_not_saved'));

                }

            } else {

                return back()->with('flash_error', tr('already_video_compressed'));
            }

        } else {

            return back()->with('flash_error', tr('video_not_found'));

        }
    }


    /**
     * Function Name : banner_image()
     *
     * @uses Set banner image for video
     *
     * @param object $request - Banner image video details
     *
     * @created: Shobana Chandrasekar
     *
     * @updated: Vidhya R
     *
     * @return response of success/failure message details
     */
    public function videos_set_banner(Request $request) {

        $validator = Validator::make( $request->all(), array(
                'admin_video_id' => 'required|exists:admin_videos,id,is_approved,'.VIDEO_APPROVED.',status,'.VIDEO_PUBLISHED,
                'banner_image' => 'required|mimes:jpeg,jpg,bmp,png',
            ), [

                'admin_video_id.exists' => tr('video_not_exists'),

            ]
        );
       
        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return back()->with('flash_error', $error_messages);

        } else {

            $video = AdminVideo::find($request->admin_video_id);

            if($request->hasFile('banner_image')) {

                if ($video->is_banner == BANNER_VIDEO) {

                    Helper::delete_picture($video->banner_image, "/uploads/images/");

                }
                
                $video->banner_image = Helper::normal_upload_picture($request->file('banner_image'));

            }

            $video->is_banner = BANNER_VIDEO;

            $video->save();

            return back()->with('flash_success', tr('video_set_banner_success'));

        }

    }

    /**
     * Function Name : videos_remove_banner()
     *
     * @uses Remove banner image for video
     *
     * @param object $request - Banner image video details
     *
     * @created: Shobana Chandrasekar
     *
     * @updated: Vidhya R
     *
     * @return response of success/failure message details
     */
    public function videos_remove_banner(Request $request) {

        $validator = Validator::make( $request->all(), array(
                'admin_video_id' => 'required|exists:admin_videos,id',
            ), [

                'admin_video_id.exists' => tr('video_not_exists'),

            ]
        );
       
        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return back()->with('flash_error', $error_messages);

        } else {

            $video = AdminVideo::find($request->admin_video_id);

            Helper::delete_picture($video->banner_image, "/uploads/images/");

            $video->is_banner = BANNER_VIDEO_REMOVED;

            $video->save();

            return back()->with('flash_success', tr('video_remove_banner'));

        }

    }

    /**
    * Function Name: ios_control()
    *
    * @uses To update the ios payment subscription status
    *
    * @param settings key value
    *
    * @created Maheswari
    *
    * @updated Maheswari
    *
    * @return response of success / failure message.
    */
    public function ios_control(){

        if(Auth::guard('admin')->check()){

            return view('admin.settings.ios-control')->with('page','ios-control');

        } else {

            return back();
        }
    }

    /**
    * Function Name: ios_control()
    *
    * @uses To update the ios settings value
    *
    * @param settings key value
    *
    * @created Maheswari
    *
    * @updated Maheswari
    *
    * @return response of success / failure message.
    */
    public function ios_control_save(Request $request){

        if(Auth::guard('admin')->check()){

            $settings = Settings::get();

            foreach ($settings as $key => $setting_details) {

                # code...

                $current_key = "";

                $current_key = $setting_details->key;
                
                    if($request->has($current_key)) {

                        $setting_details->value = $request->$current_key;
                    }

                $setting_details->save();
            }

            return back()->with('flash_success',tr('settings_success'));

        } else {

            return back();
        }
    
    }

    /**
     * Function Name : admins_create()
     *
     * To create a admin only super admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - -
     *
     * @return response of html page with details
     */
    public function admins_create(Request $request) {

        $model = new Admin();

        return view('admin.admins.create')
                ->with('model', $model)
                ->with('page', 'admins')
                ->with('sub_page', 'create-admins');
    }

    /**
     * Function Name : admins_edit()
     *
     * To edit a admin based on admin id only super admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - Admin Id
     *
     * @return response of html page with details
     */
    public function admins_edit(Request $request) {

        $model = Admin::find($request->id);

        if ($model) {

            return view('admin.admins.edit')
                ->with('model', $model)
                ->with('page', 'admins')
                ->with('sub_page', 'create-admins');

        } else {

            return back()->with('flash_error', tr('admin_not_found'));
        }

    }

    /**
     * Function Name : admins_view()
     *
     * To view a admin based on admin id only super admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - Admin Id
     *
     * @return response of html page with details
     */
    public function admins_view(Request $request) {

        $model = Admin::find($request->id);

        if ($model) {

            return view('admin.admins.view')
                ->with('model', $model)
                ->with('page', 'admins')
                ->with('sub_page', 'admins-index');

        } else {

            return back()->with('flash_error', tr('admin_not_found'));

        }
    }


    /**
     * Function Name : admins_delete()
     *
     * To delete a admin based on admin id. only super admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - Admin Id
     *
     * @return response of html page with details
     */
    public function admins_delete(Request $request) {

        $model = Admin::find($request->id);

        if ($model) {

            if ($model->delete()) {

                return back()->with('flash_success', tr('admin_delete_success'));

            } else {

                return back()->with('flash_error', tr('admin_delete_failure'));

            }

        } else {

            return back()->with('flash_error', tr('admin_not_found'));

        }
    }

    /**
     * Function Name : admins_status()
     *
     * To change the status of the admin, based on admin id. only super admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - Admin Id
     *
     * @return response of html page with details
     */
    public function admins_status(Request $request) {

        $model = Admin::find($request->id);

        if ($model) {

            $model->is_activated = $model->is_activated ? ADMiN_DECLINE_STATUS : ADMIN_APPROVE_STATUS;

            if ($model->save()) {

                if ($model->status) {

                    return back()->with('flash_success', tr('admin_approve_success'));

                } else {

                    return back()->with('flash_success', tr('admin_decline_success'));

                }   

            } else {

                return back()->with('flash_error', tr('admin_not_saved'));

            }

        } else {

            return back()->with('flash_error', tr('admin_not_found'));

        }
    }

    /**
     * Function Name : admins_index()
     *
     * To list out admins (only super admin can access this option)
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - 
     *
     * @return response of html page with details
     */
    public function admins_index(Request $request) {

        $data = Admin::orderBy('created_at', 'desc')->get();

        return view('admin.admins.index')
                ->with('data', $data)
                ->with('page', 'admins')
                ->with('sub_page', 'admins-index');
        
    }

    /**
     * Function Name : admins_save()
     *
     * To save the admin details
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - Admin Id
     *
     * @return response of html page with details
     */
    public function admins_save(Request $request) {

        $validator = Validator::make( $request->all(),array(
                'name' => 'regex:/^[a-zA-Z]*$/|max:100',
                'email' => $request->id ? 'email|max:255|unique:admins,email,'.$request->id : 'required|email|max:255|unique:admins,email,NULL',
                'mobile' => 'digits_between:4,16',
                'address' => 'max:300',
                'id' => 'exists:admins,id',
                'picture' => 'mimes:jpeg,jpg,png',
                'description'=>'required|max:255',
                'password' => $request->id ? '' : 'required|min:6|confirmed',
            )
        );
        
        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return back()->with('flash_error', $error_messages);

        } else {

            $admin = $request->id ? Admin::find($request->id) : new Admin;

            if ($admin) {

                $admin->name = $request->has('name') ? $request->name : $admin->name;

                $admin->email = $request->has('email') ? $request->email : $admin->email;

                $admin->mobile = $request->has('mobile') ? $request->mobile : $admin->mobile;

                $admin->description = $request->description ? $request->description : '';

                if($request->hasFile('picture')) {

                    if($request->id){

                        Helper::delete_picture($admin->picture, "/uploads/");

                    }

                    $admin->picture = Helper::normal_upload_picture($request->picture);

                }
                    
                if (!$admin->id) {

                    $new_password = $request->password;
                    
                    $admin->password = Hash::make($new_password);

                }

                $admin->token = Helper::generate_token();

                $admin->timezone = $request->timezone;

                $admin->token_expiry = Helper::generate_token_expiry();

                $admin->is_activated = 1;

                if($admin->save()) {

                    return back()->with('flash_success', tr('admin_save_success'));

                } else {

                    return back()->with('flash_error', tr('admin_not_saved'));

                }
                  
            } else {

                return back()->with('flash_error', tr('admin_not_found'));
            }

        }
    
    }


    /**
     * Function Name : sub_admins_create()
     *
     * To create a sub admin only admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - -
     *
     * @return response of html page with details
     */
    public function sub_admins_create(Request $request) {

        $model = new Admin();

        return view('admin.sub_admins.create')
                ->with('model', $model)
                ->with('page', 'sub-admins')
                ->with('sub_page', 'sub-create-admins');
    }

    /**
     * Function Name : sub_admins_edit()
     *
     * To edit a sub admin based on subadmin id only  admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - sub Admin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_edit(Request $request) {

        $model = Admin::find($request->id);

        if ($model) {

            return view('admin.sub_admins.edit')
                ->with('model', $model)
                ->with('page', 'sub-admins')
                ->with('sub_page', 'sub-create-admins');

        } else {

            return back()->with('flash_error', tr('sub_admin_not_found'));
        }

    }

    /**
     * Function Name : sub_admins_view()
     *
     * To view a sub admin based on sub admin id only admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - Sub Admin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_view(Request $request) {

        $model = Admin::find($request->id);

        if ($model) {

            return view('admin.sub_admins.view')
                ->with('model', $model)
                ->with('page', 'sub-admins')
                ->with('sub_page', 'sub-admins-index');

        } else {

            return back()->with('flash_error', tr('sub_admin_not_found'));

        }
    }


    /**
     * Function Name : sub_admins_delete()
     *
     * To delete a sub admin based on sub admin id. only admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - Sub Admin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_delete(Request $request) {

        $model = Admin::find($request->id);

        if ($model) {

            if ($model->delete()) {

                return back()->with('flash_success', tr('sub_admin_delete_success'));

            } else {

                return back()->with('flash_error', tr('sub_admin_delete_failure'));

            }

        } else {

            return back()->with('flash_error', tr('sub_admin_not_found'));

        }
    }

    /**
     * Function Name : sub_admins_status()
     *
     * To change the status of the sub admin, based on sub admin id. only admin can access this option
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - SubAdmin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_status(Request $request) {

        $model = Admin::find($request->id);

        if ($model) {

            $model->is_activated = $model->is_activated ? ADMiN_DECLINE_STATUS : ADMIN_APPROVE_STATUS;

            if ($model->save()) {

                if ($model->status) {

                    return back()->with('flash_success', tr('sub_admin_approve_success'));

                } else {

                    return back()->with('flash_success', tr('sub_admin_decline_success'));

                }   

            } else {

                return back()->with('flash_error', tr('sub_admin_not_saved'));

            }

        } else {

            return back()->with('flash_error', tr('sub_admin_not_found'));

        }
    }

    /**
     * Function Name : sub_admins_index()
     *
     * To list out subadmins (only admin can access this option)
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - 
     *
     * @return response of html page with details
     */
    public function sub_admins_index(Request $request) {

        $data = Admin::orderBy('created_at', 'desc')->get();


        return view('admin.sub_admins.index')
                ->with('data', $data)
                ->with('page', 'sub-admins')
                ->with('sub_page', 'sub-admins-index');
        
    }

    /**
     * Function Name : sub_admins_save()
     *
     * To save the sub admin details
     * 
     * @created - Shobana Chandrasekar
     *
     * @updated -  
     *
     * @param object $request - Sub Admin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_save(Request $request) {

        $validator = Validator::make( $request->all(),array(
                'name' => 'regex:/^[a-zA-Z]*$/|max:100',
                'email' => $request->id ? 'email|max:255|unique:admins,email,'.$request->id : 'required|email|max:255|unique:admins,email,NULL',
                'mobile' => 'digits_between:4,16',
                'address' => 'max:300',
                'id' => 'exists:admins,id',
                'picture' => 'mimes:jpeg,jpg,png',
                'description'=>'required|max:255',
                'password' => $request->id ? '' : 'required|min:6|confirmed',
            )
        );
        
        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return back()->with('flash_error', $error_messages);

        } else {

            $admin = $request->id ? Admin::find($request->id) : new Admin;

            if ($admin) {

                $admin->name = $request->has('name') ? $request->name : $admin->name;

                $admin->email = $request->has('email') ? $request->email : $admin->email;

                $admin->mobile = $request->has('mobile') ? $request->mobile : $admin->mobile;

                $admin->description = $request->description ? $request->description : '';

                if($request->hasFile('picture')) {

                    if($request->id){

                        Helper::delete_picture($admin->picture, "/uploads/");

                    }

                    $admin->picture = Helper::normal_upload_picture($request->picture);

                }
                    
                if (!$admin->id) {

                    $new_password = $request->password;
                    
                    $admin->password = Hash::make($new_password);

                }

                $admin->token = Helper::generate_token();

                $admin->timezone = $request->timezone;

                $admin->token_expiry = Helper::generate_token_expiry();

                $admin->is_activated = 1;

                if($admin->save()) {

                    return back()->with('flash_success', tr('sub_admin_save_success'));

                } else {

                    return back()->with('flash_error', tr('sub_admin_not_saved'));

                }
                  
            } else {

                return back()->with('flash_error', tr('sub_admin_not_found'));
            }

        }
    
    }


    /**
     * Function Name : videos_download_status()
     *
     * @uses To change the status of the download video option from IOS / ANdroid
     *
     * @param object $id - Video id
     *
     * @created: Shobana Chandrasekar
     *
     * @updated: -
     *
     * @return response of success/failure message details
     */
    public function videos_download_status($id) {

        if($data = AdminVideo::where('id' , $id)->first()) {

            $data->download_status  = $data->download_status ? DISABLED_DOWNLOAD : ENABLED_DOWNLOAD;

            $data->save();

            return back()->with('flash_success' , $data->download_status ? tr('enable_video_success') : tr('disable_video_success'));
        } else {
            return back()->with('flash_error',tr('admin_not_error'));
        }
    }

   /**
    * Function Name : offline_videos()
    *
    * @uses To list out offline videos based on users
    *
    * @created Shobana Chandrasekar
    *
    * @updated
    *
    * @param object $request - user & video details
    *
    * @return response of json details
    */
   public function offline_videos(Request $request) {

        try {

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
                    'sub_profile_id'=>'exists:sub_profiles,id',
                ),
                array(
                    'exists' => 'The :attribute doesn\'t exists please provide correct profile',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

                throw new Exception($error_messages);

            } else {

                $user = User::find($request->id);

                $videos = OfflineAdminVideo::select('admin_videos.title', 'offline_admin_videos.*')
                    ->leftJoin('admin_videos', 'admin_videos.id', '=', 'offline_admin_videos.admin_video_id')
                    ->where('user_id', $request->id)->paginate(10);

            }

            return view('admin.users.offline_videos')->with('videos' , $videos)
                    ->withPage('users')
                    ->with('sub_page','view-user')
                    ->with('user', $user);            

        } catch (Exception $e) {

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return back()->with('flash_error', $e->getMessage());

        }

   }


   /**
    * Function Name : offline_videos_delete()
    *
    * @uses delete local storage file
    *
    * @created Shobana Chandrasekar
    *
    * @updated
    *
    * @param object $request - user & video details
    *
    * @return response of json details
    */
   public function offline_videos_delete(Request $request) {

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
                    'admin_video_id' => 'required|integer|exists:admin_videos,id,status,'.VIDEO_PUBLISHED.',is_approved,'.VIDEO_APPROVED,
                    'sub_profile_id'=>'exists:sub_profiles,id',
                ),
                array(
                    'exists' => 'The :attribute doesn\'t exists please provide correct video id',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

                throw new Exception($error_messages);

            } else {

                $model = OfflineAdminVideo::where('admin_video_id',$request->admin_video_id)->where('user_id', $request->id)->first();

                if ($model) {

                    if ($model->delete()) {


                    } else {

                        throw new Exception(tr('offline_video_not_delete'));
                        
                    }

                } else {

                    throw new Exception(tr('offline_video_not_save'));
                    
                }
            }

            DB::commit();

            $response_array = array('success' => true);

           return back()->with('flash_success', tr('offline_video_delete_success'));

        } catch (Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return back()->with('flash_error', $e->getMessage());

        }

   }

   /**
    * Function Name : categories_home_status()
    *
    * @uses update the home page display status for selected category
    *
    * @created Vithya R
    *
    * @updated
    *
    * @param object $request - user & video details
    *
    * @return success/failure message
    */
   public function categories_home_status(Request $request) {

        try {
        
            $validator = Validator::make($request->all(),
                [
                    'category_id' => 'required|integer|exists:categories,id,status,'.APPROVED,
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);

            }

            DB::beginTransaction();

            // Check the home max count

            $total_home_page_categories = Category::where('is_home_display', YES)->count();

            if($total_home_page_categories > Setting::get('max_home_count')) {

                throw new Exception(tr('admin_category_home_limit_exceeed'), 101);

            }

            $category_details = Category::find($request->category_id);

            if(!$category_details) {

                throw new Exception(tr('admin_category_not_found'), 101);
                
            }

            $category_details->is_home_display = $category_details->is_home_display == YES ? NO : YES;

            if($category_details->save()) {

                DB::commit();

                $message = $category_details->is_home_display == YES ? tr('admin_category_home_status_added') : tr('admin_category_home_status_removed');

                return back()->with('flash_success', $message);

            } else {

                throw new Exception(tr('admin_category_home_status_error'), 101);
                
            }
            

        } catch (Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            return back()->with('flash_error', $error_messages);

        }

   }

   /**
    * Function Name : admin_videos_original_status()
    *
    * @uses update the original page display status for selected video
    *
    * @created Vithya R
    *
    * @updated
    *
    * @param integer admin_video_id
    *
    * @return success/failure message
    */
   public function admin_videos_original_status(Request $request) {

        try {
        
            $validator = Validator::make($request->all(),
                [
                    'admin_video_id' => 'required|integer|exists:admin_videos,id,status,'.APPROVED,
                ]
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);

            }

            DB::beginTransaction();

            // Check the home max count

            $total_original_based_videos = AdminVideo::where('is_original_video', ORIGINAL_VIDEO_YES)->count();

            if($total_original_based_videos > Setting::get('max_original_count')) {

                throw new Exception(tr('admin_video_original_limit_exceeed'), 101);

            }

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if(!$admin_video_details) {

                throw new Exception(tr('admin_video_not_found'), 101);
                
            }

            $admin_video_details->is_original_video = $admin_video_details->is_original_video == ORIGINAL_VIDEO_YES ? ORIGINAL_VIDEO_NO : ORIGINAL_VIDEO_YES;

            if($admin_video_details->save()) {

                DB::commit();

                $message = $admin_video_details->is_original_video == ORIGINAL_VIDEO_YES ? tr('admin_video_original_status_added') : tr('admin_video_original_status_removed');

                return back()->with('flash_success', $message);

            } else {

                throw new Exception(tr('admin_video_original_status_error'), 101);
                
            }
            

        } catch (Exception $e) {

            DB::rollback();

            $error_messages = $e->getMessage();

            return back()->with('flash_error', $error_messages);

        }

   }

}   





