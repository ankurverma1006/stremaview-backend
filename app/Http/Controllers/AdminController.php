<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Repositories\AdminRepository as AdminRepo;

use App\Repositories\VideoRepository as VideoRepo;

use App\Repositories\PushNotificationRepository as PushRepo;

use App\Helpers\Helper;

use App\Helpers\VideoHelper;

use App\Helpers\EnvEditorHelper;

use App\Jobs\StreamviewCompressVideo;

// use App\Jobs\NormalPushNotification;

use App\Jobs\SendVideoMail;

use App\Jobs\SendMailCamp;

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

use App\Admin;

use App\SubAdmin;

use App\User;

use App\UserPayment;

use App\UserHistory;

use App\UserRating;

use App\UserCoupon;

use App\UserLoggedDevice;

use App\Wishlist;

use App\SubProfile;

use App\Moderator;

use App\Redeem;

use App\RedeemRequest;

use App\Category;

use App\SubCategory;

use App\SubCategoryImage;

use App\CastCrew;

use App\Subscription;

use App\Coupon;

use App\Genre;

use App\AdminVideo;

use App\AdminVideoImage;

use App\VideoCastCrew;

use App\PayPerView;

use App\Language;

use App\Notification;

use App\EmailTemplate;

use App\Settings;

use App\Page;

use App\Flag;


class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');  
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
     * Function: dashboard()
     * 
     * @uses used to display analytics of the website
     *
     * @created Anjana H
     *
     * @updated Anjana H
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

            return view('admin.dashboard.dashboard')
                        ->withPage('dashboard')
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
     * Function Name : users_index()
     *
     * @uses To list out users object details
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return View page
     */
    public function users_index() {

        $users = User::orderBy('created_at','desc')->paginate(10);

        return view('admin.users.index')
        		->withPage('users')
                ->with('users' , $users)
                ->with('sub_page','users-view');
    }

    /**
     * Function Name : users_create()
     *
     * @uses To create a user object details
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return View page
     */
    public function users_create() {

        $user_details = new User;

        return view('admin.users.create')
                    ->with('page' , 'users')
                    ->with('sub_page','users-create')
                    ->with('user_details',$user_details);
    }

    /**
     * Function Name : users_edit()
     *
     * @uses To display and update user object details based on user id
     *
     * @created  Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $user_id
     *
     * @return View page
     */
    public function users_edit(Request $request) {

        try {
          
            $user_details = User::find($request->user_id);

            if( count($user_details) == 0 ) {

                throw new Exception( tr('admin_user_not_found'), 101);
            } 

            return view('admin.users.edit')
                    ->with('page' , 'users')
                    ->with('sub_page','users-view')
                    ->with('user_details',$user_details);
      

        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.users.index')->with('flash_error',$error);
        }

    }

    /**
     * Function Name : users_save()
     *
     * @uses To save the user object details of new/existing based on details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $user_id , (request) user details
     *
     * @return success/error message
     */
    public function users_save(Request $request) {

        try {

            DB::beginTransaction();
           
            $validator = Validator::make( $request->all(), [
                        // 'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                        'name' => 'required|min:2|max:100',
                        'email' => $request->user_id ? 'required|email|max:255|unique:users,email,'.$request->user_id : 'required|email|max:255|unique:users,email',
                        'mobile' => 'required|digits_between:4,16',
                        'password' => $request->user_id ? '' : 'required|min:6|confirmed',
                ]
            );

            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());
                
                throw new Exception($error, 101);            
            } 

            $new_user = NO;

            if($request->user_id != '') {

                $user_details = User::find($request->user_id);

                $message = tr('admin_user_update_success');  

                if($request->hasFile('picture')) {

                    Helper::delete_picture($user_details->picture, "/uploads/images/users/"); 
                } 

            } else {

                $new_user = YES;

                //Add New User
                $user_details = new User;
                
                $new_password = $request->password;

                $user_details->password = Hash::make($new_password);

                $message = tr('admin_user_create_success');

                $user_details->login_by = LOGIN_BY_MANUAL;
                
                $user_details->device_type = DEVICE_WEB ;

                $user_details->picture = asset('placeholder.png');
            }  

            if($request->hasFile('picture')) {
                
                $user_details->picture = Helper::normal_upload_picture($request->file('picture'), "/uploads/images/users/" );
            }          

            $user_details->timezone = $request->has('timezone') ? $request->timezone : '';

            $user_details->name = $request->has('name') ? $request->name : '';

            $user_details->email = $request->has('email') ? $request->email: '';

            $user_details->mobile = $request->has('mobile') ? $request->mobile : '';
            
            $user_details->token = Helper::generate_token();

            $user_details->token_expiry = Helper::generate_token_expiry();

            $user_details->is_activated = $user_details->status = USER_APPROVED; 
            
            $user_details->no_of_account = DEFAULT_SUB_ACCOUNTS;

            if($request->user_id == '') {
                
                // email notification for new user
                $email_data['name'] = $user_details->name;

                $email_data['password'] = $new_password;

                $email_data['email'] = $user_details->email;

                $email_data['template_type'] = ADMIN_USER_WELCOME;

                // $subject = tr('user_welcome_title').' '.Setting::get('site_name');
                $page = "emails.admin_user_welcome";

                $email = $user_details->email;

                Helper::send_email($page,$subject = null,$email,$email_data);

                // Check the default subscription and save the user type for new user 
                user_type_check($user_details->id);

            }

            $user_details->is_verified = USER_EMAIL_VERIFIED;      

            if( $user_details->save() ) {

                DB::commit();

            } else {

                throw new Exception(tr('admin_user_save_error'), 101);
            }

            if( $new_user == YES ) {

                $sub_profile = new SubProfile;

                $sub_profile->user_id = $user_details->id;

                $sub_profile->name = $user_details->name;

                $sub_profile->picture = $user_details->picture;

                $sub_profile->status = DEFAULT_TRUE;

                $user_details->is_verified = USER_EMAIL_VERIFIED;

                $user_details->save();

                if( $sub_profile->save() ) {

                    DB::commit();

                } else {

                    throw new Exception(tr('admin_user_sub_profile_save_error'), 101);
                }

            } else {

                $sub_profile = SubProfile::where('user_id', $request->user_id)->first();

                if (!$sub_profile) {

                    $sub_profile = new SubProfile;

                    $sub_profile->user_id = $user_details->id;

                    $sub_profile->name = $user_details->name;

                    $sub_profile->picture = $user_details->picture;

                    $sub_profile->status = DEFAULT_TRUE;

                    if( $sub_profile->save() ) {

                        DB::commit();

                    } else {

                        throw new Exception(tr('admin_user_sub_profile_save_error'), 101);
                    }

                }

            }

            if($user_details) {

                $moderator = Moderator::where('email', $user_details->email)->first();

                // If the user already registered as moderator, atuomatically the status will update.
                if($moderator && $user_details) {

                    $user_details->is_moderator = DEFAULT_TRUE;

                    $user_details->moderator_id = $moderator->id;
                    
                    if( $user_details->save() ) {

                        DB::commit();

                    } else {

                        throw new Exception(tr('admin_user_save_error'), 101);
                    }

                    $moderator->is_activated = DEFAULT_TRUE;

                    $moderator->is_user = DEFAULT_TRUE;
                    
                    if( $moderator->save() ) {

                        DB::commit();

                    } else {

                        throw new Exception(tr('admin_user_to_moderator_save_error'), 101);
                    }
                }

                register_mobile('web');

                if (Setting::get('track_user_mail')) {

                    user_track("StreamHash - New User Created");
                }

                return redirect()->route('admin.users.view' ,['user_id' => $user_details->id] )->with('flash_success', $message);
            } 
            
            throw new Exception(tr('admin_user_save_error'), 101);                     
            
        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return redirect()->back()->withInput()->with('flash_error', $error);
            
        }

    }

    /**
     * Function Name : users_view()
     *
     * @uses To display user details based on user id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $user_id 
     *
     * @return view page
     */
    public function users_view(Request $request) {

        try {
               
            $user_details = User::find($request->user_id) ;
            
            if( count($user_details) == 0 ) {

                throw new Exception(tr('admin_user_not_found'), 101);
            }

            return view('admin.users.view')
                    ->with('page','users')
                    ->with('sub_page','users-view')
                    ->with('user_details' , $user_details);
        
        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.users.index')->with('flash_error',$error);
        }

    }    

    /**
     * Function: users_delete()
     * 
     * @uses To delete the user object based on user id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function users_delete(Request $request) {

        try {
            
            DB::beginTransaction();

            $user_details = User::where('id',$request->user_id)->first();

            if( count($user_details) == 0 ) { 

                throw new Exception(tr('admin_user_not_found'), 101);
            }
            if ($user_details->device_type) {

                // Load Mobile Registers
                subtract_count($user_details->device_type);
            }

            if( $user_details->picture )
                // Delete the old pic
                Helper::delete_picture($user_details->picture, "/uploads/images/users/"); 

                // After reduce the count from mobile register model delete the user
            if( $user_details->is_moderator ) {    

                $moderator = Moderator::where('email',$user_details->email)->first();
                
                if($moderator){

                    $moderator->is_user = NO;

                    $moderator->save(); 
                }
            }
            if ($user_details->delete()) {

                DB::commit();

                return redirect()->route('admin.users.index')->with('flash_success',tr('admin_user_delete_success'));  
            } 

            throw new Exception(tr('admin_user_delete_error'), 101);
            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return redirect()->back()->withInput()->with('flash_error', $error);
        }

    }

    /**
     * Function Name : users_status_change()
     *
     * @uses To update user status to approve/decline based on user id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $user_id
     *
     * @return success/error message
     */
    public function users_status_change(Request $request) {

        try {

            DB::beginTransaction();
       
            $user_details = User::find($request->user_id);

            if( count( $user_details) == 0) {
                
                throw new Exception(tr('admin_user_not_found'), 101);
            } 
            
            $user_details->is_activated = $user_details->is_activated == USER_ACTIVATED ? USER_DEACTIVATED : USER_ACTIVATED;

            $message = $user_details->is_activated == USER_ACTIVATED ? tr('admin_user_activate_success') : tr('admin_user_deactivate_success');

            if( $user_details->save() ) {

                DB::commit();

                return back()->with('flash_success',$message);
            } 

            throw new Exception(tr('admin_user_is_activated_save_error'), 101);
            
        } catch( Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.users.index')->with('flash_error',$error);
        }

    }


    /**
     * Function: users_verify_status()
     * 
     * @uses To verify for the user Email 
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $user_id
     *
     * @return success/error message
     */
    public function users_verify_status(Request $request) {

        try {   

            DB::beginTransaction();
       
            $user_details = User::find($request->user_id);

            if( count( $user_details) == 0) {
                
                throw new Exception(tr('admin_user_not_found'), 101);
            } 
            
            $user_details->is_verified = $user_details->is_verified == USER_EMAIL_VERIFIED ? USER_EMAIL_NOT_VERIFIED : USER_EMAIL_VERIFIED;

            $message = $user_details->is_verified == USER_EMAIL_VERIFIED ? tr('admin_user_verify_success') : tr('admin_user_unverify_success');

            if( $user_details->save() ) {

                DB::commit();

                return back()->with('flash_success',$message);
            } 
            
            throw new Exception(tr('admin_user_is_activated_save_error'), 101);
            
        } catch (Exception $e) {
            
            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.users.index')->with('flash_error',$error);
        }
    }

    /**
     * Function : users_sub_profiles()
     *
     * @uses list the sub profiles based on the selected user
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     * 
     * @return list of sub profiles page
     */

    public function users_sub_profiles(Request $request) {

        try {
            
            $user_details = User::find($request->user_id);

            if( count($user_details) == 0 ) { 

                throw new Exception(tr('admin_user_not_found'), 101);                
            }

            $sub_profiles = SubProfile::where('user_id', $request->user_id)
                                        ->orderBy('created_at','desc')
                                        ->paginate(10);

            return view('admin.users.sub_profiles')
                        ->withPage('users')
                        ->with('sub_page','users-view')
                        ->with('user_details' , $user_details)
                        ->with('sub_profiles' , $sub_profiles);

        } catch (Exception $e) {
             
            $error = $e->getMessage();

            return redirect()->route('admin.users.index')->with('flash_error',$error);
        }        

    }


    /**
     * Function Name : users_upgrade()
     *
     * @uses To upgrade a user as moderator based on user id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $user_id
     *
     * @return success/error message
     */
    public function users_upgrade(Request $request) {

        try {

            DB::beginTransaction();
            
            $user_details = User::find($request->user_id);
            
            if( count($user_details) == 0 ) {

                throw new Exception(tr('admin_user_not_found'), 101);
            } 
            
            $moderator_details = Moderator::where('email' , $user_details->email)->first();

            if( count($moderator_details) == 0 ) {

                $moderator_user = new Moderator;

                $moderator_user->name = $user_details->name;

                $moderator_user->email = $user_details->email;

                if($user_details->login_by == LOGIN_BY_MANUAL ) {

                    $moderator_user->password = $user_details->password;  

                    $new_password = tr('user_login_password');

                } else {

                    $new_password = time();
                    $new_password .= rand();
                    $new_password = sha1($new_password);
                    $new_password = substr($new_password, 0, 8);
                    $moderator_user->password = Hash::make($new_password);
                }

                $moderator_user->picture = $user_details->picture;
                $moderator_user->mobile = $user_details->mobile;
                $moderator_user->address = $user_details->address;
                
                if( $moderator_user->save() ) {

                    DB::commit();

                } else {

                    throw new Exception(tr('admin_user_to_moderator_save_error'), 101);
                }

                $email_data = array();

               //  $subject = tr('user_welcome_title').' '.Setting::get('site_name');
                $page = "emails.moderator_welcome";
                $email = $user_details->email;
                $email_data['template_type'] = MODERATOR_WELCOME;
                $email_data['name'] = $moderator_user->name;
                $email_data['email'] = $moderator_user->email;
                $email_data['password'] = $new_password;

                Helper::send_email($page,$subject = null,$email,$email_data);

                $moderator_details = $moderator_user;
            } 

            if($moderator_details) {

                $user_details->is_moderator = YES;
                $user_details->moderator_id = $moderator_details->id;
                $user_details->save();

                if( $user_details->save() ) {

                    DB::commit();

                } else {

                    throw new Exception(tr('admin_user_to_moderator_save_error'), 101);
                }

                $moderator_details->is_activated = USER_ACTIVATED ;

                $moderator_details->is_user = YES;
                
                if( $moderator_details->save() ) {

                    DB::commit();

                    return back()->with('flash_success',tr('admin_user_upgrade'));
                } 

                throw new Exception(tr('admin_user_to_moderator_save_error'), 101);

            }

            throw new Exception(tr('admin_user_to_moderator_save_error'), 101);

        } catch (Exception $e) {
            
            DB::rollback();
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }


    /**
     * Function Name : users_upgrade_disable()
     *
     * @uses To disable a user as moderator based on user id, moderator id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $user_id,$moderator_id
     *
     * @return success/error message
     */
    public function users_upgrade_disable(Request $request) {

        try {

            DB::beginTransaction();

            $moderator_details = Moderator::find($request->moderator_id);
            
            if( count($moderator_details) == 0 ) {

                throw new Exception(tr('admin_moderator_not_found'), 101);
            }

            $user_details = User::find($request->user_id);
            
            if( count($user_details) == 0) {

                throw new Exception(tr('admin_user_not_found'), 101);
            }

            $user_details->is_moderator = NO;
            
            if( $user_details->save() ) {

                DB::commit();

            } else {

                throw new Exception(tr('admin_user_upgrade_disable_error'), 101);
            }

            $moderator_details->is_activated = MODERATOR_DEACTIVATED;

            if( $moderator_details->save() ) {

                DB::commit();

                return back()->with('flash_success',tr('admin_user_upgrade_disable_success'));
            } 

            throw new Exception(tr('admin_user_upgrade_disable_error'), 101);
           
        } catch (Exception $e) {
            
            DB::rollback();
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }

    }

    /**
     * Function Name : users_history()
     *
     * @uses To display a sub user history based on sub profile id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $sub_profile_id
     *
     * @return view page
     */
    public function users_history(Request $request) {

        try {
            
            $sub_profile_details = SubProfile::find($request->sub_profile_id);

            if( count($sub_profile_details) == 0 ) {

                throw new Exception(tr('admin_sub_user_profile_not_found') , 101);
            }

            $user_histories = UserHistory::where('user_id' , $request->sub_profile_id)
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
                            
            return view('admin.users.history')
                        ->withPage('users')
                        ->with('sub_page','users')
                        ->with('user_histories' , $user_histories)
                        ->with('sub_profile_details', $sub_profile_details);
            
        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }

    }

    /**
     * Function Name : users_history_remove()
     *
     * @uses To delete the sub user history based on sub user history id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $user_history_id
     *
     * @return success/failure message
     */
    public function users_history_remove(Request $request) {

        try {

            DB::beginTransaction();

            $user_history = UserHistory::find($request->user_history_id);
            
            if( count($user_history) == 0 ) {
                
                throw new Exception(tr('admin_user_history_not_found'), 101);
            }

            if( $user_history->delete() ) {

                DB::commit();

                return back()->with('flash_success',tr('admin_user_history_delete_success'));
            } 

            throw new Exception(tr('admin_user_history_delete_error') , 101);      
           
        } catch (Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function Name : users_wishlist()
     *
     * @uses To view the sub user wishlist based on sub_profile_id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $sub_profile_id
     *
     * @return view page
     */
    public function users_wishlist(Request $request) {

        try {

            $user_sub_profile_details = SubProfile::find($request->sub_profile_id);
            
            if( count($user_sub_profile_details) == 0 ) {
                
                throw new Exception(tr('admin_user_sub_profile_not_found') , 101);
            }

            $user_wishlists= Wishlist::where('user_id' , $request->sub_profile_id)
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

            return view('admin.users.wishlist')
                    ->withPage('users')
                    ->with('sub_page','users')
                    ->with('user_wishlists' , $user_wishlists)
                    ->with('user_sub_profile_details', $user_sub_profile_details);

        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function Name : users_wishlist_remove()
     *
     * @uses To view the sub user wishlist based on sub_profile_id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $sub_profile_id
     *
     * @return view page
     */
    public function users_wishlist_remove(Request $request) {

        try {

            DB::beginTransaction();

            $user_wishlist_details = Wishlist::find($request->user_wishlist_id);
            
            if( count($user_wishlist_details) == 0 ) {
                
                throw new Exception(tr('admin_user_wishlist_not_found') , 101);
            }

            if( $user_wishlist_details->delete() ) {

                DB::commit();
                
                return back()->with('flash_success',tr('admin_user_wishlist_delete_success'));
            } 

            throw new Exception(tr('admin_user_wishlist_delete_error') , 101);      
          
        } catch (Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }

    }

    /**
     * Function Name : users_clear_login
     *
     * @uses To clear all the logins from all devices
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param object $request - User details
     *
     * @return response of success/failure message
     */
    public function users_clear_login(Request $request) {

        try {
            
            DB::beginTransaction();

            $user_details = User::find($request->user_id);

            if ( count($user_details) == 0 ) {

                throw new Exception(tr('admin_user_not_found'), 101);
            }

            // Delete all the records which is stored before
            $user_logged_device = UserLoggedDevice::where('user_id', $request->user_id);

            if( count( $user_logged_device) > 0  ) {

                $user_logged_device->delete();

                $user_details->logged_in_account = 0;

                $user_details->save();

                return back()->with('flash_success', tr('admin_user_clear'));

            } 

            throw new Exception(tr('admin_user_no_device_to_clear') , 101);
                       
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }

    }

    /**
     * Function: moderators_index()
     * 
     * @uses used to list the moderators
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return view page
     */
    public function moderators_index() {

        $moderators = Moderator::orderBy('created_at','desc')->paginate(10);

        return view('admin.moderators.index')
                    ->withPage('moderators')
                    ->with('sub_page','moderators-view')
                    ->with('moderators' , $moderators);
    }

    /**
     * Function Name : moderator_create()
     *
     * @uses To create a moderator object details
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return View page
     */

    public function moderators_create() {

        $moderator_details = new Moderator;

        return view('admin.moderators.create')
                ->with('page' ,'moderators')
                ->with('sub_page' ,'moderators-create')
                ->with('moderator_details',$moderator_details);
    }

    /**
     * Function Name : moderators_edit()
     *
     * @uses To display and update moderator object details based on the moderator id
     *
     * @created  Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $moderator_id
     *
     * @return View page
     */
    public function moderators_edit(Request $request) {

        try {
          
            $moderator_details = Moderator::find($request->moderator_id);

            if( count($moderator_details) == 0 ) {

                throw new Exception( tr('admin_moderator_not_found'), 101);
            } 

            return view('admin.moderators.edit')
                        ->with('page' , 'moderators')
                        ->with('sub_page','moderators-view')
                        ->with('moderator_details',$moderator_details);
            

        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.moderators.index')->with('flash_error',$error);
        }

    }

    /**
     * Function Name : moderators_save()
     *
     * @uses To save the moderator object details of new/existing based on details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $moderator_id , (request) user details
     *
     * @return success/error message
     */
    public function moderators_save(Request $request) {

        try {
            
            DB::beginTransaction();

            $validator = Validator::make( $request->all(), array(
                    // 'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                    'name' => 'required|min:2|max:100',
                    'email' => $request->moderator_id ? 'required|email|max:255|unique:moderators,email,'.$request->moderator_id : 'required|email|max:255|unique:moderators,email',
                    'mobile' => 'required|digits_between:4,16',
                    'password' => $request->moderator_id ? '' : 'required|min:6|confirmed',
                )
            );

            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());
                
                throw new Exception($error, 101);            
            } 

            $changed_email = DEFAULT_FALSE;

            $email = "";

            if( $request->moderator_id != '' ) {

                $moderator_details = Moderator::find($request->moderator_id);

                $message = tr('admin_moderator_update_success');

                if ($moderator_details->email != $request->email) {

                    $changed_email = DEFAULT_TRUE;

                    $email = $moderator_details->email;
                }

                if($request->hasFile('picture')) {

                    Helper::delete_picture($moderator_details->picture, "/uploads/images/moderators/"); 
                } 

            } else {

                $message = tr('admin_moderator_create_success');

                //Add New moderator
                $moderator_details = new Moderator;

                $new_password = $request->password;

                $moderator_details->password = Hash::make($new_password);

                $moderator_details->is_activated = MODERATOR_ACTIVATED;

                $moderator_details->picture = asset('placeholder.png');

            }

            if($request->hasFile('picture')) {
                
                $moderator_details->picture = Helper::normal_upload_picture($request->file('picture'), "/uploads/images/moderators/" );
            }    

            $moderator_details->timezone = $request->has('timezone') ? $request->timezone : '';

            $moderator_details->name = $request->has('name') ? $request->name : '';

            $moderator_details->email = $request->has('email') ? $request->email: '';

            $moderator_details->mobile = $request->has('mobile') ? $request->mobile : '';
            
            $moderator_details->token = Helper::generate_token();

            $moderator_details->token_expiry = Helper::generate_token_expiry();

            if($request->moderator_id == '') {

                $email_data['name'] = $moderator_details->name;
                $email_data['password'] = $new_password;
                $email_data['email'] = $moderator_details->email;
                $email_data['template_type'] = MODERATOR_WELCOME;
               // $subject = tr('moderator_welcome_title').Setting::get('site_name');
                $page = "emails.moderator_welcome";
                $email = $moderator_details->email;
                Helper::send_email($page,$subject = null,$email,$email_data);

            }

            if( $moderator_details->save() ) {

                DB::commit();

            } else {

                throw new Exception(tr('admin_moderator_save_error'), 101);
            }

            $user = User::where('email', $moderator_details->email)->first();

            // if the moderator already exists in user table, the status will change automatically
            if($moderator_details && $user) {

                $user->is_moderator = DEFAULT_TRUE;
                $user->moderator_id = $moderator_details->id;

                if( $user->save() ) {

                    DB::commit();

                } else {

                    throw new Exception(tr('admin_moderator_save_error'), 101);
                }

                $moderator_details->is_activated = MODERATOR_ACTIVATED;

                $moderator_details->is_user = DEFAULT_TRUE;

                if( $moderator_details->save() ) {

                    DB::commit();

                } else {

                    throw new Exception(tr('admin_moderator_save_error'), 101);
                }
            }

            if ($changed_email) {

                if ($email) {

                    $email_data = array();

                   //  $subject = tr('user_welcome_title').' '.Setting::get('site_name');
                    $page = "emails.moderator_update_profile";
                    $email_data['template_type'] = MODERATOR_UPDATE_MAIL;
                    $email_data['name'] = $moderator_details->name;
                    $email_data['email'] = $moderator_details->email;

                    Helper::send_email($page,$subject = null,$email,$email_data);
                }
            }

            if (Setting::get('track_user_mail')) {

                user_track("StreamHash - Moderator Created");
            }

            return redirect()->route('admin.moderators.view',['moderator_id' => $moderator_details->id] )->with('flash_success', $message);
        
            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return redirect()->back()->with('flash_error',$error);
        }  
        
    }

    /**
     * Function Name : moderators_view()
     *
     * @uses To display moderator details based on moderator id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $moderator_id 
     *
     * @return view page
     */
    public function moderators_view(Request $request) {

        try {
               
            $moderator_details = Moderator::find($request->moderator_id) ;
            
            if( count($moderator_details) == 0 ) {

                throw new Exception(tr('admin_moderator_not_found'), 101);
            }

            return view('admin.moderators.view')
                        ->with('page','moderators')
                        ->with('sub_page','moderators-view')
                        ->with('moderator_details' , $moderator_details);
           
        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.moderators.index')->with('flash_error',$error);
        }

    }
    
    /**
     * Function: moderators_delete()
     * 
     * @uses To delete the moderator object based on moderator id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function moderators_delete(Request $request) {

        try {

            DB::beginTransaction();

            $moderator_details = Moderator::find($request->moderator_id);

            if( count($moderator_details) == 0 ) {

                throw new Exception(tr('admin_moderator_not_found'), 101);
            }

            if( $moderator_details->picture ) {

                Helper::delete_picture($moderator_details->picture , '/uploads/images/moderators');
            }

            if( $moderator_details->is_user ) {

                $user_details = User::where('email',$moderator_details->email)->first();

                if( $user_details ) {

                    $user_details->is_moderator = NO;

                    if( $user_details->save() ) {
                        
                        DB::commit();

                    } else {

                        throw new Exception(tr('admin_moderator_delete_error'), 101);
                    } 
                }
            }            

            if( $moderator_details->id ) {

                $admin_video_details = AdminVideo::where('uploaded_by',$moderator_details->id)->first();

                if($admin_video_details) {
                    
                    if ($admin_video_details->delete()) {
                        
                        DB::commit();

                    } else {

                        throw new Exception(tr('admin_moderator_delete_error'), 101);
                    }  
                }         
            }

            if ($moderator_details->delete()) {
                        
                DB::commit();

                return redirect()->route('admin.moderators.index')->with('flash_success',tr('admin_moderator_delete_success'));
            } 
            
            throw new Exception(tr('admin_moderator_delete_error'), 101);

        } catch (Exception $e) {
            
            DB::rollback();
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }


    /**
     * Function Name : umoderator_status_change()
     *
     * @uses To update moderator status to approve/decline based on moderator id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $moderator_id
     *
     * @return success/error message
     */
    public function moderators_status_change(Request $request) {

        try {

            DB::beginTransaction();
       
            $moderator_details = Moderator::find($request->moderator_id);

            if( count( $moderator_details) == 0 ) {
                
                throw new Exception(tr('admin_moderator_not_found'), 101);
            }

            $moderator_details->is_activated = $moderator_details->is_activated == MODERATOR_ACTIVATED ? MODERATOR_DEACTIVATED : MODERATOR_ACTIVATED ;
                        
            $message = $moderator_details->is_activated == MODERATOR_ACTIVATED ? tr('admin_moderator_activate_success') : tr('admin_moderator_deactivate_success');

            if( $moderator_details->save() ) {

                DB::commit();

                return back()->with('flash_success',$message);
            } 

            throw new Exception(tr('admin_moderator_is_activated_save_error'), 101);
           
        } catch( Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.moderators.index')->with('flash_error',$error);
        }

    }

    /**
     * Function: moderators_redeem_requests()
     * 
     * @uses To list Moderator Reedems 
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param request details
     *
     * @return View Page
     */
    public function moderators_redeem_requests(Request $request) {

        try {
            
            $base_query = RedeemRequest::orderBy('updated_at' , 'desc');

            $moderator_details = [];

            if( $request->moderator_id ) {

                $moderator_details = Moderator::find($request->moderator_id);

                if( count($moderator_details) == 0) {

                    throw new Exception(tr('admin_moderator_not_found'), 101);
                }
            
                $base_query = $base_query->where('moderator_id' , $request->moderator_id);
            }
            
            $redeem_requests = $base_query->get();
            
            return view('admin.moderators.redeems')
                        ->withPage('redeems')
                        ->with('sub_page' , 'redeems')
                        ->with('redeem_requests' , $redeem_requests)
                        ->with('moderator_details' , $moderator_details);
            
        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.moderators.index')->with('flash_error',$error);
        }
    
    }

    /**
     * Function: moderators_redeems_payout_direct()
     * 
     * @uses To payout for the selected redeem request with direct payment
     *
     * @created Anjana H 
     *
     * @updated Anjana H 
     *
     * @param - 
     *
     * @return success/failure message
     */
    public function moderators_redeems_payout_direct(Request $request) {

        try {
            
            DB::beginTransaction();

            $validator = Validator::make($request->all() , [
                'redeem_request_id' => 'required|exists:redeem_requests,id',
                'paid_amount' => 'required', 
                ]);

            if( $validator->fails() ) {

                $error = impolde(',', $validator->messages()->all());

                throw new Exception($error, 101);                

            }

            $redeem_request_details = RedeemRequest::find($request->redeem_request_id);

            if( count($redeem_request_details) == 0 ) {

                throw new Exception(tr('admin_reedem_request_not_found'), 101);
            }

            if( $redeem_request_details->status == REDEEM_REQUEST_PAID ) {

                throw new Exception(tr('admin_redeem_request_status_mismatch'), 101);
            } 

            $redeem_request_details->paid_amount = $redeem_request_details->paid_amount + $request->paid_amount;

            $redeem_request_details->status = REDEEM_REQUEST_PAID;

            $redeem_request_details->payment_mode = "direct";

            if( $redeem_request_details->save() ) {
                
                DB::commit();

            } else { 

                throw new Exception(tr('admin_redeem_request_save_error'), 101);
            }
        
            $redeem = Redeem::where('moderator_id', $redeem_request_details->moderator_id)->first();

            $redeem->paid += $request->paid_amount;

            $redeem->remaining = $redeem->total_moderator_amount - $redeem->paid;

            if( $redeem->save() ) {
                
                DB::commit();

            } else { 

                throw new Exception(tr('admin_redeem_request_save_error'), 101);
            }

            if ($redeem_request_details->moderator) {

                $redeem_request_details->moderator->paid_amount += $request->paid_amount;

                $redeem_request_details->moderator->remaining_amount = $redeem->total_moderator_amount - $redeem->paid;

                if( $redeem_request_details->moderator->save() ) {
                
                    DB::commit();

                    return redirect()->route('admin.moderators.redeems')->with('flash_success' , tr('action_success'));
        
                } else { 

                    throw new Exception(tr('admin_redeem_request_save_error'), 101);
                }    
            }

            throw new Exception(tr('admin_redeem_request_save_error'), 101);
        
        } catch (Exception $e) {
             
            DB::rollback();

            $error = $e->getMessage();

            return redirect()->route('admin.moderators.redeems')->with('flash_error',$error);
        }

    }

    /**
     * Function: moderators_payout_invoice()
     * 
     * @uses To list the categories
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param request details
     *
     * @return View Page
     */
    public function moderators_redeems_payout_invoice(Request $request) {

        try {
        
            $validator = Validator::make($request->all() , [
                'redeem_request_id' => 'required|exists:redeem_requests,id',
                'paid_amount' => 'required', 
                'moderator_id' => 'required'
                ]);

            if($validator->fails()) {

                $error = impolde(',',$validator->messages()->all());

                throw new Exception($error, 101);

            } 

            $redeem_request_details = RedeemRequest::find($request->redeem_request_id);

            if($redeem_request_details) {

                if ($redeem_request_details->status == REDEEM_REQUEST_PAID ) {

                    throw new Exception( tr('admin_redeem_request_status_mismatch'), 101);
                } 

                $invoice_data['moderator_details'] = $moderator_details = Moderator::find($request->moderator_id);

                $invoice_data['redeem_request_id'] = $request->redeem_request_id;

                $invoice_data['redeem_request_status'] = $redeem_request_details->status;

                $invoice_data['moderator_id'] = $request->moderator_id;

                $invoice_data['item_name'] = Setting::get('site_name')." - Checkout to"."$moderator_details ? $moderator_details->name : -";

                $invoice_data['payout_amount'] = $request->paid_amount;

                $data = json_decode(json_encode($invoice_data));

                return view('admin.moderators.payout')
                            ->withPage('moderators')
                            ->with('sub_page' , 'moderators')
                            ->with('data' , $data);                
            }

            throw new Exception(tr('admin_reedem_request_not_found'), 101);
           
        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.moderators.redeems')->with('flash_error',$error);
        }

    }

    /**
     * Function Name : moderators_videos()
     *
     * @uses Display the moderator videos list
     *
     * @param Moderator id
     *
     * @return Moderator video list details
     */
    public function moderators_videos(Request $request) {

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
                    ->where('uploaded_by',$request->moderator_id)
                    ->paginate(10);

        return view('admin.videos.index')
                    ->with('admin_videos' , $videos)
                    ->with('category' , [])
                    ->with('sub_category' , [])
                    ->with('genre' , [])
                    ->withPage('videos')
                    ->with('sub_page','view-videos');
   
    }


    /**
     * Function: categories_index()
     * 
     * @uses To list the categories
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param  
     *
     * @return view page
     */
    public function categories_index() {

        $categories = Category::select('categories.id',
                            'categories.name' , 'categories.picture',
                            'categories.is_series', 'categories.status',
                            'categories.is_approved', 'categories.created_by'
                        )
                        ->orderBy('categories.created_at', 'desc')
                        ->distinct('categories.id')
                        ->paginate(10);

        return view('admin.categories.index')
                    ->withPage('categories')
                    ->with('sub_page','categories-view')
                    ->with('categories' , $categories);
    }

    /**
     * Function Name : categories_create()
     *
     * @uses To create a category object details
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return View page
     */
    public function categories_create() {

        $category_details = new Category;

        return view('admin.categories.create')
                    ->with('page' , 'categories')
                    ->with('sub_page','categories-create')
                    ->with('category_details',$category_details);
    }

    /**
     * Function Name : categories_edit()
     *
     * @uses To display and update category object details based on the category id
     *
     * @created  Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $category_id
     *
     * @return View page
     */
    public function categories_edit(Request $request) {

        try {
          
            $category_details = Category::find($request->category_id);

            if( count($category_details) == 0 ) {

                throw new Exception( tr('admin_category_not_found'), 101);
            } 

            return view('admin.categories.edit')
                    ->with('page' , 'categories')
                    ->with('sub_page','categories-view')
                    ->with('category_details',$category_details);
        
        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.categories.index')->with('flash_error',$error);
        }

    }

    /**
     * Function Name : categories_save()
     *
     * @uses To save the category object details of new/existing based on details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $category_id , (request) category details
     *
     * @return success/error message
     */
    public function categories_save(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make( $request->all(), array(
                        // 'name' => $request->category_id ? 'required|regex:/^[a-z\d\-. \'\s]+$/i|min:2|max:100|unique:categories,name,'.$request->category_id : 'required|regex:/^[a-z\d\-. \'\s]+$/i|min:2|max:100|unique:categories,name',
                        'name' => $request->category_id ? 'required|min:2|max:100|unique:categories,name,'.$request->category_id : 'required|min:2|max:100|unique:categories,name',
                        'picture' => $request->category_id ? 'mimes:jpeg,jpg,bmp,png' : 'required|mimes:jpeg,jpg,bmp,png',
                    )
            );
           
            if( $validator->fails() ) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);
            } 

            if( $request->category_id != '') {

                $category_details = Category::find($request->category_id);
                
                $message = tr('admin_category_update_success');
                
                if($request->hasFile('picture')) {

                    Helper::delete_picture($category_details->picture, "/uploads/images/categories/");
                }

            } else {

                $message = tr('admin_category_create_success');

                //Add New Category object
                $category_details = new Category;
                $category_details->is_approved = DEFAULT_TRUE;
                $category_details->created_by = ADMIN;

            }

            $category_details->name = $request->has('name') ? $request->name : '';

            $category_details->is_series = $request->has('is_series') == YES ? $request->is_series : NO ;

            $category_details->status = APPROVED;
            
            if($request->hasFile('picture') && $request->file('picture')->isValid()) {
                
                $category_details->picture = Helper::normal_upload_picture($request->file('picture'), "/uploads/images/categories/" );
            }

            if( $category_details->save() ) {

                DB::commit();
                
                if( Setting::get('track_user_mail') ) {

                    user_track("StreamHash - Category Created");
                }

                return redirect()->route('admin.categories.view' ,['category_id' => $category_details->id])->with('flash_success', $message);
            }                    

            throw new Exception(tr('admin_category_save_error'), 101);
            
        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    
    }

    /**
     * Function: categories_view()
     * 
     * @uses To display category details based on category id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $category_id
     *
     * @return view page
     */
    public function categories_view(Request $request) {

        try {

            $category_details = Category::find($request->category_id);
            
            if( count($category_details) == 0 ) {

                throw new Exception(tr('admin_category_not_found'), 101);
            } 

            return view('admin.categories.view')
                    ->with('page' ,'categories')
                    ->with('sub_page' ,'categories-view')
                    ->with('category_details' ,$category_details);
           
        } catch (Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.categories')->with('flash_error',$error);
        }

    }

    /**
     * Function Name : categories_status_change()
     *
     * @uses To update category is_approved to approved/declined based on category id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $category_id
     *
     * @return success/error message
     */
    public function categories_status_change(Request $request) {

        try {

            DB::beginTransaction();
       
            $category_details = Category::find($request->category_id);

            if( count( $category_details) == 0) {
                
                throw new Exception(tr('admin_category_not_found'), 101);
            } 

            $category_details->is_approved = $category_details->is_approved == CATEGORY_APPROVED ? CATEGORY_DECLINED : CATEGORY_APPROVED;

            $message = $category_details->is_approved == CATEGORY_APPROVED ? tr('admin_category_approved_success') : tr('admin_category_declined_success');

            if( $category_details->save() ) {

                DB::commit();

                if ( $category_details->is_approved == CATEGORY_DECLINED ) {

                    foreach($category_details->subCategory as $sub_category) {               
                        $sub_category->is_approved = DECLINED;

                        if( $sub_category->save() ) {

                            DB::commit();

                        } else {

                            throw new Exception(tr('admin_category_is_approve_save_error'), 101);                            
                        }
                    } 

                    foreach($category_details->adminVideo as $video)
                    {                
                        $video->is_approved = DECLINED;
                        
                        if( $video->save() ) {

                            DB::commit();

                        } else {

                            throw new Exception(tr('admin_category_is_approve_save_error'), 101);                            
                        }
                    } 

                    foreach( $category_details->genre as $genre )
                    {                
                        $genre->is_approved = DECLINED;
                        
                        if( $genre->save() ) {

                            DB::commit();

                        } else {

                            throw new Exception(tr('admin_category_is_approve_save_error'), 101);                            
                        }
                    } 
                }

                return back()->with('flash_success',$message);
            } 
            
            throw new Exception(tr('admin_category_is_approve_save_error'), 101);
            
        } catch( Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.categories.index')->with('flash_error',$error);
        }

    }

    /**
     * Function: categories_delete()
     * 
     * @uses To delete the category object based on category id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function categories_delete(Request $request) {

        try {

            DB::beginTransaction();
            
            $category_details = Category::where('id' , $request->category_id)->first();

            if( count($category_details) == 0 ) {  

                throw new Exception(tr('admin_category_not_found'), 101);
            }

            Helper::delete_picture($category_details->picture, "/uploads/images/categories/");
            
            if( $category_details->delete() ) {

                DB::commit();

                return redirect()->route('admin.categories.index')->with('flash_success',tr('admin_category_delete_success'));
            } 
            
            throw new Exception(tr('admin_category_delete_error'), 101);

        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function Name : sub_categories_index()
     *
     * @uses To create a sub_category object details
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return View page
     */
    public function sub_categories_index(Request $request) {

        try {

            $category_details = Category::find($request->category_id);

            if( count($category_details) == 0 ) {

                throw new Exception(tr('admin_category_not_found'), 101);
            }

            $sub_categories = SubCategory::where('category_id' , $request->category_id)
                            ->select(
                                    'sub_categories.id as id',
                                    'sub_categories.name as sub_category_name',
                                    'sub_categories.description',
                                    'sub_categories.is_approved',
                                    'sub_categories.created_by'
                                    )
                            ->orderBy('sub_categories.created_at', 'desc')
                            ->paginate(10);

            $sub_category_images = SubCategoryImage::where('sub_category_id' , $request->sub_category_id)
                                ->orderBy('position' , 'ASC')->get();

            return view('admin.categories.sub_categories.index')
                        ->with('page' , 'sub_categories')
                        ->with('sub_page','sub_categories-create')
                        ->with('category_details' , $category_details)
                        ->with('sub_category_images' , $sub_category_images)
                        ->with('sub_categories',$sub_categories);

        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function Name : sub_categories_create()
     *
     * @uses To create a sub_category object details
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return View page
     */
    public function sub_categories_create(Request $request) {

        $category_details = Category::find($request->category_id);

        $sub_category_details = new SubCategory;

        $sub_category_images = new SubCategoryImage; 

        return view('admin.categories.sub_categories.create')
                ->with('page' ,'categories')
                ->with('sub_page' ,'add-category')
                ->with('category_details' , $category_details)
                ->with('sub_category_details' , $sub_category_details)
                ->with('sub_category_images' , $sub_category_images);
    }

    /**
     * Function Name : sub_categories_edit()
     *
     * @uses To display and update sub_category object details based on the sub_category id
     *
     * @created  Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $sub_category_id
     *
     * @return View page
     */
    public function sub_categories_edit(Request $request) {

        try {
          
            $sub_category_details = SubCategory::find($request->sub_category_id);

            if( count($sub_category_details) == 0 ) {

                throw new Exception( tr('admin_sub_category_not_found'), 101);
            } 

            $category_details = Category::find($request->category_id);

            $sub_category_images = SubCategoryImage::where('sub_category_id' , $request->sub_category_id)->orderBy('position' , 'ASC')->get();

            return view('admin.categories.sub_categories.create')
                    ->with('page' ,'categories')
                    ->with('sub_page' ,'add-category')
                    ->with('category_details' , $category_details)
                    ->with('sub_category_details' , $sub_category_details)
                    ->with('sub_category_images' , $sub_category_images);
        
        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.sub_categories.index')->with('flash_error',$error);
        }

    }

    /**
     * Function Name : sub_categories_save()
     *
     * @uses To save the sub_category object details of new/existing based on details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $sub_category_id , (request) sub_category details
     *
     * @return success/error message
     */
    public function sub_categories_save(Request $request) {

        try {
            
            DB::beginTransaction();

            $validator = Validator::make( $request->all(), array(
                            'category_id' => $request->category_id ? 'required|integer|exists:categories,id' : 'required|integer|exists:categories,id',
                            'sub_category_id' => $request->sub_category_id ? 'required|integer|exists:sub_categories,id' : '' ,
                            // 'name' => 'required|regex:/^[a-z\d\-. \'\s]+$/i|min:2|max:100',
                            'name' => 'required|min:2|max:100',
                            'picture1' => $request->category_id ? 'mimes:jpeg,jpg,bmp,png' : 'required|mimes:jpeg,jpg,bmp,png' ,
                    )
            );
           
            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);
                
            } 

            if($request->sub_category_id != '') {

                $sub_category = SubCategory::find($request->sub_category_id);

                $message = tr('admin_sub_category_update_success');

                if($request->hasFile('picture1')) {
                    
                    Helper::delete_picture($sub_category->picture1, "/uploads/images/sub_categories/");
                }

            } else {

                $message = tr('admin_sub_category_create_success');

                //Add New SubCategory
                $sub_category = new SubCategory;
                $sub_category->is_approved = DEFAULT_TRUE;
                $sub_category->created_by = ADMIN;
            }

            $sub_category->category_id = $request->has('category_id') ? $request->category_id : '';
            
            if($request->has('name')) {
                $sub_category->name = $request->name;
            }

            if( $request->has('description')) {
                $sub_category->description =  $request->description;   
            }

            if( $sub_category->save()) { // Otherwise it will save empty values

                DB::commit();
                
                if($request->hasFile('picture')) {

                    sub_category_image($request->file('picture') , $sub_category->id, 1, "/uploads/images/sub_categories/");
                }

                if($sub_category) {

                    if (Setting::get('track_user_mail')) {

                        user_track("StreamHash - Sub category Created");
                    }
                    
                    return redirect()->route('admin.sub_categories.view', ['category_id' => $sub_category->category_id ,'sub_category_id' => $sub_category->id] )->with('flash_success', $message);
                } 

            } 
           
            throw new Exception(tr('admin_sub_category_save_error'), 101);
       
        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function: sub_categories_view()
     * 
     * @uses to display Sub Category details based on Sub Category id
     *
     * @created Anjana H
     *
     * @updated Anjana H
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
            } 

            $sub_category_details = $sub_category_details->leftjoin('sub_category_images','sub_category_images.sub_category_id','=','sub_categories.id')
            ->where('sub_categories.id','=', $request->sub_category_id)->first();

            return view('admin.categories.sub_categories.view')
                        ->with('page' ,'categories')
                        ->with('sub_page' ,'categories-view')
                        ->with('sub_category_details' ,$sub_category_details);
           
        } catch (Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.sub_categories.index',['category_id' => $request->category_id] )->with('flash_error',$error);

        }

    }

    /**
     * Function Name : sub_categories_status_change()
     *
     * @uses To update sub category is_approved to approved/declined based on sub category id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $sub_category_id
     *
     * @return success/error message
     */
    public function sub_categories_status_change(Request $request) {
        
        try {

            DB::beginTransaction();

            $sub_category_details = SubCategory::find($request->sub_category_id);

            if( count($sub_category_details) ==0 ) { 

                throw new Exception(tr('admin_sub_category_not_found'), 101);
            }
            
            $sub_category_details->is_approved = $sub_category_details->is_approved == SUB_CATEGORY_APPROVED ? SUB_CATEGORY_DECLINED : SUB_CATEGORY_APPROVED ;
            
            $message = $sub_category_details->is_approved == SUB_CATEGORY_APPROVED ? tr('admin_sub_category_approved_success') : tr('admin_sub_category_declined_success');
           
            if( $sub_category_details->save() ) { 

                if ( $sub_category_details->is_approved == CATEGORY_DECLINED ) {

                    foreach($sub_category_details->adminVideo as $video) {    

                        $video->is_approved = $request->status;

                        if( $video->save() ) {


                        } else {

                            throw new Exception(tr('admin_sub_category_is_approve_save_error'), 101);
                        }
                    } 

                    foreach($sub_category_details->genres as $genre) {   

                        $genre->is_approved = CATEGORY_DECLINED;
                        
                        if( $genre->save() ) {

                        } else {

                            throw new Exception(tr('admin_sub_category_is_approve_save_error'), 101);
                        }
                    } 

                }

                DB::commit();
            
                return back()->with('flash_success', $message);
            }  

            throw new Exception(tr('admin_sub_category_is_approve_save_error'), 101);
            
        } catch (Exception $e) {
            
            DB::rollback();
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function: sub_categories_delete()
     * 
     * @uses To delete the category object based on category id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function sub_categories_delete(Request $request) {

        try {

            DB::beginTransaction();
            
            $sub_category_details = SubCategory::where('id' , $request->sub_category_id)->first();

            if( count($sub_category_details) == 0 ) {  

                throw new Exception(tr('admin_sub_category_not_found'), 101);
            }

            $category_id = $sub_category_details->category_id;

            Helper::delete_picture($sub_category_details->picture, "/uploads/images/sub_categories/");
            
            if( $sub_category_details->delete() ) {

                DB::commit();

                return redirect()->route('admin.sub_categories.index', ['category_id' => $category_id ])->with('flash_success',tr('admin_sub_category_delete_success'));
            } 

            throw new Exception(tr('admin_sub_category_delete_error'), 101);

        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }

    }

    /**
     * Function: genres_index()
     * 
     * @uses To list the genres details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return view page
     */
    public function genres_index(Request $request) {

        $sub_category_details = SubCategory::find($request->sub_category_id);

        $genres = Genre::where('sub_category_id' , $request->sub_category_id)
                        ->leftjoin('sub_categories', 'sub_categories.id', '=', 'genres.sub_category_id')
                        ->leftjoin('categories', 'categories.id', '=', 'genres.category_id')
                        ->select('genres.id as genre_id',
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
                                    'genres.position as position')
                        ->orderBy('genres.created_at', 'desc')
                        ->paginate(10);

        return view('admin.categories.sub_categories.genres.index')
                    ->withPage('categories')
                    ->with('sub_page','view-categories')
                    ->with('sub_category_details' , $sub_category_details)
                    ->with('genres' , $genres);
    
    }

    /**
     * Function: genres_create()
     * 
     * @uses To create a genres object details object based on sub category id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function genres_create(Request $request) {

        try {
            
            $sub_category_details = SubCategory::find($request->sub_category_id);

            if( count($sub_category_details ) == 0 ) {

               throw new Exception(tr('admin_sub_category_not_found'), 101);
            }

            $genre_details = new Genre;
        
            return view('admin.categories.sub_categories.genres.create')
                    ->with('page' ,'categories')
                    ->with('sub_page' ,'add-category')
                    ->with('sub_category_details' , $sub_category_details)
                    ->with('genre_details', $genre_details);            

        } catch (Exception $e) {

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function Name : genres_edit()
     *
     * @uses To display and update genres object details based on the genres id
     *
     * @created  Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $sub_category_id, $genres_id
     *
     * @return View page
     */
    public function genres_edit(Request $request) {

        try {
            
            $sub_category_details = SubCategory::find($request->sub_category_id);

            if( count($sub_category_details) == 0 ) {

                throw new Exception(tr('admin_genre_not_found'), 101);
            }

            $genre_details = Genre::find($request->genre_id);
        
            return view('admin.categories.sub_categories.genres.edit')
                        ->with('page' ,'categories')
                        ->with('sub_page' ,'add-category')
                        ->with('sub_category_details' , $sub_category_details)
                        ->with('genre_details', $genre_details);

        } catch (Exception $e) {

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function Name : genres_save()
     *
     * @uses To save the gener object details of new/existing based on details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $gener_id , (request) gener details
     *
     * @return success/error message
     *
     */
    public function genres_save(Request $request) {

       try {
            
            $validator = Validator::make( $request->all(), array(
                    'category_id' => 'required|integer|exists:categories,id',
                    'sub_category_id' => 'required|integer|exists:sub_categories,id',
                    // 'name' => 'required|regex:/^[a-z\d\-.\s]+$/i|min:2|max:100',
                    'name' => 'required|min:2|max:100',
                    'video'=> $request->genre_id ? 'mimes:mkv,mp4,qt' : 'required|mimes:mkv,mp4,qt',
                    'image'=> $request->genre_id ? 'mimes:jpeg,jpg,bmp,png' : 'required|mimes:jpeg,jpg,bmp,png',
                )
            );

            if( $validator->fails() ) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);
            }

            $genre_details = $request->genre_id ? Genre::find($request->genre_id) : new Genre;

            if( $genre_details->id ) {

                $position = $genre_details->position;

            } else {

                // To order the position of the genres
                $position = 1;

                if($check_position = Genre::where('sub_category_id' , $request->sub_category_id)
                                ->orderBy('position' , 'desc')
                                ->first()) {

                    $position = $check_position->position +1;
                } 
            }

            $genre_details->category_id = $request->category_id;
            $genre_details->sub_category_id = $request->sub_category_id;
            $genre_details->name = $request->name;

            $genre_details->position = $position;
            $genre_details->status = DEFAULT_TRUE;
            $genre_details->is_approved = GENRE_APPROVED;
            $genre_details->created_by = ADMIN;

            if($request->hasFile('video')) {

                if ($genre_details->id) {

                    if ($genre_details->video) {

                        Helper::delete_picture($genre_details->video, '/uploads/videos/original/');
                    }  
                }

                $video = Helper::video_upload($request->file('video'), 1);

                $genre_details->video = $video['db_url'];  
            }

            if( $request->hasFile('image') ) {

                if( $genre_details->id ) {

                    if ( $genre_details->image ) {

                        Helper::delete_picture($genre_details->image,'/uploads/images/genres/');  
                    }  
                }

                $genre_details->image =  Helper::normal_upload_picture($request->file('image'), '/uploads/images/genres/');
            }

            if($request->hasFile('subtitle')) {

                if( $genre_details->id ) {

                    if( $genre_details->subtitle ) {

                        Helper::delete_picture($genre_details->subtitle, "/uploads/subtitles/");
                    }  
                }

                $genre_details->subtitle =  Helper::subtitle_upload($request->file('subtitle'));
            }

            if( $genre_details->save() ) {

                DB::commit();

            } else {

                throw new Exception(tr('admin_genre_save_error'),101);
            }

            $message = ($request->genre_id) ? tr('admin_genre_update_success') : tr('admin_genre_create_success');

            $genre_details->unique_id = $genre_details->id;

            if( $genre_details->save() ) {

                DB::commit();

                if (Setting::get('track_user_mail')) {

                    user_track("StreamHash - Genre Created");
                }

                return back()->with('flash_success', $message);
            } 

            throw new Exception(tr('admin_genre_save_error'),101);
           
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function Name : genres_view()
     *
     * @uses To display the selected genre details
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param -
     *
     * @return view page
     */
    public function genres_view(Request $request) {

        try {

            $genre_details = Genre::where('genres.id' , $request->genre_id)
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

            if( count($genre_details) == 0 ) {

                throw new Exception(tr('admin_genre_not_found'), 101);
            }

            return view('admin.categories.sub_categories.genres.view')
                        ->withPage('categories')
                        ->with('sub_page','view-categories')
                        ->with('genre_details' , $genre_details);

        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.categories.index')->with('flash_error',$error);
        }
        
    }

    /**
     * Function Name : genre_position_change()
     *
     * Change position of the genre
     *
     * @param object $request - Genre id & position of the genre
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @return success/failure message
     */
    public function genre_position_change(Request $request) {

        try {

            DB::beginTransaction();

            $genre_details = Genre::find($request->genre_id);

            if( count($genre_details) == 0 ) {

                throw new Exception( tr('admin_genre_not_found'));
            }

            $changing_row_position = $genre_details->position;

            $change_genre = Genre::where('position', $request->position)
                            ->where('sub_category_id', $genre_details->sub_category_id)
                            ->where('is_approved', DEFAULT_TRUE)
                            ->first();

            if( !$change_genre ) {

                throw new Exception( tr('admin_given_position_not_exits'));
            }

            $new_row_position = $change_genre->position;

            $genre_details->position = $new_row_position;

            if( $genre_details->save() ) {

                DB::commit();

            } else {

                throw new Exception(tr('admin_genre_save_error'));
            }

            $change_genre->position = $changing_row_position;

            if( $change_genre->save() ) {

                DB::commit();

                return back()->with('flash_success', tr('admin_genre_position_updated_success'));

            } 
            
            throw new Exception(tr('admin_genre_save_error'));
           
        } catch (Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function Name : genres_status_change()
     *
     * @uses To update genre status to approve/decline based on genre id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $genre_id
     *
     * @return success/error message
     */
    public function genres_status_change(Request $request) {

        try {

            DB::beginTransaction();

            $genre_details = Genre::find($request->genre_id);

            if( count($genre_details) == 0 ) {

                throw new Exception(tr('admin_genre_not_found'));
            }

            $genre_details->is_approved = $genre_details->is_approved == APPROVED ? DECLINED : APPROVED ;

            $position = $genre_details->position;

            $sub_category_id = $genre_details->sub_category_id;

            if( $genre_details->is_approved == DECLINED ) {

                foreach($genre_details->adminVideo as $video) {

                    $video->is_approved = $request->status;

                    $video->save();
                }

                $next_genres = Genre::where('sub_category_id', $sub_category_id)
                                ->where('position', '>', $position)
                                ->orderBy('position', 'asc')
                                ->where('is_approved', DEFAULT_TRUE)
                                ->get();

                if( count($next_genres) > 0 ) {

                    foreach ($next_genres as $key => $value) {
                        
                        $value->position = $value->position - 1;

                        if ($value->save()) {

                            DB::commit();

                        } else {

                            throw new Exception(tr('admin_genre_save_error'));
                        }
                    }
                }

                $genre_details->position = 0;

            } else {

                $get_genre_position = Genre::where('sub_category_id', $sub_category_id)
                                ->orderBy('position', 'desc')
                                ->where('is_approved', DEFAULT_TRUE)
                                ->first();

                if( $get_genre_position ) {

                    $genre_details->position = $get_genre_position->position + 1;
                }
            }

            if ($genre_details->save()) {

                $message = $genre_details->is_approved == APPROVED ? tr('admin_genre_approve_success') : tr('admin_genre_decline_success') ;

                DB::commit();

                return back()->with('flash_success', $message); 
            } 

            throw new Exception(tr('admin_genre_is_approve_save_error'));
            
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('flash_error', $e->getMessage());
        }    
    }

    /**
     * Function Name : genres_delete()
     *
     * @uses to delete the selected genre
     *
     * @created  
     *
     * @updated 
     *
     * @param 
     *
     * @return view page
     */
    public function genres_delete(Request $request) {

        try {

            DB::beginTransaction();
            
            $genre_details = Genre::where('id',$request->genre_id)->first();
            
            if(count($genre_details) == 0 ) {

                throw new Exception(tr('admin_genre_not_found'), 101);
            }

            Helper::delete_picture($genre_details->image,'/uploads/images/'); 

            if ($genre_details->video) {

                Helper::delete_picture($genre_details->video, '/uploads/videos/original/');   
            }

            if ($genre_details->subtitle) {

                Helper::delete_picture($genre_details->subtitle, "/uploads/subtitles/");
            }  

            $position = $genre_details->position;

            $sub_category_id = $genre_details->sub_category_id;

            if( $genre_details->delete()) {

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

                DB::commit();

                return back()->with('flash_success', tr('admin_not_genre_del'));
            } 

            throw new Exception(tr('genre_not_saved'),101);
            
        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    
    }
    
    /**
     * Function: pages_index()
     * 
     * @uses To list the static_pages
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return view page
     */
    public function pages_index() {

        $pages = Page::orderBy('created_at' , 'desc')->paginate(10);

        return view('admin.pages.index')
                    ->with('page','pages')
                    ->with('sub_page','pages-view')
                    ->with('pages',$pages);
    }

    /**
     * Function Name : pages_create()
     *
     * @uses To list out pages object details
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return View page
     */
    public function pages_create() {

        $page_details = new Page;

        return view('admin.pages.create')
                    ->with('page' , 'pages')
                    ->with('sub_page',"pages-create")
                    ->with('page_details', $page_details);
    }
      
    /**
     * Function Name : pages_edit()
     *
     * @uses To display and update pages object details based on the pages id
     *
     * @created  Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $static_page_id
     *
     * @return View page
     */
    public function pages_edit(Request $request) {

        try {
          
            $page_details = Page::find($request->page_id);

            if( count($page_details) == 0 ) {

                throw new Exception( tr('admin_page_not_found'), 101);
            } 

            return view('admin.pages.edit')
                    ->with('page' , 'pages')
                    ->with('sub_page','pages-view')
                    ->with('page_details',$page_details);

        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.pages.index')->with('flash_error',$error);
        }
    }

    /**
     * Function Name : pages_save()
     *
     * @uses To save the page object details of new/existing based on details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $page_id , (request) page details
     *
     * @return success/error message
     */
    public function pages_save(Request $request) {

        try {
            
            DB::beginTransaction();

            $validator = Validator::make($request->all() , array(
                'type' => $request->page_id ? '' : 'required',
                'heading' => 'required|max:255',
                'description' => 'required',
            ));

            if( $validator->fails() ) {

                $error = implode(',',$validator->messages()->all());

                throw new Exception($error, 101);                
            } 

            if( $request->has('page_id') ) {

                $page_details = Page::find($request->page_id);

            } else {

                if(Page::count() < Setting::get('no_of_static_pages')) {

                    if( $request->type != 'others' ) {

                        $check_page_type = Page::where('type',$request->type)->first();
                        
                        if($check_page_type){

                            throw new Exception(tr('admin_page_exists').$request->type , 101);
                        }
                    }
                    
                    $page_details = new Page;
                    
                } else {

                    throw new Exception(tr('admin_page_exists').$request->type , 101);
                }                    
            }

            if( $page_details ) {

                $page_details->type = $request->type ? $request->type : $page_details->type;

                $page_details->heading = $request->heading ? $request->heading : $page_details->heading;

                $page_details->description = $request->description ? $request->description : $page_details->description;

                if( $page_details->save() ) {

                    DB::commit();

                    return back()->with('flash_success',tr('admin_page_create_success'));
                }

                throw new Exception(tr('admin_page_save_error'), 101);                
            }
            
            throw new Exception(tr('admin_page_save_error'), 101);
                
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return redirect()->route('admin.pages.index')->with('flash_error',$error);
        }

    }

    /**
     * Function: pages_view()
     * 
     * @uses To display pages details based on pages id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $page_id
     *
     * @return view page
     */
    public function pages_view(Request $request) {

        try {

            $page_details = Page::find($request->page_id);
            
            if( count($page_details) == 0 ) {

                throw new Exception(tr('admin_page_not_found'), 101);
            }

            return view('admin.pages.view')
                    ->with('page' ,'pages')
                    ->with('sub_page' ,'pages-view')
                    ->with('page_details' ,$page_details);

        } catch (Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.pages.index')->with('flash_error',$error);
        }
    }

    /**
     * Function: pages_delete()
     * 
     * @uses To delete the page object based on page id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function pages_delete(Request $request) {

        try {

            DB::beginTransaction();
            
            $page_details = Page::where('id' , $request->page_id)->first();

            if( count($page_details) == 0 ) {  

                throw new Exception(tr('admin_page_not_found'), 101);
            }

            Helper::delete_picture($page_details->picture, "/uploads/images/pages/");
            
            if( $page_details->delete() ) {

                DB::commit();

                return redirect()->route('admin.pages.index')->with('flash_success',tr('admin_page_delete_success'));
            } 

            throw new Exception(tr('admin_page_delete_error'), 101);               
            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    
    /**
     * Function Name : cast_crews_index()
     *
     * @uses To list out details of cast and crews
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return response of html page with details
     */
    public function cast_crews_index(Request $request) {

        $cast_crews = CastCrew::orderBy('created_at', 'desc')->paginate(10);
    
        return view('admin.cast_crews.index')
                ->with('page', 'cast-crews')
                ->with('sub_page', 'cast-crew-index')
                ->with('cast_crews', $cast_crews);
    }

    /**
     * Function Name : cast_crews_create()
     *
     * @uses To create a CastCrew object details
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return View page
     */
    public function cast_crews_create() {

        $cast_crew_details = new CastCrew;

        return view('admin.cast_crews.create')
                    ->with('page' , 'cast-crews')
                    ->with('sub_page','cast-crews-create')
                    ->with('cast_crew_details',$cast_crew_details);
    }

    /**
     * Function Name : cast_crews_edit()
     *
     * @uses To display and update cast_crew object details based on the cast_crew id
     *
     * @created  Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $cast_crew_id
     *
     * @return View page
     */
    public function cast_crews_edit(Request $request) {

        try {
          
            $cast_crew_details = CastCrew::find( $request->cast_crew_id );

            if( count($cast_crew_details) == 0 ) {

                throw new Exception( tr('admin_cast_crew_not_found'), 101);
            }

            return view('admin.cast_crews.edit')
                        ->with('page' , 'cast-crews')
                        ->with('sub_page','cast-crews-view')
                        ->with('cast_crew_details', $cast_crew_details);
            
        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.cast_crews.index')->with('flash_error',$error);
        }

    }

    /**
     * Function Name : cast_crews_save()
     *
     * @uses To save the details of the cast and crews
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) cast_crew_id, details
     *
     * @return success/failure message
     */
    public function cast_crews_save(Request $request) {

        try {
                
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'cast_crew_id'=>'exists:cast_crews,id',
                'name'=>'required|min:2|max:128',
                'image'=>$request->cast_crew_id ? 'mimes:jpeg,jpg,png' : 'required|mimes:jpeg,png,jpg',
                'description'=>'required'
            ]);

            if( $validator->fails() ) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error);
            } 

            $cast_crew_details = $request->cast_crew_id ? CastCrew::where('id', $request->cast_crew_id)->first() : new CastCrew;

            $cast_crew_details->name = $request->name;

            $cast_crew_details->unique_id = $cast_crew_details->name;

            if ($request->hasFile('image')) {

                if ($request->cast_crew_id) {

                    Helper::delete_picture($cast_crew_details->image, '/uploads/images/cast_crews/');
                }

                $cast_crew_details->image = Helper::normal_upload_picture($request->file('image'), '/uploads/images/cast_crews/');
            }

            $cast_crew_details->description = $request->description;

            $cast_crew_details->status = DEFAULT_TRUE; // By default it will be 1, future it may vary

            if( $cast_crew_details->save() ) {

                DB::commit();

                $message = $request->cast_crew_id ? tr('admin_cast_crew_update_success') : tr('admin_cast_crew_create_success'); 

                return redirect()->route('admin.cast_crews.view', ['cast_crew_id'=>$cast_crew_details->id] )->with('flash_success',$message );
            } 

            throw new Exception(tr('admin_cast_crew_save_error'));

        } catch (Exception $e) {

            DB::rollback();

            $error =$e->getMessage();
            
            return back()->with('flash_error', $error);
        }
    }

    /**
     * Function Name : cast_crews_view()
     *
     * @uses To display cast_crew details based on cast_crew id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $cast_crew_id 
     *
     * @return view page
     */
    public function cast_crews_view(Request $request) {

        try {
               
            $cast_crew_details = CastCrew::find( $request->cast_crew_id );
            
            if( count($cast_crew_details) == 0 ) {

                throw new Exception(tr('admin_cast_crew_not_found'), 101);
            } 

            return view('admin.cast_crews.view')
                        ->with('page','cast-crews')
                        ->with('sub_page','cast-crews-view')
                        ->with('cast_crew_details' , $cast_crew_details);
            
        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.cast_crews.index')->with('flash_error',$error);
        }

    } 

    /**
     * Function: cast_crews_delete()
     * 
     * @uses To delete the cast_crew object based on cast_crew id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function cast_crews_delete(Request $request) {

        try {
            
            DB::beginTransaction();

            $cast_crew_details = CastCrew::where('id',$request->cast_crew_id)->first();

            $image = $cast_crew_details->image;

            if( $cast_crew_details->delete() ) {
                
                DB::commit();
                
                if ($image) {

                    Helper::delete_picture($image, '/uploads/cast_crews/');
                }

                return redirect(route('admin.cast_crews.index'))->with('flash_success', tr('cast_crew_delete_success'));
            }

            throw new Exception(tr('admin_cast_crew_delete_error'), 101);
                   
        } catch( Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return redirect()->route('admin.cast_crews.index')->with('flash_error',$error);
        }
    }
    
    /**
     * Function Name : cast_crews_status_change()
     *
     * @uses To update cast_crew is_approved to approved/declined based on cast_crew id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $cast_crew_id
     *
     * @return success/error message
     */
    public function cast_crews_status_change(Request $request) {

        try {
            
            DB::beginTransaction();

            $cast_crew_details = CastCrew::where('id', $request->cast_crew_id)->first();

            if( count($cast_crew_details) == 0 ) {

                throw new Exception(tr('cast_crew_not_found'), 101);
            }

            $cast_crew_details->status = $cast_crew_details->status == CAST_APPROVED ? CAST_DECLINED : CAST_APPROVED;

            if( $cast_crew_details->save() ) {

                if ( $cast_crew_details->status == CAST_DECLINED) {

                    if (count($cast_crew_details->videoCastCrews) > 0) {


                        foreach($cast_crew_details->videoCastCrews as $value) {

                            $value->delete();  
                            
                        }
                    }
                }

                DB::commit();

                $message = $cast_crew_details->status == CAST_APPROVED ? tr('cast_crew_approve_success') : tr('cast_crew_decline_success'); 

                return redirect()->route('admin.cast_crews.index')->with('flash_success',$message);
            
            } 
                
            throw new Exception(tr('cast_crew_status_error'), 101);
            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return redirect()->route('admin.cast_crews.index')->with('flash_error',$error);
        }

    }

    /**
     * Function Name: coupons_index()
     *
     * @uses list out coupon details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param -
     *
     * @return view page
     */
    public function coupons_index() {

        $coupons = Coupon::orderBy('updated_at','desc')->paginate(10);

        return view('admin.coupons.index')
                    ->with('page','coupons')
                    ->with('sub_page','coupons-view')
                    ->with('coupons',$coupons);    
    }

    /**
     * Function Name: coupons_create()
     *
     * @uses To create a counpon details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param -
     *
     * @return view page
     */
    public function coupons_create() {

        $coupon_details = new Coupon;

        $coupon_details->expiry_date = date('Y-m-d');

        return view('admin.coupons.create')
                ->with('page','coupons')
                ->with('sub_page','coupons-create')
                ->with('coupon_details', $coupon_details);
    }

    /**
     * Function Name: coupons_edit() 
     *
     * @uses Edit the coupon details and get the coupon edit form for 
     *
     * @created Anjana H
     *
     * @updated Anjana
     *
     * @param Coupon id
     *
     * @return Get the html form
     */
    public function coupons_edit(Request $request){

        try {

            $coupon_details = Coupon::find( $request->coupon_id );

            if( count ($coupon_details) == 0 ){

                throw new Exception(tr('admin_coupon_not_found'), 101);
            } 

            return view('admin.coupons.edit')
                        ->with('page','coupons')
                        ->with('sub_page','coupons-view')
                        ->with('coupon_details',$coupon_details);
            
        } catch(Exception $e) {

            $error = $e->getMessage();

            return redirect()->back()->with('flash_error',$error);
        }   

    }

        /**
     * Function Name: coupons_save()
     *
     * @uses To save the coupon object details of new/existing based on details
     *
     * @created Maheswari
     *
     * @updated Anjana H
     *
     * @param request coupon_id, details
     *
     * @return success/error message
     */
    public function coupons_save(Request $request){

        try {
            
            $validator = Validator::make($request->all(),[
                'id'=>'exists:coupons,id',
                'title'=>'required',
                'coupon_code'=>$request->coupon_id ? 'required|max:10|min:1|unique:coupons,coupon_code,'.$request->coupon_id : 'required|unique:coupons,coupon_code|min:1|max:10',
                'amount'=>'required|numeric|min:1|max:5000',
                'amount_type'=>'required',
                'expiry_date'=>'required|date_format:d-m-Y|after:today',
                'no_of_users_limit'=>'required|numeric|min:1|max:1000',
                'per_users_limit'=>'required|numeric|min:1|max:100',
            ]);

            if( $validator->fails() ) {

                $error = implode(',',$validator->messages()->all());

                throw new Exception( $error, 101);                
            }

            if( $request->coupon_id != '' ) {
                        
                    $coupon_detail = Coupon::find($request->coupon_id); 

                    $message=tr('admin_coupon_update_success');

            } else {

                $coupon_detail = new Coupon;

                $coupon_detail->status = APPROVED;

                $message = tr('admin_coupon_create_success');
            }

            // Check the condition amount type equal zero mean percentage
            
            if( $request->amount_type == PERCENTAGE ) {

                // Amount type zero must should be amount less than or equal 100 only
                if($request->amount <= 100){

                    $coupon_detail->amount_type = $request->has('amount_type') ? $request->amount_type :DEFAULT_FALSE;
     
                    $coupon_detail->amount = $request->has('amount') ?  $request->amount : '';

                } else {

                    throw new Exception(tr('admin_coupon_amount_lessthan_100'), 101);
                }

            } else {

                // This else condition is absoulte amount 

                // Amount type one must should be amount less than or equal 5000 only
                if( $request->amount <= 5000 ) {

                    $coupon_detail->amount_type=$request->has('amount_type') ? $request->amount_type : DEFAULT_TRUE;

                    $coupon_detail->amount=$request->has('amount') ?  $request->amount : '';

                } else {

                    throw new Exception(tr('admin_coupon_amount_lessthan_5000'), 101);
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
            
            if( $coupon_detail ) {

                if( $coupon_detail->save() ) {

                    DB::commit();

                    return redirect()->route('admin.coupons.view',['coupon_id' => $coupon_detail->id])->with('flash_success',$message);
                } 

                throw new Exception(tr('admin_coupon_save_error'), 101);
            } 

            throw new Exception(tr('admin_coupon_not_found'), 101);             
                                  
        } catch(Exception $e) {

                DB::rollback();

                $error = $e->getMessage();

                return redirect()->back()->withInput()->with('flash_error', $error);
        }
        
    }


    /**
     * Function Name: coupons_delete()
     *
     * @uses Delete the particular coupon detail
     *
     * @created Maheswari
     *
     * @updated Anjana H
     *
     * @param integer $id
     *
     * @return Deleted Success message
     */
    public function coupons_delete(Request $request) {

        try {
            
            DB::beginTransaction();

            $coupon_details = Coupon::find($request->coupon_id);

            if( count($coupon_details) == 0 ) {

                throw new Exception(tr('admin_coupon_not_found'), 101);
            } 

            if( $coupon_details->delete()) {

                DB::commit();

                return redirect()->route('admin.coupons.index')->with('flash_success',tr('coupon_delete_success'));
            } 

            throw new Exception(tr('admin_coupon_delete_error'), 101);
           
        } catch( Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
        
    }

    /**
     * Function Name: coupons_view()
     *
     * @uses To display coupon details based on coupon_id
     *
     * @created Maheswari
     *
     * @updated Anjana H
     *
     * @param Integer (request() $coupon_id
     *
     * @return view page
     */
    public function coupons_view(Request $request) {

        try {

            $coupon_details = Coupon::find($request->coupon_id);

            if( count($coupon_details) == 0 ){

                throw new Exception(tr('admin_coupon_not_found'), 101);
            }

            $user_coupon = "0";

            return view('admin.coupons.view')
                    ->with('page','coupons')
                    ->with('sub_page','coupons-view')
                    ->with('coupon_details',$coupon_details)
                    ->with('user_coupon', $user_coupon);
        
        } catch(Exception $e) {

            $error = $e->getMessage();

            return redirect()->back()->with('flash_error', $error);
        }  
    }

    /**
     * Function Name: coupons_status_change()
     * 
     * @uses Coupon status for active and inactive update the status function
     *
     * @created Maheswari
     *
     * @updated Anjana H
     *
     * @param integer $coupon_id
     *
     * @return Success message for active/inactive
     */
    public function coupons_status_change(Request $request) {

        try {
            
            DB::beginTransaction();

            $coupon_details = Coupon::find($request->coupon_id);

            if( count($coupon_details) == 0 ) {

                throw new Exception(tr('admin_coupon_not_found'), 101);    
            } 

            $coupon_details->status = $coupon_details->status == APPROVED ? DECLINED : APPROVED;

            if( $coupon_details->save() ) { 

                DB::commit();

                $message = $coupon_details->status == APPROVED ? tr('admin_coupon_approved_success') : tr('admin_coupon_declined_success');

                return back()->with('flash_success',$message);
            } 

            throw new Exception(tr('admin_coupon_status_save_error'), 101);
            
        } catch(Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return redirect()->back()->with('flash_error',$error);
        } 
        
    }

    /**
     * Function: subscriptions_index()
     * 
     * @uses To list the subscriptions
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return view page
     */
    public function subscriptions_index() {

        $subscriptions = Subscription::orderBy('created_at','desc')->whereNotIn('status', [DELETE_STATUS])->paginate(10);

        return view('admin.subscriptions.index')
                    ->with('page','subscriptions')
                    ->with('sub_page','subscriptions-view')
                    ->with('subscriptions',$subscriptions);
    }

    /**
     * Function Name : subscriptions_create()
     *
     * @uses To create subscription object 
     *
     * @created Anjana H 
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return View page
     */
    public function subscriptions_create() {

        $subscription_details = new Subscription;

        return view('admin.subscriptions.create')
                    ->with('page','subscriptions')
                    ->with('sub_page','subscriptions-create')
                    ->with('subscription_details', $subscription_details);
    }

    /**
     * Function Name : subscriptions_edit()
     *
     * @uses To display and update subscription object details based on the subscription id
     *
     * @created  Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $subscription_id
     *
     * @return View page
     */
    public function subscriptions_edit(Request $request) {

        try {
          
            $subscription_details = Subscription::find($request->subscription_id);

            if( count($subscription_details) == 0 ) {

                throw new Exception( tr('admin_subscription_not_found'), 101);
            }

            return view('admin.subscriptions.edit')
                    ->with('page' , 'subscriptions')
                    ->with('sub_page','subscriptions-view')
                    ->with('subscription_details', $subscription_details);           

        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.subscriptions.index')->with('flash_error',$error);
        }

    }
    
    /**
     * Function Name : subscriptions_save()
     *
     * @uses To save the subscription object details of new/existing based on details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $subscription_id , (request) subscription details
     *
     * @return success/error message
     */
    public function subscriptions_save(Request $request) {

        try {
            
            DB::beginTransaction();

            $validator = Validator::make($request->all(),[
                'title' => $request->subscription_id ? 'required|max:255|unique:subscriptions,title,'.$request->subscription_id : 'required|max:255|unique:subscriptions,title',
                'plan' => 'required|numeric|min:1|max:12',
                'amount' => 'required|numeric',
                'no_of_account'=>'required|numeric|min:1',
            ]);
            
            if( $validator->fails() ) {

                $error = implode(',', $validator->messages()->all() );

                throw new Exception($error, 101);
            } 

            if( $request->popular_status == TRUE ) {

                Subscription::where('popular_status' , TRUE )->update(['popular_status' => FALSE]);
            }

            if( $request->subscription_id != '' ) {

                $subscription_details = Subscription::find($request->subscription_id);

                $subscription_details->update($request->all());

            } else {

                $subscription_details = Subscription::create($request->all());

                $subscription_details->status = APPROVED ;

                $subscription_details->popular_status = $request->popular_status == APPROVED ? APPROVED  : DECLINED ;

                $subscription_details->unique_id = $subscription_details->title;

                $subscription_details->no_of_account = $request->no_of_account;
            } 

            if( $subscription_details->save() ) { 

                DB::commit();

                $message = $request->subscription_id ? tr('admin_subscription_update_success') : tr('admin_subscription_create_success');
                
                return redirect()->route('admin.subscriptions.view', ['subscription_id' => $subscription_details->id] )->with('flash_success', $message);

            } 
            
            throw new Exception(tr('admin_subscription_save_error'), 101);
            
            
        } catch (Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.subscriptions.index')->with('flash_error',$error);
        }    
        
    }

    /**
     * Function: subscriptions_view()
     * 
     * @uses To display subscription details based on subscription id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $subscriptions_id
     *
     * @return view page
     */
    public function subscriptions_view(Request $request) {

        try {

            $subscription_details = Subscription::find($request->subscription_id);
            
            if( count($subscription_details) == 0 ) {

                throw new Exception(tr('admin_subscription_not_found'), 101);                
            } 

            $earnings = $subscription_details->userSubscription()->where('status' , APPROVED)->sum('amount');

            $total_subscribers = $subscription_details->userSubscription()->where('status' , APPROVED)->count();

            return view('admin.subscriptions.view')
                        ->with('page' ,'subscriptions')
                        ->with('sub_page' ,'subscriptions-view')
                        ->with('subscription_details' , $subscription_details)
                        ->with('total_subscribers', $total_subscribers)
                        ->with('earnings', $earnings);
           

        } catch (Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.subscriptions.index')->with('flash_error',$error);

        }

    }

    /**
     * Function: subscriptions_delete()
     * 
     * @uses To delete the subscription object based on subscription id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function subscriptions_delete(Request $request) {

        try {

            DB::beginTransaction();

            $subscription_details = Subscription::find($request->subscription_id);

            $subscription_details->status = DELETE_STATUS;

            if( $subscription_details->save() ) {

                DB::commit();
                
                return redirect()->route('admin.subscriptions.index')->with('flash_success', tr('admin_subscription_delete_success'));

            } 
                
            throw new Exception(tr('admin_subscription_delete_error'), 101);
        
        } catch( Exception $e) {
            
            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.subscriptions.index')->with('flash_error',$error);
        }
    }


    /**
     * Function: subscriptions_popular_status()
     * 
     * @uses To update subscription's popular_status to APPROVED/DECLINED based on subscription id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function subscriptions_popular_status(Request $request) {

        try {
            
            DB::beginTransaction();

            if($request->has('subscription_id')) {  
            
                $subscription_details = Subscription::where('id', $request->subscription_id)->first();
                
                if( count($subscription_details) == 0 ) {

                    throw new Exception(tr('admin_subscription_not_found'), 101);
                }

                $subscription_details->popular_status  = $subscription_details->popular_status == APPROVED ? DECLINED : APPROVED ;

                $message = $subscription_details->popular_status ? tr('admin_subscription_popular_success') : tr('admin_subscription_remove_popular_success'); 
                
                if( $subscription_details->save() ) { 

                    DB::commit();

                    return back()->with('flash_success' , $message);                
                } 

                throw new Exception(tr('admin_subscription_populor_status_error'), 101);
            } 
            
            throw new Exception( tr('try_again'), 101);
            
        } catch (Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.subscriptions.index')->with('flash_error',$error);
        }
    }

    /**
     * Function: subscriptions_users()
     * 
     * @uses To update subscription's popular_status to APPROVED/DECLINED based on subscription id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/failure message
     */
    public function subscriptions_users(Request $request) {

        try {

            if($request->has('subscription_id')) {     

                $user_ids = [];

                $user_payments = UserPayment::where('subscription_id' , $request->subscription_id)->select('user_id')->get();

                foreach ($user_payments as $key => $value) {

                    $user_ids[] = $value->user_id;
                }

                $subscription_details = Subscription::find($request->subscription_id);

                $users = User::whereIn('id' , $user_ids)->orderBy('created_at','desc')->paginate(10);
                
                return view('admin.users.index')
                            ->withPage('users')
                            ->with('sub_page','users-view')
                            ->with('user_payments' , $user_payments)
                            ->with('users' , $users)
                            ->with('subscription_details' , $subscription_details);
            }  

            throw new Exception(tr('admin_subscription_not_found'), 101);
            
        } catch (Exception $e) {

            $error = $e->getMessage();

            return redirect()->route('admin.subscriptions.index')->with('flash_error',$error);
        }
    }

    /**
     * Function Name : subscriptions_status_change()
     *
     * @uses To update subscription status to approve/decline based on subscription id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $subscription_id
     *
     * @return success/error message
     */
    public function subscriptions_status_change(Request $request) {

        try {

            DB::beginTransaction();
       
            $subscription_details = Subscription::find($request->subscription_id);

            if( count( $subscription_details) == 0) {
                
                throw new Exception(tr('admin_subscription_not_found'), 101);
            } 
            
            $subscription_details->status = $subscription_details->status == APPROVED ? DECLINED : APPROVED;

            $message = $subscription_details->status == APPROVED ? tr('admin_subscription_approved_success') : tr('admin_subscription_declined_success');

            if( $subscription_details->save() ) {

                DB::commit();

                return back()->with('flash_success',$message);
            } 
            
            throw new Exception(tr('admin_subscription_status_save_error'), 101);
           
        } catch( Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.subscriptions.index')->with('flash_error',$error);
        }

    }    

    /**
     * Function Name : users_subscriptions()
     *
     * @uses To display subscriptions based on user id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $user_id
     *
     * @return success/error message
     */
    public function users_subscriptions(Request $request) {

        try {

            $subscriptions = Subscription::orderBy('created_at','desc')
                            ->whereNotIn('status', [DELETE_STATUS])->get();

            $payments = UserPayment::orderBy('created_at' , 'desc')
                        ->where('user_id' , $request->user_id)->get();

            return view('admin.subscriptions.user_plans')
                        ->withPage('users')   
                        ->with('sub_page','users-view')
                        ->with('subscriptions' , $subscriptions)
                        ->with('user_id', $request->user_id)
                        ->with('payments', $payments); 
            
        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->back()->with('flash_error',$error);
        }            
    }

    /**
     * Function Name : users_subscription_save()
     *
     * @uses To save user subscription based on subscription and user id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $subscription_id, $user_id
     *
     * @return success/error message
     */
    public function users_subscriptions_save(Request $request) {

        try {

            DB::beginTransaction();

            $user_payment_details = UserPayment::where('user_id' , $request->user_id)->where('status', DEFAULT_TRUE)->orderBy('id', 'desc')->first();

            $uses_payment = new UserPayment();

            $uses_payment->subscription_id = $request->subscription_id;

            $uses_payment->user_id = $request->user_id;

            $uses_payment->subscription_amount = ($uses_payment->subscription) ? $uses_payment->subscription->amount  : 0;

            $uses_payment->amount = ($uses_payment->subscription) ? $uses_payment->subscription->amount  : 0;

            $uses_payment->payment_id = ($uses_payment->amount > 0) ? uniqid(str_replace(' ', '-', 'PAY')) : 'Free Plan'; 

            if ($user_payment_details) {

                if (strtotime($user_payment_details->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

                 $uses_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$uses_payment->subscription->plan} months", strtotime($user_payment_details->expiry_date)));

                } else {

                    $uses_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$uses_payment->subscription->plan} months"));
                }

            } else {

                $uses_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$uses_payment->subscription->plan} months"));
            }

            $uses_payment->status = DEFAULT_TRUE;

            if( $uses_payment->save() )  {

                $uses_payment->user->user_type = DEFAULT_TRUE;

                if( $uses_payment->user->save() ) {

                    DB::commit();

                    return back()->with('flash_success', tr('admin_subscription_applied_success'));                
                }

                throw new Exception(tr('admin_user_subascription_save_error'), 101);
            } 

            throw new Exception(tr('admin_user_subascription_save_error'), 101);
            
        } catch (Exception $e) {
            
            DB::rollback();
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }

    }

    /**
     * Function Name : users_auto_subscription_enable()
     *
     * To prevent automatic subscriptioon, user have option to cancel subscription
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param (request) - User details & payment details
     *
     * @return success/failure message
     */
    public function users_auto_subscription_enable(Request $request) {
        
        try {

            $user_payment = UserPayment::where('user_id', $request->user_id)->where('status', PAID_STATUS)->orderBy('created_at', 'desc')
                ->where('is_cancelled', AUTORENEWAL_CANCELLED)
                ->first();

            if( count($user_payment) == 0)  {

                throw new Exception(tr('user_payment_details_not_found'), 101);
            }  

            $user_payment->is_cancelled = AUTORENEWAL_ENABLED;

            $user_payment->save();

            return back()->with('flash_success', tr('autorenewal_enable_success'));
        
        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }     

    }  

    /**
     * Function Name : users_auto_subscription_disable()
     *
     * @uses To prevent automatic subscriptioon of user,user has option to cancel subscription
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param $request - User details & payment details
     *
     * @return success/failure message
     */
    public function users_auto_subscription_disable(Request $request) {

        try {
            
            DB::beginTransaction();

            $user_payment = UserPayment::where('user_id', $request->user_id)->where('status', PAID_STATUS)->orderBy('created_at', 'desc')->first();

            if( count($user_payment) == 0 ) {

                throw new Exception(tr('admin_user_payment_details_not_found'), 101);
            } 

            $user_payment->is_cancelled = AUTORENEWAL_CANCELLED;

            $user_payment->cancel_reason = $request->cancel_reason;

            if ($user_payment->save()) {
               
                DB::commit();

                return back()->with('flash_success', tr('admin_cancel_subscription_success'));            
            }

            throw new Exception(tr('admin_subscription_save_error'), 101);                            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }      

    }

    /**
     * Function Name : revenue_dashboard()
     *
     * @uses To display revenue details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return success/error message
     */
    public function revenue_dashboard() {

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

        return view('admin.payments.revenue_dashboard')
                    ->with('page', 'payments')
                    ->with('sub_page', 'revenue_system')
                    ->with('total_revenue',$total_revenue)
                    ->with('video_amount', $video_amount)
                    ->with('user_amount', $user_amount)
                    ->with('admin_amount', $admin_amount ? $admin_amount : 0);
    }

    /**
     * Function: profile()
     * 
     * @uses admin profile details 
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param - 
     *
     * @return view page
     */
    public function profile() {

        $id = Auth::guard('admin')->user()->id;

        $admin_details = Admin::find($id);

        return view('admin.accounts.profile')
                    ->withPage('profile')
                    ->with('sub_page','')
                    ->with('admin_details' , $admin_details);
    }

    /**
     * Function: profile_save()
     * 
     * @uses save admin updated profile details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param - 
     *
     * @return view page
     */
    public function profile_save(Request $request) {

        try {
            
            DB::beginTransaction();

            $validator = Validator::make( $request->all(), [
                    'name' => 'regex:/^[a-zA-Z]*$/|max:100',
                    'email' => $request->id ? 'email|max:255|unique:admins,email,'.$request->id : 'required|email|max:255|unique:admins,email,NULL',
                    'mobile' => 'digits_between:4,16',
                    'address' => 'max:300',
                    'id' => 'required|exists:admins,id',
                    'picture' => 'mimes:jpeg,jpg,png'
                ]
            );
            
            if( $validator->fails() ) {
             
                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);                
            } 
                
            $admin_details = Admin::find($request->id);
            
            $admin_details->name = $request->has('name') ? $request->name : $admin_details->name;

            $admin_details->email = $request->has('email') ? $request->email : $admin_details->email;

            $admin_details->mobile = $request->has('mobile') ? $request->mobile : $admin_details->mobile;

            $admin_details->gender = $request->has('gender') ? $request->gender : $admin_details->gender;

            $admin_details->address = $request->has('address') ? $request->address : $admin_details->address;

            if($request->hasFile('picture')) {
                
                Helper::delete_picture($admin_details->picture, "/uploads/admins");

                $admin_details->picture = Helper::normal_upload_picture($request->picture);
            }
                
            $admin_details->remember_token = Helper::generate_token();
            
            $admin_details->is_activated = APPROVED;
            
            if( $admin_details->save() ) {

                DB::commit();

                return back()->with('flash_success', tr('admin_profile_update_success'));
            } 

            throw new Exception(tr('admin_profile_save_error'), 101);
                        
        } catch (Exception $e) {
               
            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.profile')->with('flash_error',$error);
        }
    
    }

    /**
     * Function: change_password()
     * 
     * @uses change the admin password 
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param - 
     *
     * @return redirect with success/ error message
     */
    public function change_password(Request $request) {
        
        try {
            
            DB::beginTransaction();

            $old_password = $request->old_password;
            
            $new_password = $request->password;
            
            $confirm_password = $request->confirm_password;
            
            $validator = Validator::make($request->all(), [              
                    'password' => 'required|confirmed|min:6',
                    'old_password' => 'required',
                    'password_confirmation' => 'required|min:6',
                    'id' => 'required|exists:admins,id'
                ]);

            if( $validator->fails() ) {

                $error = implode(',',$validator->messages()->all());

                throw new Exception($error, 101);
            } 

            $admin_details = Admin::find($request->id);

            if( Hash::check($old_password,$admin_details->password) ) {
                
                $admin_details->password = Hash::make( $new_password );
               
                if( $admin_details->save() ) {

                    DB::commit();

                    return back()->with('flash_success', tr('admin_password_change_success'));                
                }                 
                
                throw new Exception(tr('admin_password_save_error'), 101);
                               
            } else {

                throw new Exception(tr('admin_password_mismatch'), 101);
            }

            $response = response()->json($response_array,$response_code);

            return $response;
            
        } catch (Exception $e) {  
            
            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.profile')->with('flash_error',$error);
        }
    
    }

    /**
     * Function: settings()
     * 
     * @uses To display settings details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param - 
     *
     * @return success/error message
     */   
    public function settings() {

        $settings = array();

        $result = EnvEditorHelper::getEnvValues();

        $languages = Language::where('status', DEFAULT_TRUE)->get();

        return view('admin.settings.settings')
                ->withPage('settings')
                ->with('sub_page','site_settings')
                ->with('settings' , $settings)
                ->with('result', $result)
                ->with('languages' , $languages); 
    
    }

    /**
     * Function: settings_save()
     * 
     * @uses to update settings details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return success/error message
     */
    public function settings_save(Request $request) {

        try {

            DB::beginTransaction();

            foreach( $request->toArray() as $key => $value) {
              
                $check_settings = Settings::where('key' ,'=', $key)->count();

                if ( $check_settings == 0 ) {

                    throw new Exception( $key.tr('admin_settings_key_not_found'), 101);
                }

                if( $request->hasFile($key) ) {

                    Helper::delete_picture($key, "/uploads/settings/");

                    $file_path = Helper::normal_upload_picture($request->file($key), "/uploads/settings/");

                    $result = Settings::where('key' ,'=', $key)->update(['value' => $file_path]); 
               
                } else {

                    if ($key == "site_name") {

                        $site_name = preg_replace("/[^A-Za-z0-9]/", "", $value);

                        \Enveditor::set("SITENAME", $site_name);

                    }
                    
                    $result = Settings::where('key' ,'=', $key)->update(['value' => $value]); 

                    if( $result == TRUE ) {
                     
                        DB::commit();
                   
                    } else {

                        throw new Exception(tr('admin_settings_save_error'), 101);
                    }   
                }  
            }

            return back()->with('flash_success', tr('admin_settings_key_save_success') );
            
        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }

    }

    /**
     * Function: common_settings_save()
     * 
     * @uses to update settings details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return success/error message
     */
    public function common_settings_save(Request $request) {

        try {

            $settings = array();

            $admin_id = \Auth::guard('admin')->user()->id;

            foreach( $request->all() as $key => $data ) {

                if( \Enveditor::set($key, $data)) { 

                    // do nothing on success update
                    
                } else {

                    $result = Settings::where('key' ,'=', $key)->update(['value' => $data]); 

                    if( $result == TRUE ) {
                     
                        DB::commit();
                   
                    } else {

                        throw new Exception(tr('admin_settings_save_error'), 101);
                    }     
                }
            }

            return redirect()->route('clear-cache')->with('setting', $settings);
            
        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }

    }

    /**
     * Function: video_settings_save()
     * 
     * @uses to update settings details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return success/error message
     */
    public function video_settings_save(Request $request) {

        try {
            
            DB::beginTransaction();

            $settings = Settings::all();

            foreach( $request->all() as $key => $data) {

                \Enveditor::set($key, $data);
            }

            foreach( $request->toArray() as $key => $value) {
              
                $check_settings = Settings::where('key' ,'=', $key)->count();

                if( $check_settings == 0 ) {

                    throw new Exception( $key.tr('admin_settings_key_not_found'), 101);
                }

                if( $request->hasFile($key) ) {

                    Helper::delete_picture($key, "/uploads/settings/");

                    $file_path = Helper::normal_upload_picture($request->file($key), "/uploads/settings/");

                    $result = Settings::where('key' ,'=', $key)->update(['value' => $file_path]); 
               
                } else {

                    $result = Settings::where('key' ,'=', $key)->update(['value' => $value]); 

                    if( $result == TRUE ) {
                     
                        DB::commit();
                   
                    } else {

                        throw new Exception(tr('admin_settings_save_error'), 101);
                    }   
                }  
            }

            return back()->with('flash_success', tr('admin_settings_key_save_success'));
            
        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }

    }

    /**
     * Functiont Name: home_page_settings()
     * 
     * @uses to display/update the user home page content settings
     * 
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return view page
     */
    public function home_page_settings() {

        return view('admin.settings.home_page')
                    ->with('page','settings')
                    ->with('sub_page','home_page_settings');

    }

    public function payment_settings() {

        $settings = array();

        return view('admin.payment-settings')
                    ->withPage('payment-settings')
                    ->with('sub_page','')
                    ->with('settings' , $settings); 
    }

    /**
     * Functiont Name: custom_push()
     * 
     * @uses to display/update Custom Push notification
     * 
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return view page
     */
    public function custom_push() {

        return view('admin.static_pages.push')
                    ->with('page' , "custom-push")
                    ->with('title' , "Custom Push");

    }

    /**
     * Functiont Name: custom_push_save()
     * 
     * @uses to save Custom Push notification
     * 
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param $request details
     *
     * @return success/failure message
     */
    public function custom_push_save(Request $request) {

        try {
            
            $validator = Validator::make(
                $request->all(),[ 'message' => 'required' ]
            );

            if( $validator->fails() ) {

                $error = $validator->messages()->all();

                throw new Exception($error, 101);                
            } 

            $message = $request->message;

            $title = Setting::get('site_name');

            $message = $message;
            
            $id = 'all';

            $android_register_ids = User::where('is_activated' , USER_APPROVED)->where('device_token' , '!=' , "")->where('device_type' , DEVICE_ANDROID)->where('push_status' , ON)->pluck('device_token')->toArray();

            PushRepo::push_notification_android($android_register_ids , $title , $message);

            $ios_register_ids = User::where('is_activated' , USER_APPROVED)->where('device_type' , 'DEVICE_IOS')->where('push_status' , ON)->select('device_token' , 'id as user_id')->get();

            PushRepo::push_notification_ios($ios_register_ids , $title , $message);

            return back()->with('flash_success' , tr('admin_push_notification_success'));
       
        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    }
    
    /**
     * Functiont Name: help()
     * 
     * @uses: to display help details
     * 
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return view page
     */
    public function help() {

        return view('admin.static_pages.help')
                    ->withPage('help')
                    ->with('sub_page' , "");
    }

    /**
     * Function: user_payments()
     * 
     * @uses used to list the user_payments
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return view page
     */
    public function user_payments() {

        $payments = UserPayment::orderBy('created_at' , 'desc')->paginate(10);

        $payment_count = UserPayment::count();

        return view('admin.payments.user_payments')
                    ->with('page','payments')
                    ->with('sub_page','user-payments')
                    ->with('payments' , $payments)
                    ->with('payment_count', $payment_count); 
    }
    
    /**
     * Function: video_payments()
     * 
     * @uses To list the Pay Per View
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return view page
     */
    public function video_payments() {

        $payments = PayPerView::orderBy('created_at' , 'desc')->paginate(10);

        $payment_count = PayPerView::count();
      
        return view('admin.payments.video-payments')
                    ->withPage('payments')
                    ->with('sub_page','video-subscription')
                    ->with('payment_count',$payment_count)
                    ->with('payments' , $payments); 
    }


    /**
     * Function: mailcamp_create()
     * 
     * @uses To display mail camp form  
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param
     *
     * @return view page
     */
    public function mailcamp_create() {

        $users = User::select('users.id','users.name','users.email',
                                'users.is_activated','users.is_verified',
                                'users.amount_paid')
                            ->where('is_activated',TRUE)
                            ->where('email_notification', TRUE)
                            ->where('is_verified',TRUE)
                            ->get();

        $moderators = Moderator::select('moderators.id','moderators.name',
                                    'moderators.email','moderators.is_activated')
                            ->where('is_activated',TRUE)
                            ->get();

         return view('admin.mail_camp')
                    ->with('users',$users)
                    ->with('moderators',$moderators)
                    ->with('page','mail_camp');
    }
    
    /**
     * Function Name : email_send_process()
     *
     * @uses To send emails(email camp) to chosen role(USERS,MODERATORS,CUSTOM_USERS)
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param $request details
     *
     * @return success/failure message
     */
    public function email_send_process(Request $request) {  

        try {
                   
            $validator = Validator::make($request->all(),[
                'to'=>'required|in:'.USERS.','.MODERATORS.','.CUSTOM_USERS,
                'users_type'=>'in:'.ALL_USER.','.NORMAL_USERS.','.PAID_USERS.','.SELECT_USERS.','.ALL_MODERATOR.','.SELECT_MODERATOR,
                'subject'=>'required|min:5|max:255',
                'content'=>'required|min:5',
            ]);

            if ($validator->fails()) {

                $error = implode(',',$validator->messages()->all());

                throw new Exception($error, 101);                
            }
          
            if ($request->to == USERS ) {

                $base_query = User::select('users.id')->where('is_activated', DEFAULT_TRUE)->where('is_verified', DEFAULT_TRUE);

                switch ($request->users_type) {

                    case ALL_USER:
                        $email_details = $base_query->pluck('users.id')->toArray();
                        break;

                    case NORMAL_USERS:

                        $email_details = $base_query->where('user_type',0)->pluck('users.id')->toArray();

                        break;

                    case PAID_USERS:
                        $email_details = $base_query->where('user_type',1 )->pluck('users.id')->toArray();
                        break; 

                    case SELECT_USERS:
                        $email_details = $request->select_user;
                        break;

                    default:
                        throw new Exception(tr('admin_user_type_not_found'), 101);
                }

            } else if ($request->to == MODERATORS) {

                switch ($request->users_type) {

                    case ALL_MODERATOR:
                        $email_details = Moderator::select('moderators.id')->where('is_activated', DEFAULT_TRUE)->pluck('moderators.id')->toArray();
                        break;

                    case SELECT_MODERATOR:
                        $email_details = $request->select_moderator;
                        break;

                    default:
                        throw new Exception(tr('admin_moderator_not_found'), 101);
                }

            } else if ($request->to == CUSTOM_USERS) {

                $custom_user = $request->custom_user;
                
                if ($custom_user != '') {

                    $email_details = explode(',', $custom_user);
                    
                    if (Setting::get('custom_users_count') >= count($email_details)) {

                        foreach ($email_details as $key => $value) {   

                            Log::info('Custom Mail list : '.$value);

                            if (!filter_var($value,FILTER_VALIDATE_EMAIL)) {

                                //This variable is only for email validate messsage purpose only 
                                $validate_email = DEFAULT_FALSE;

                                $invalid_email[] = $value;

                                $message = tr('custom_email_invalid');

                                $error = implode(' , ' , $invalid_email);

                                throw new Exception($error, 101);                                

                            } else {

                                //This variable is only for email validate messsage purpose only  using
                                $validate_email = DEFAULT_TRUE;

                                $subject = $request->subject;
                                    
                                $content = $request->content;
                               
                                $page = "emails.send_mail";

                                $email = $value;

                                // Get the custom user name before @ symbol
                                $name =  substr($email, 0, strrpos($email, "@"));
                                
                                $email_data['name'] = $name;

                                $email_data['content'] = $content;

                                $email_data['email'] = $value;

                                Helper::send_email($page,$subject,$email,$email_data);

                                return back()->with('flash_success',tr('mail_send_successfully'));

                            }                            
                        }

                    } else{

                        throw new Exception(tr('custom_user_count'), 101);
                    }

                } else {

                    throw new Exception(tr('custom_user_field_required'), 101);
                }
                    
            } else { 

                throw new Exception(tr('admin_user_not_found'), 101);
            }
       
            if ( count($email_details) > 0 ) {

                $users_moderator_type = $request->to;

                $subject = $request->subject;
                        
                $content = $request->content;

                dispatch(new SendMailCamp($email_details,$subject,$content,$users_moderator_type));

                return back()->with('flash_success',tr('mail_send_successfully'));
            } 

            throw new Exception(tr('details_not_found'), 101);
            
        } catch (Exception $e) {

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    } 

    /**
     * Function Name : templates_index()
     *
     * @uses To display email templates
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param 
     *
     * @return view page
     */
    public function templates_index(Request $request) {

        $templates = EmailTemplate::orderBy('created_at', 'desc')->get();

        return view('admin.email_templates.index')
                ->with('templates', $templates)
                ->with('page', 'email_templates')
                ->with('sub_page', 'email_templates');

    }

    /**
     * Function Name : templates_edit()
     *
     * @uses To display and update email template based on template_id
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $template_id
     *
     * @return view page
     */
    public function templates_edit(Request $request) {

        try {
            
            $template_details = EmailTemplate::find($request->template_id);

            $template_types = [USER_WELCOME => tr('user_welcome_email'), 
                                ADMIN_USER_WELCOME => tr('admin_created_user_welcome_mail'), 
                                FORGOT_PASSWORD => tr('forgot_password'), 
                                MODERATOR_WELCOME=>tr('moderator_welcome'), 
                                PAYMENT_EXPIRED=>tr('payment_expired'), 
                                PAYMENT_GOING_TO_EXPIRY=>tr('payment_going_to_expiry'), 
                                NEW_VIDEO=>tr('new_video'), 
                                EDIT_VIDEO=>tr('edit_video')];

            if (count($template_details) == 0) {

                throw new Exception(tr('template_not_found'), 101);
            }

            return view('admin.email_templates.edit')
                        ->with('page', 'email_templates')
                        ->with('sub_page', 'create_template')
                        ->with('template_details', $template_details)
                        ->with('template_types', $template_types);

        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    } 

    /**
     * Function Name : templates_save()
     *
     * @uses To save/update email template based on request details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $template_id, (request) details
     *
     * @return view page
     */
    public function templates_save(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'template_type'=>'required|in:'.USER_WELCOME.','.ADMIN_USER_WELCOME.','.FORGOT_PASSWORD.','.MODERATOR_WELCOME.','. PAYMENT_EXPIRED.','.PAYMENT_GOING_TO_EXPIRY.','.NEW_VIDEO.','.EDIT_VIDEO,
                'subject'=>'required|max:255',
                'description'=>'required',
            ]);

            $template = $request->template_id ? EmailTemplate::find($request->template_id) : new EmailTemplate;

            if($template) {

                $template->subject = $request->subject;
                    
                $template->description = $request->description;

                $template->template_type = $request->template_type;

                $template->status = DEFAULT_TRUE;

                if ($template->save()) {

                    DB::commit();

                    $message = $request->template_id ? tr('admin_template_update_success') : tr('admin_template_create_success'); 

                    return redirect()->route('admin.templates.index')->with('flash_success', $message);

                } else {

                    throw new Exception(tr('admin_template_save_error'), 101);
                }

            } 

            throw new Exception(tr('admin_template_not_found'), 101);
           
        } catch(Exception $e) {

            DB::rollback();

            $message = $e->getMessage();

            return back()->with('flash_error', $message);
        }
    }

    /**
     * Function Name : templates_view()
     *
     * @uses To disaply email template based on request details
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param Integer (request) $template_id
     *
     * @return view page
     */
    public function templates_view(Request $request) {

        try {

            $template_details = EmailTemplate::find($request->template_id);

            if( count($template_details) == 0) {

                throw new Exception(tr('admin_template_not_found'), 101);
            }   
            
            return view('admin.email_templates.view')
                        ->with('page', 'email_templates')
                        ->with('sub_page', 'templates')
                        ->with('template_details', $template_details);
            
        } catch (Exception $e) {

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }

    } 


    /**
     * Function Name : sub_admins_index()
     *
     * @uses To list out subadmins (only admin can access this option)
     * 
     * @created Anjana H
     *
     * @updated Anjana H  
     *
     * @param object $request
     *
     * @return view page
     */
    public function sub_admins_index() {

        $sub_admins = Admin::orderBy('created_at', 'desc')->where('role', SUBADMIN)->get();

        return view('admin.sub_admins.index')
                ->with('page', 'sub-admins')
                ->with('sub_page', 'sub-admins-view')
                ->with('sub_admins', $sub_admins);        
    }

    /**
     * Function Name : sub_admins_create()
     *
     * To create a sub admin only admin can access this option
     * 
     * @created Anjana H
     *
     * @updated Anjana H  
     *
     * @param object $request - -
     *
     * @return response of html page with details
     */
    public function sub_admins_create() {

        $sub_admin_details = new Admin();

        return view('admin.sub_admins.create')
                ->with('page', 'sub-admins')
                ->with('sub_page', 'sub-admins-create')
                ->with('sub_admin_details', $sub_admin_details);
    }

    /**
     * Function Name : sub_admins_edit()
     *
     * @uses To edit a sub admin based on subadmin id only  admin can access this option
     * 
     * @created
     *
     * @updated 
     *
     * @param object $request - sub Admin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_edit(Request $request) {

       try {
          
            $sub_admin_details = Admin::find($request->sub_admin_id);

            if( count($sub_admin_details) == 0 ) {

                throw new Exception( tr('admin_sub_admin_not_found'), 101);

            }

            return view('admin.sub_admins.edit')
                        ->with('page', 'sub-admins')
                        ->with('sub_page', 'sub-admins-view')
                        ->with('sub_admin_details', $sub_admin_details);

        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.sub_admins.index')->with('flash_error',$error);
        }
    }

    /**
     * Function Name : sub_admins_view()
     *
     * @uses To view a sub admin based on sub admin id only admin can access this option
     * 
     * @created Anjana H
     *
     * @updated Anjana H  
     *
     * @param object $request - Sub Admin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_view(Request $request) {

        try {
          
            $sub_admin_details = Admin::find($request->sub_admin_id);

            if( count($sub_admin_details) == 0 ) {

                throw new Exception( tr('admin_sub_admin_not_found'), 101);
            } 

            return view('admin.sub_admins.view')
                    ->with('page', 'sub-admins')
                    ->with('sub_page', 'sub-admins-view')
                    ->with('sub_admin_details', $sub_admin_details);
       
        } catch( Exception $e) {
            
            $error = $e->getMessage();

            return redirect()->route('admin.sub_admins.index')->with('flash_error',$error);
        }
    }


    /**
     * Function Name : sub_admins_delete()
     *
     * @uses To delete a sub admin based on sub admin id. only admin can access this option
     * 
     * @created Anjana H
     *
     * @updated Anjana H  
     *
     * @param object $request - Sub Admin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_delete(Request $request) {

         try {

            DB::beginTransaction();
            
            $sub_admin_details = Admin::where('id' , $request->sub_admin_id)->first();

            if(count($sub_admin_details) == 0 ) {  

                throw new Exception(tr('admin_sub_admin_not_found'), 101);
            }
            
            if( $sub_admin_details->delete() ) {

                DB::commit();

                return redirect()->route('admin.sub_admins.index')->with('flash_success',tr('admin_sub_admin_delete_success'));
            }

            throw new Exception(tr('admin_sub_admin_delete_error'), 101);
            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }
    }

    /**
     * Function Name : sub_admins_save()
     *
     * @uses To save the sub admin details
     * 
     * @created Anjana H
     *
     * @updated Anjana H  
     *
     * @param object $request - Sub Admin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_save(Request $request) {

        try {
            
            DB::beginTransaction();

            $validator = Validator::make( $request->all(),array(
                    'name' => 'required|max:100',
                    'email' => $request->sub_admin_id ? 'email|max:255|unique:admins,email,'.$request->sub_admin_id : 'required|email|max:255|unique:admins,email,NULL',
                    'mobile' => 'digits_between:4,16',
                    'address' => 'max:300',
                    'sub_admin_id' => 'exists:admins,id',
                    'picture' => 'mimes:jpeg,jpg,png',
                    'description'=>'max:255',
                    'password' => $request->sub_admin_id ? '' : 'required|min:6|confirmed',
                )
            );
            
            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);
            } 

            $sub_admin_details = $request->sub_admin_id ? Admin::find($request->sub_admin_id) : new Admin;

            if (!$sub_admin_details) {

                throw new Exception(tr('sub_admin_not_found'), 101);
            }

            $sub_admin_details->name = $request->name ?: $sub_admin_details->name;

            $sub_admin_details->email = $request->email ?: $sub_admin_details->email;

            $sub_admin_details->mobile = $request->mobile ?: $sub_admin_details->mobile;

            $sub_admin_details->description = $request->description ?: '';

            $sub_admin_details->role = SUBADMIN;

            if(!$request->sub_admin_id) {
                
                $sub_admin_details->picture = asset('placeholder.png');

            }

            if($request->hasFile('picture')) {

                if($request->sub_admin_id) {

                    Helper::delete_picture($sub_admin_details->picture, "/uploads/sub_admins/");
                }

                $sub_admin_details->picture = Helper::normal_upload_picture($request->picture, "/uploads/sub_admins/");
            }
                
            if (!$sub_admin_details->id) {

                $new_password = $request->password;
                
                $sub_admin_details->password = Hash::make($new_password);
            }

            $sub_admin_details->timezone = $request->timezone;

            $sub_admin_details->token = Helper::generate_token();

            $sub_admin_details->token_expiry = Helper::generate_token_expiry();

            $sub_admin_details->is_activated = DEFAULT_TRUE;

            if($sub_admin_details->save()) {

                DB::commit();

                $message = $request->sub_admin_id ? tr('admin_sub_admin_update_success') : tr('admin_sub_admin_create_success');
                
                return redirect()->route('admin.sub_admins.view', ['sub_admin_id' =>$sub_admin_details->id ])->with('flash_success', $message);

            } 

            throw new Exception(tr('admin_sub_admin_save_error'), 101);
           
        } catch (Exception $e) {
            
            DB::rollback();
            
            $error = $e->getMessage();

            return back()->withInput()->with('flash_error',$error);
        }
    
    }

    /**
     * Function Name : sub_admins_status()
     *
     * @uses To change the status of the sub admin, based on sub admin id. only admin can access this option
     * 
     * @created Anjana H
     *
     * @updated Anjana H  
     *
     * @param object $request - SubAdmin Id
     *
     * @return response of html page with details
     */
    public function sub_admins_status(Request $request) {

        try {

            DB::beginTransaction();
       
            $sub_admin_details = Admin::find($request->sub_admin_id);

            if( count( $sub_admin_details) == 0) {
                
                throw new Exception(tr('admin_sub_admin_not_found'), 101);
            } 
            
            $sub_admin_details->is_activated = $sub_admin_details->is_activated == APPROVED ? DECLINED : APPROVED;

            $message = $sub_admin_details->is_activated == APPROVED ? tr('admin_sub_admin_approve_success') : tr('admin_sub_admin_decline_success');

            if( $sub_admin_details->save() ) {

                DB::commit();

                return back()->with('flash_success', $message);
            } 

            throw new Exception(tr('admin_sub_admin_status_error'), 101);
            
        } catch( Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return redirect()->route('admin.sub_admins.index')->with('flash_error',$error);
        }
    }


   /**
     * Function Name : gif_generator()
     *
     * @uses Future, Not now - To create a gif based on 3 images
     *
     * @created Vidhya R
     *
     * @updated Vidhya R
     *
     * @param integer $request - video id
     *
     * @return response of json details
     */
    public function gif_generator(Request $request) {

        try {

            $admin_video_details = AdminVideo::find($request->id);

            if ($admin_video_details) {

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

                $admin_video_details->video_gif_image = Helper::web_url()."/uploads/gifs/video_{$request->video_id}.gif";

                $admin_video_details->save();

                return back()->with('flash_success', tr('gif_generate_success'));
            }

            throw new Exception( tr('gif_generate_failure'), 101);
            
        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error);
        }

    }

    /**
     * Function Name admin_videos_index()
     *
     * @uses Get the videos list 
     *
     * @created Anjana H
     *
     * @updated Anjana H 
     *
     * @param 
     *
     * @return Videos list
     */
    public function admin_videos_index(Request $request) {

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
                             'admin_videos.position',
                             'admin_videos.is_original_video',
                             'admin_videos.is_home_slider',
                             'admin_videos.is_kids_video',
                             'admin_videos.download_status',
                             'admin_videos.video_resolutions',
                             'admin_videos.video_resize_path',
                             'admin_videos.video_type'
                        )
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

        $admin_videos = $query->paginate(10);

        foreach ($admin_videos as $key => $admin_video_details) {
            
            $is_video_eligible_for_download = VideoHelper::check_video_download_eligibility($admin_video_details);

            $admin_video_details->is_video_eligible_for_download = $is_video_eligible_for_download;

        }

        return view('admin.videos.index')
                    ->with('page', 'videos')
                    ->with('sub_page',$sub_page)
                    ->with('category' , $category)
                    ->with('sub_category' , $sub_category)
                    ->with('genre' , $genre)
                    ->with('moderator_details' , $moderator_details)
                    ->with('admin_videos' , $admin_videos);
   
    }

    /**
     * Function Name admin_videos_create()
     *
     * @uses To display a upload video form
     *
     * @created Vidhya R
     *
     * @updated
     *
     * @param
     *
     * @return view page
     */
    public function admin_videos_create(Request $request) {

        $categories = Category::where('categories.is_approved' , APPROVED)
                            ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                                'categories.is_series' ,'categories.status' , 'categories.is_approved')
                            ->leftJoin('sub_categories' , 'categories.id' , '=' , 'sub_categories.category_id')
                            ->groupBy('sub_categories.category_id')
                            ->where("sub_categories.is_approved", SUB_CATEGORY_APPROVED)
                            ->havingRaw("COUNT(sub_categories.id) > 0")
                            ->orderBy('categories.name' , 'asc')
                            ->get();

        $admin_video_details = new AdminVideo;


        $admin_video_details->trailer_video_resolutions = [];

        $admin_video_details->video_resolutions = [];

        $videoimages = $video_cast_crews = [];

        $cast_crews = CastCrew::select('id', 'name')->where('status', APPROVED)->get();

        return view('admin.videos.video-upload')
                ->with('page', 'videos')
                ->with('sub_page', 'admin_videos_create')
                ->with('categories', $categories)
                ->with('videoimages', $videoimages)
                ->with('cast_crews', $cast_crews)
                ->with('video_cast_crews', $video_cast_crews)
                ->with('admin_video_details', $admin_video_details);
    }

    /**
     * Function Name : admin_videos_save()
     *
     * @uses To save a new video as well as updated video details
     *
     * @created Vidhya R
     *
     * @updated 
     *
     * @param 
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
     * @created Vidhya R
     *
     * @updated - 
     *
     * @param object $request - - 
     *
     * @return response of html page with details
     */
    public function admin_videos_edit(Request $request) {



        try {

            $admin_video_details = AdminVideo::where('admin_videos.id' , $request->admin_video_id)->first();

            if (!$admin_video_details) {

                throw new Exception(tr('admin_video_not_found_error'), 101);
            }

            $categories = Category::where('categories.is_approved' , DEFAULT_TRUE)
                                ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                                    'categories.is_series' ,'categories.status' , 'categories.is_approved')
                                ->leftJoin('sub_categories' , 'categories.id' , '=' , 'sub_categories.category_id')
                                ->groupBy('sub_categories.category_id')
                                ->where('sub_categories.is_approved' , SUB_CATEGORY_APPROVED)
                                ->havingRaw("COUNT(sub_categories.id) > 0")
                                ->orderBy('categories.name' , 'asc')
                                ->get();

            $sub_categories = SubCategory::where('category_id', '=', $admin_video_details->category_id)
                                    ->leftJoin('sub_category_images' , 'sub_categories.id' , '=' , 'sub_category_images.sub_category_id')
                                    ->select('sub_category_images.picture' , 'sub_categories.*')
                                    ->where('sub_category_images.position' , 1)
                                    ->where('is_approved' , SUB_CATEGORY_APPROVED)
                                    ->orderBy('name', 'asc')
                                    ->get();

            $admin_video_details->publish_time = $admin_video_details->publish_time ? date('d-m-Y H:i:s', strtotime($admin_video_details->publish_time)) : $admin_video_details->publish_time;

            $videoimages = get_video_image($admin_video_details->id);

            $admin_video_details->video_resolutions = $admin_video_details->video_resolutions ? explode(',', $admin_video_details->video_resolutions) : [];

            $admin_video_details->trailer_video_resolutions = $admin_video_details->trailer_video_resolutions ? explode(',', $admin_video_details->trailer_video_resolutions) : [];

            $video_cast_crews = VideoCastCrew::select('cast_crew_id')
                    ->where('admin_video_id', $request->id)
                    ->get()->pluck('cast_crew_id')->toArray();
           
            $cast_crews = CastCrew::select('id', 'name')->get();
           
            return view('admin.videos.video-upload')->with('page', 'videos')
                        ->with('sub_page', 'admin_videos_create')
                        ->with('categories', $categories)
                        ->with('admin_video_details', $admin_video_details)
                        ->with('sub_categories', $sub_categories)
                        ->with('videoimages', $videoimages)
                        ->with('cast_crews',$cast_crews)
                        ->with('video_cast_crews', $video_cast_crews);

        } catch (Exception $e) {
                       
            $error = $e->getMessage();

            return back()->with('flash_error',$error); 
        }

    }

    /**
     * Function Name : admin_videos_view()
     *
     * @uses get the selected video details
     *
     * @created Vidhya R
     *
     * @updated - 
     *
     * @param integer $admin_video_id
     *
     * @return response of html page with details
     */

    public function admin_videos_view(Request $request) {

        try {

            $validator = Validator::make($request->all() , [
                    'id' => 'required|exists:admin_videos,id'
                ]);

            if($validator->fails()) {
                
                $error = implode(',', $validator->messages()->all());
                
                throw new Exception($error , 101);                
            } 

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
                                 'admin_videos.is_kids_video',
                                 'admin_videos.download_status',
                                 'admin_videos.age',
                                 'admin_videos.status',
                                 'admin_videos.is_original_video',
                                 'admin_videos.is_home_slider'
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
                    if ($videos->is_approved == DEFAULT_TRUE) {
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

            if($videos->is_banner == DEFAULT_TRUE) {

                $sub_page = 'view-banner-videos';
            }

            // Load Video Cast & crews

            $video_cast_crews = VideoCastCrew::select('cast_crew_id', 'name')
                        ->where('admin_video_id', $request->id)
                        ->leftjoin('cast_crews', 'cast_crews.id', '=', 'video_cast_crews.cast_crew_id')
                        ->get()->pluck('name')->toArray();

            return view('admin.videos.view')->with('video' , $videos)
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
                   
        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error',$error); 

        }
    
    }

    /**
     * Function Name : admin_videos_view()
     *
     * @uses get the selected video details
     *
     * @created Vidhya R
     *
     * @updated - 
     *
     * @param integer $admin_video_id
     *
     * @return response of html page with details
     */

    public function admin_videos_delete(Request $request) {

        try {

            DB::beginTransaction();
            
            $admin_video_details = AdminVideo::where('id' , $request->admin_video_id)->first();

            if( count($admin_video_details) == 0) {

                throw new Exception(tr('admin_video_not_found_error'), 101);
            }

            $main_video = $admin_video_details->video;

            $subtitle = $admin_video_details->subtitle;

            $banner_image = $admin_video_details->banner_image;

            $default_image = $admin_video_details->default_image;

            $video_resize_path = $admin_video_details->video_resize_path;

            $trailer_resize_path = $admin_video_details->trailer_resize_path;

            $position = $admin_video_details->position;

            $genre_id = $admin_video_details->genre_id;

            if ($admin_video_details->delete()) {

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

                            if ($value->save()) {


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

                DB::commit();

                return back()->with('flash_success', 'Video deleted successfully');
            }

            throw new Exception(tr('video_delete_failure'), 101);                
            
        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    
    }

    /**
     * Function Name : admin_videos_view()
     *
     * @uses get the selected video details
     *
     * @created Vidhya R
     *
     * @updated - 
     *
     * @param integer $admin_video_id
     *
     * @return response of html page with details
     */

    public function admin_videos_slider_status(Request $request) {

        try {
           
            DB::beginTransaction();

            $admin_video_details = AdminVideo::where('is_home_slider' , DEFAULT_TRUE )->update(['is_home_slider' => DEFAULT_FALSE]); 

            $admin_video_details = AdminVideo::where('id' , $request->admin_video_id)->update(['is_home_slider' => DEFAULT_TRUE ] );

            if($admin_video_details == DEFAULT_TRUE){
                
                DB::commit();

                return back()->with('flash_success', tr('slider_success'));
            
            } else {

                throw new Exception(tr('admin_video_slider_error'), 101);                
            }
            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    
    }

    /**
     * Function Name : admin_videos_status_approve()
     *
     * @uses get the selected video details
     *
     * @created Vidhya R
     *
     * @updated - 
     *
     * @param integer $admin_video_id
     *
     * @return response of html page with details
     */

    public function admin_videos_status_approve(Request $request) {

        try {

            DB::beginTransaction();

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if (!$admin_video_details) {

                throw new Exception(tr('admin_video_not_found_error'), 101);
            }

            $admin_video_details->is_approved = DEFAULT_TRUE;

            if (empty($admin_video_details->publish_time) || $admin_video_details->publish_time == '0000-00-00 00:00:00') {

                $admin_video_details->publish_time = date('Y-m-d H:i:s');
            }

            // Check the video has genre type or not

            if ($admin_video_details->genre_id > 0) {

                /*
                 * Check is there any videos present in same genre, 
                 * if it is, assign the position with increment of 1
                 */
                $get_video_position = AdminVideo::where('genre_id', $admin_video_details->genre_id)
                                ->orderBy('position', 'desc')
                                ->where('is_approved', DEFAULT_TRUE)
                                ->where('status', DEFAULT_TRUE)
                                ->first();

                if($get_video_position) {

                    $admin_video_details->position = $get_video_position->position + 1;
                }
            }

            // Uncommented by vidhya. with below code the response will error

            if($admin_video_details->is_approved == DEFAULT_TRUE) {

                // Notification::save_notification($admin_video_details->id);                
                $message = tr('admin_not_video_approve');
            
            } else {

                $message = tr('admin_not_video_decline');
            } 

            $admin_video_details->save();

            DB::commit();

            return back()->with('flash_success', $message);

        }catch(Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    
    }

    /**
     * Function Name : admin_videos_status_decline()
     *
     * @uses To Publish the video for user
     *
     * @created Vidhya R
     *
     * @updated - 
     *
     * @param integer $admin_video_id
     *
     * @return response of html page with details
     */

    public function admin_videos_status_decline(Request $request) {

        try {
        
            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if (!$admin_video_details) {

                throw new Exception(tr('admin_video_not_found_error'), 101);
            }

            $admin_video_details->is_approved = DEFAULT_FALSE;

            // Check the video has genre type or not
                   
            if ($admin_video_details->genre_id > 0) {

                /*
                 * Check is there any videos present in same genre, 
                 * if it is, assign the position with decrement of 1.(for all videos)
                 */
                $next_videos = AdminVideo::where('genre_id', $admin_video_details->genre_id)
                                ->where('position', '>', $admin_video_details->position)
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

                $admin_video_details->position = 0;
            }

            if($admin_video_details->is_approved == DEFAULT_TRUE) {

                $message = tr('admin_not_video_approve');

            } else {

                $message = tr('admin_not_video_decline');
            }

            DB::commit();

            $admin_video_details->save();

            return back()->with('flash_success', $message);

        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);

        }
    
    }

    /**
     * Function Name : admin_videos_publish()
     *
     * @uses To Publish the video for user
     *
     * @created Vidhya R
     *
     * @updated
     *
     * @param integer $admin_video_id
     *
     * @return response of html page with details
     */

    public function admin_videos_publish(Request $request) {
            
        try {
            
            DB::beginTransaction();

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if(count($admin_video_details) == 0) {

                throw new Exception(tr('admin_video_not_found_error'), 101);               
            }
            
            $admin_video_details->status = VIDEO_PUBLISHED;
            
            $admin_video_details->publish_time = date('Y-m-d H:i:s');

            if ($admin_video_details->save()) {
                
                DB::commit();

                return back()->with('flash_success', tr('admin_published_video_success'));
            } 

            throw new Exception( tr('admin_published_video_failure'), 101);
        
        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    }

    /**
     * Function Name: admin_videos_ppv_add
     *
     * @uses To save the payment details
     *
     * @created Vidhya R
     *
     * @updated
     *
     * @param integer $admin_video_id
     *
     * @param object  $request Object (Post Attributes)
     *
     * @return flash message
     */

    public function admin_videos_ppv_add($id, Request $request) {

        try {

            if ($request->amount > 0) {

                $admin_video_details = AdminVideo::find($id);

                // Get post attribute values and save the values
                if ($admin_video_details) {

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

                throw new Exception(tr('admin_published_video_failure'), 101);
                
            } else {

                throw new Exception(tr('add_ppv_amount'), 101);                
            }

        } catch (Exception $e) {
            
            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    
    }

    /**
     * Function : admin_videos_ppv_remove
     *
     * @uses To remove pay per view
     *
     * @created
     *
     * @updated vidhya R
     *
     * @param integer $admin_video_id
     * 
     * @return falsh success
     */

    public function admin_videos_ppv_remove(Request $request) {
       
        try {
            
            DB::beginTransaction();

            // Load video model using auto increment id of the table
            $admin_video_details = AdminVideo::find($request->admin_video_id);
           
            if ($admin_video_details) {
                
                $admin_video_details->amount = DEFAULT_FALSE;
                
                $admin_video_details->type_of_subscription = DEFAULT_FALSE;
                
                $admin_video_details->type_of_user = DEFAULT_FALSE;
               
                $admin_video_details->is_pay_per_view = PPV_DISABLED;
               
                if ($admin_video_details->save()) {

                    DB::commit();
                    
                    return back()->with('flash_success' , tr('removed_pay_per_view'));

                } else {

                    throw new Exception(tr('admin_video_published_save_error'), 101);
                }
            }

            return back()->with('flash_error' , tr('admin_published_video_failure'));
                   
        } catch (Exception $e) {

            DB::rollback();
            
            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    }

    /**
     * Function : admin_videos_change_position
     *
     * @uses Change position of the video based on genres
     *
     * @created
     *
     * @updated vidhya R
     *
     * @param integer $admin_video_id
     * 
     * @return falsh success
     */

    public function admin_videos_change_position(Request $request) {

        try {

            DB::beginTransaction();

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if ( count($admin_video_details) == 0 ) {

                throw new Exception( tr('video_not_found'));                
            }

            $changing_row_position = $admin_video_details->position;

            $change_video = AdminVideo::where('position', $request->position)
                    ->where('genre_id', $admin_video_details->genre_id)
                    ->where('is_approved', DEFAULT_TRUE)
                    ->where('status', DEFAULT_TRUE)
                    ->first();

            if ( count($change_video) == 0)  {

                throw new Exception( tr('given_position_not_exits'));
            }          

            $new_row_position = $change_video->position;

            $admin_video_details->position = $new_row_position;

            if ($admin_video_details->save()) {

                $change_video->position = $changing_row_position;

                if ($change_video->save()) {

                    DB::commit();

                    return back()->with('flash_success', tr('video_position_updated_success'));

                } else {

                    throw new Exception(tr('video_not_saved'));
                }

            } 

            throw new Exception(tr('video_not_saved'));
           
        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }

    }

    /**
     * Function Name : admin_videos_compression_complete()
     *
     * @uses To complete the compressing videos
     *
     * @param integer video id - Video id
     *
     * @created Vidhya R
     *
     * @updated Vidhya R
     *
     * @return response of success/failure message
     */

    public function admin_videos_compression_complete(Request $request) {
        
        try {
            
            $admin_video_details = AdminVideo::find($request->id);

            if (count($admin_video_details) == 0) {

                throw new Exception( tr('video_not_found'), 101);                
            }

            // Check the video has compress state or not

            if ($admin_video_details->compress_status <= OVERALL_COMPRESS_COMPLETED) {

                $admin_video_details->compress_status = COMPRESSION_NOT_HAPPEN;

                $admin_video_details->trailer_compress_status = COMPRESS_COMPLETED;

                $admin_video_details->main_video_compress_status = COMPRESS_COMPLETED;

                if($admin_video_details->save()) {
                    
                    DB::commit();

                    return back()->with('flash_success', tr('video_compress_success'));

                } else {

                    throw new Exception(tr('video_not_saved'), 101);
                }

            }
                
            throw new Exception(tr('already_video_compressed'), 101);
                       
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    
    }

    /**
     * Function Name : admin_videos_banner_add()
     *
     * @uses Set banner image for video
     *
     * @param object $request - Banner image video details
     *
     * @created Vidhya R
     *
     * @updated Vidhya R
     *
     * @return response of success/failure message details
     */
    public function admin_videos_banner_add(Request $request) {

        try {
            
            DB::beginTransaction();

            $validator = Validator::make( $request->all(), array(
                    'admin_video_id' => 'required|exists:admin_videos,id,is_approved,'.VIDEO_APPROVED.',status,'.VIDEO_PUBLISHED,
                    'banner_image' => 'required|mimes:jpeg,jpg,bmp,png',
                ), [

                    'admin_video_id.exists' => tr('video_not_exists'),

                ]
            );
           
            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);
            } 

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if($request->hasFile('banner_image')) {

                if ($admin_video_details->is_banner == BANNER_VIDEO) {

                    Helper::delete_picture($admin_video_details->banner_image, "/uploads/images/");
                }
                
                $admin_video_details->banner_image = Helper::normal_upload_picture($request->file('banner_image'));
            }

            $admin_video_details->is_banner = BANNER_VIDEO;

            if( $admin_video_details->save()) {
                
                DB::commit();

                return back()->with('flash_success', tr('video_set_banner_success'));
            } 

            throw new Exception(tr('admin_video_save_error'), 101);
            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    }

    /**
     * Function Name : admin_videos_banner_remove()
     *
     * @uses Remove banner image for video
     *
     * @param object $request - Banner image video details
     *
     * @created Vidhya R
     *
     * @updated Vidhya R
     *
     * @return response of success/failure message details
     */
    public function admin_videos_banner_remove(Request $request) {
        
        try {
            
            DB::beginTransaction();

            $validator = Validator::make( $request->all(), array(
                    'admin_video_id' => 'required|exists:admin_videos,id',
                ), [

                    'admin_video_id.exists' => tr('video_not_exists'),

                ]
            );
           
            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception( $error , 101);
            } 

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            Helper::delete_picture($admin_video_details->banner_image, "/uploads/images/");

            $admin_video_details->is_banner = BANNER_VIDEO_REMOVED;

            if( $admin_video_details->save()) {
            
                DB::commit();

                return back()->with('flash_success', tr('video_remove_banner'));            
            } 

            throw new Exception(tr('admin_video_banner_remove'), 101);
            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }

    }

    /**
     * Function Name admin_videos_spams()
     *
     * @uses Load all the videos from flag table
     *
     * @created Maheswari
     *
     * @updated vidhya R
     *
     * @param Get the flag details in groupby video_id
     *
     * @return all the spam videos
     */

    public function admin_videos_spams() {

        // Load all the videos from flag table

        $spam_videos = Flag::groupBy('video_id')->paginate(10);

        return view('admin.spam_videos.spam_videos')
                        ->with('page' , 'videos')
                        ->with('sub_page' , 'spam_videos')
                        ->with('spam_videos' , $spam_videos);
    
    }

    /**
     * Function Name : admin_videos_spams_user_reports()
     *
     * @uses Load the flags based on the video id
     *
     * @created Maheswari
     *
     * @updated vithya R
     *
     * @param integer $admin_video_id
     *
     * @return all the spam videos in user reports
    */
    public function admin_videos_spams_user_reports(Request $request) {

        try {

            if(!$request->admin_video_id) {
               
                throw new Exception(tr('spam_video_id_error'), 101);                
            }

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if(!$admin_video_details) {
                
                throw new Exception(tr('spam_video_id_error'), 101);
            }

            // Load all the users based on the selected 

            $spam_videos = Flag::where('video_id', $request->admin_video_id)->paginate(10);

            return view('admin.spam_videos.user_report')
                        ->with('page' , 'videos')
                        ->with('sub_page' , 'spam_videos')
                        ->with('spam_videos' , $spam_videos)
                        ->with('video_details' , $admin_video_details);   
            
        } catch (Exception $e) {
              
            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    }

    /**
     * Function Name admin_videos_spams_remove() 
     *
     * @uses Delete the spam details
     *
     * @created Maheswari
     *
     * @updated vithya R
     *
     * @param integer $flag_id
     *
     * @return success/failure message
     */   
    public function admin_videos_spams_remove($flag_id) {
       
       try {
            DB::beginTransaction();

            if(!$flag_id) {

                throw new Exception(tr('spam_video_id_error'), 101);
            }

            $flag_details = Flag::find($flag_id);

            if(!$flag_details) {

                throw new Exception(tr('spam_details_not_found'), 101);                
            }

            $flag_details->delete();
            
            DB::commit();

            return back()->with('flash_success',tr('spam_deleted'));
                   
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);
        }
    }

   /**
    * Function Name : offline_videos()
    *
    * @uses To list out offline videos based on users
    *
    * @created Vidhya R
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

                    throw new Exception(tr('sub_profile_details_not_found'),101);
                }

            } else {

                $subProfile = SubProfile::where('user_id', $request->id)
                            ->where('id', $request->sub_profile_id)->first();

                if (!$subProfile) {

                    throw new Exception(tr('sub_profile_details_not_found'),101);
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

                $error = implode(',', $validator->messages()->all());

                $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error);

                throw new Exception($error);
            } 

            $user = User::find($request->id);

            $videos = OfflineAdminVideo::select('admin_videos.title', 'offline_admin_videos.*')
                ->leftJoin('admin_videos', 'admin_videos.id', '=', 'offline_admin_videos.admin_video_id')
                ->where('user_id', $request->id)->paginate(10);

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
    * @created Vidhya R
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
            } 

            $offline_admin_video_details = OfflineAdminVideo::where('admin_video_id',$request->admin_video_id)->where('user_id', $request->id)->first();

            if ($offline_admin_video_details) {

                if ($offline_admin_video_details->delete()) {


                } else {

                    throw new Exception(tr('offline_video_not_delete'));
                    
                }

            } else {

                throw new Exception(tr('offline_video_not_save'));
                
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
     * Function: admin_videos_download_status()
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

    public function admin_videos_download_status(Request $request) {

        try {
            
            DB::beginTransaction();

            $admin_video_details = AdminVideo::find($request->admin_video_id);

            if(!$admin_video_details) {

                throw new Exception(tr('video_not_found'), 101);
            }

            // Check the video is eligible for download

            $is_video_eligible_for_download = VideoHelper::check_video_download_eligibility($admin_video_details);

            if($is_video_eligible_for_download == NO) {

                $error = tr('admin_video_not_eligible_for_download');

                return back()->with('flash_error', $error);        
            }

            $admin_video_details->download_status = $admin_video_details->download_status == ENABLED_DOWNLOAD ?  DISABLED_DOWNLOAD : ENABLED_DOWNLOAD;

            if($admin_video_details->save()) {
                
                DB::commit();

                $message = $admin_video_details->download_status == ENABLED_DOWNLOAD ? tr('admin_video_marked_as_downloadable') : tr('admin_video_removed_from_downloadable');

                return back()->with('flash_success', $message);              
            
            } 
                
            throw new Exception(tr('admin_video_download_error'), 101);  
           
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();

            return back()->with('flash_error', $error);        
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

            $max_original_count = Setting::get('max_original_count') ?: 12;

            if($total_original_based_videos > $max_original_count) {

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



}

