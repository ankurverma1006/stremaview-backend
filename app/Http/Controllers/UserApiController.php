<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Helper;

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

use App\Subscription;

use App\Card;

use App\Notification;

use App\PayPerView;

use App\Moderator;

use App\Flag;

use App\Genre;

use App\LikeDislikeVideo;

use App\UserPayment;

use App\User;

use App\Admin;

use App\AdminVideo;

use App\AdminVideoImage;

use App\Settings;

use App\UserRating;

use App\Wishlist;

use App\UserHistory;

use App\Coupon;

use App\Page;

use App\Category;

use App\SubProfile;

use App\UserLoggedDevice;

use App\ContinueWatchingVideo;

use App\SubCategory;

use App\VideoCastCrew;

use App\CastCrew;

use App\UserCoupon;

use App\OfflineAdminVideo;

class UserApiController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('UserApiVal' , 
                    array('except' => ['register' , 
                            'login' , 
                            'forgot_password',
                            'privacy',
                            'about' , 
                            'terms',
                            'contact', 
                            'site_settings',
                            'allPages',
                            'getPage', 
                            'check_social', 
                            'searchAll', 
                            'reasons',
                            'save_continue_watching_video', // While watching video we no need to logout the user
                            ])
                    );

    }
    
    /**
     * Function Name : register()
     * 
     * @uses Register a new user 
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
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

                $allowedSocialLogin = array('facebook','google');

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

                        $response_array = array(
                            'success' => true,
                            'id' => $user->id,
                            'name' => $user->name,
                            'mobile' => $user->mobile,
                            'gender' => $user->gender,
                            'email' => $user->email,
                            'picture' => $user->picture,
                            'token' => $user->token,
                            'token_expiry' => $user->token_expiry,
                            'login_by' => $user->login_by,
                            'social_unique_id' => $user->social_unique_id,
                            'verification_control'=> Setting::get('email_verify_control'),
                            'sub_profile_id'=>$sub_profile->id,
                            'email_notification'=>$user->email_notification,
                            'payment_subscription' => Setting::get('ios_payment_subscription_status'),
                            'message'=> Setting::get('email_verify_control') ? tr('register_verify_success') : tr('register_success')
                        );

                        $response_array = Helper::null_safe($response_array);

                        $response_array['user_type'] = $user->user_type ? 1 : 0;

                        $response_array['push_status'] = $user->push_status ? 1 : 0;

                    } else {

                        $response_array = ['success'=>false, 'error_messages'=>Helper::get_error_message(3001), 'error_code'=>3001];

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
     * Function Name : login()
     *
     * @uses Registered user can login using their email & Password
     * 
     * @created: Vithya R
     * 
     * @edited: Shobana C
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

                $subProfile = SubProfile::where('user_id', $user->id)->first();

                if ($subProfile) {

                    $sub_profile_id = $subProfile->id;

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


                   // if ($user->no_of_account > $user->logged_in_account) {
 
                        // Generate new tokens
                        // $user->token = Helper::generate_token();

                    // $user->token = Helper::generate_token();

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


                        // Respond with user details

                        $response_array = array(
                            'success' => true,
                            'id' => $user->id,
                            'name' => $user->name,
                            'mobile' => $user->mobile,
                            'email' => $user->email,
                            'gender' => $user->gender,
                            'picture' => $user->picture,
                            'token' => $user->token,
                            'token_expiry' => $user->token_expiry,
                            'login_by' => $user->login_by,
                            'is_activated'=>$user->is_activated,
                            // 'user_type' => $user->user_type,
                            'sub_profile_id'=>$sub_profile_id,
                            'social_unique_id' => $user->social_unique_id,
                            // 'push_status' => $user->push_status,
                            'one_time_subscription'=>$user->one_time_subscription,
                            'sub_profile_id'=>$sub_profile_id,
                            'email_notification'=>$user->email_notification,
                            'payment_subscription' => Setting::get('ios_payment_subscription_status'),
                            'message'=>tr('login_success'),
                            'logged_in_account'=>$user->logged_in_account,
                        );

                        $response_array = Helper::null_safe($response_array);

                        $response_array['user_type'] = $user->user_type ? 1 : 0;
                        $response_array['push_status'] = $user->push_status ? 1 : 0;


                    /*} else {

                        throw new Exception(tr('user_details_not_save'));
                        
                    }*/                       
                } else {

                    throw new Exception(tr('no_of_logged_in_device'));
                    
                }
                   
               
            }
           
            DB::commit();

            $response = response()->json($response_array, 200);

            return $response;

        } catch(Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return response()->json($response_array);

        }
    
    }

 
    /**
     * Function Name : forgot_password()
     *
     * @uses If the user forgot his/her password he can hange it over here
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *     
     * @param object $request - Email id
     *
     * @return send mail to the valid user
     */
    public function forgot_password(Request $request) {

        try {

            DB::beginTransaction();

            $email =$request->email;
            
            $validator = Validator::make(

                $request->all(),

                array(
                    'email' => 'required|email|exists:users,email',
                ),
                 array(
                    'exists' => 'The :attribute doesn\'t exists',
                )
            );

            if ($validator->fails()) {
                
                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages, 101);
            
            } else {

                $user = User::where('email' , $email)->first();

                if($user) {

                    // If the user social login users, should not send mail to the users.

                    $allowedSocialLogin = array('facebook','google');

                    if (in_array($user->login_by,$allowedSocialLogin)) {

                        throw new Exception(tr('you_registered_as_social_user'));

                    } else {

                        if(!$user->is_activated) {

                            throw new Exception(Helper::get_error_message(905), 905);

                        }

                        if (!$user->is_verified) {

                            if (Setting::get('email_verify_control')) {

                                throw new Exception(Helper::get_error_message(3001), 3001);

                            } else {

                                $user->is_verified = USER_EMAIL_VERIFIED;

                            }

                        }

                        $new_password = Helper::generate_password();

                        $user->password = Hash::make($new_password);

                        $user->save();

                        $email_data = array();

                       // $email = $user->email;

                        $email_data['email']  = $user->email;

                        $email_data['password'] = $new_password;

                        $email_data['template_type'] = FORGOT_PASSWORD;

                        $page = "emails.forgot-password";

                        $email_send = Helper::send_email($page,$subject = null,$user->email,$email_data);

                        $response_array['success'] = true;

                        $response_array['message'] = Helper::get_message(106);

                    } 

                } else {

                    throw new Exception(tr('no_user_detail_found'));
                    
                }

            }

            DB::commit();

            $response = response()->json($response_array, 200);

            return $response;

        } catch(Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return response()->json($response_array);
        }
    
    }

    /**
     * Function Name : change_password()
     *
     * @uses To change the password of the user
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - Password & confirm Password
     *
     * @return json response of the user
     */
    public function change_password(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                    'password' => 'required|confirmed',
                    'old_password' => 'required',
                ]);

            if($validator->fails()) {
                
                $error_messages = implode(',',$validator->messages()->all());
               
                throw new Exception($error_messages, 101);
           
            } else {

                $user = User::find($request->id);

                if(Hash::check($request->old_password,$user->password)) {

                    $user->password = Hash::make($request->password);
                    
                    $user->save();

                    $response_array = Helper::null_safe(array('success' => true , 'message' => Helper::get_message(102)));

                } else {

                    throw new Exception(Helper::get_error_message(131), 131);
                    
                }

            }

            DB::commit();

            $response = response()->json($response_array,200);

            return $response;

        } catch(Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return response()->json($response_array);

        }

    }

    /** 
     * Function Name : user_details()
     *
     * @uses To display the user details based on user  id
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - User Id
     *
     * @return json response with user details
     */
    public function user_details(Request $request) {

        try {

            $user = User::find($request->id);

            if (!$user) { 

                throw new Exception(tr('no_user_detail_found'), 101);
                
            }

            $subProfile = SubProfile::where('user_id', $user->id)
                    ->where('status', DEFAULT_TRUE)
                    ->first();

            $sub_profile_id = ($subProfile) ? $subProfile->id : '';

            $card_last_four_number = "";

            if ($user->card_id) {

                $card = Card::find($user->card_id);

                if ($card) {

                    $card_last_four_number = $card->last_four;

                }

            }

            $user_payment_details = UserPayment::where('user_id', $request->id)->where('status', PAID_STATUS)->orderBy('created_at', 'desc')->first();

            $current_subscription_plan = "";

            if ($user_payment_details) {

                if($subscription_details = $user_payment_details->subscription) {

                    $active_plan = $subscription_details->title ?: "";
                }

            }

     

            $response_array = array(
                'success' => true,
                'id' => $user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
                'gender' => $user->gender,
                'email' => $user->email,
                'picture' => $user->picture,
                'token' => $user->token,
                'token_expiry' => $user->token_expiry,
                'login_by' => $user->login_by,
                'social_unique_id' => $user->social_unique_id,
                'user_type'=>$user->user_type,
                'sub_profile_id'=>$sub_profile_id,
                'card_last_four_number'=>$card_last_four_number,
                'email_notification'=>$user->email_notification,
                'current_subscription_plan'=> $current_subscription_plan
            );

            $response = response()->json(Helper::null_safe($response_array), 200);

            return $response;

        } catch(Exception $e) {

            $response_array = ['success'=>false , 'error_messages'=> $e->getMessage(), 'error_code'=>$e->getCode()];

            return response()->json($response_array);
        }
    
    }
 
    /**
     * Function Name : update_profile()
     *
     * @uses To update the user details
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param objecct $request : User details
     *
     * @return json response with user details
     */
    public function update_profile(Request $request) {

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

                // Error messages added in response for debugging
                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {

                $user = User::find($request->id);

                if($user) {
                    
                    $user->name = $request->name ? $request->name : $user->name;
                    
                    if($request->has('email')) {

                        $user->email = $request->email;
                    }

                    $user->mobile = $request->mobile ? $request->mobile : $user->mobile;

                    $user->gender = $request->gender ? $request->gender : $user->gender;

                    $user->address = $request->address ? $request->address : $user->address;

                    $user->description = $request->description ? $request->description : $user->address;

                    // Upload picture
                    if ($request->hasFile('picture') != "") {

                        Helper::delete_picture($user->picture, "/uploads/images/"); // Delete the old pic

                        $user->picture = Helper::normal_upload_picture($request->file('picture'));

                    }

                    if ($user->save()) {

                        $payment_mode_status = $user->payment_mode ? $user->payment_mode : "";

                        $subProfile = SubProfile::where('user_id', $user->id)->where('status', DEFAULT_TRUE)->first();

                        $sub_profile_id = ($subProfile) ? $subProfile->id : '';

                        $response_array = array(
                            'success' => true,
                            'id' => $user->id,
                            'name' => $user->name,
                            'mobile' => $user->mobile,
                            'gender' => $user->gender,
                            'email' => $user->email,
                            'picture' => $user->picture,
                            'token' => $user->token,
                            'token_expiry' => $user->token_expiry,
                            'login_by' => $user->login_by,
                            'social_unique_id' => $user->social_unique_id,
                            'sub_profile_id'=>$sub_profile_id,
                            'message'=>tr('update_success')
                        );

                        $response_array = Helper::null_safe($response_array);

                    } else {

                        throw new Exception(tr('user_details_not_save'));
                        
                    }

                } else {

                    throw new Exception(tr('no_user_detail_found'));
                    
                }
            
            }

            DB::commit();

            $response = response()->json($response_array, 200);

            return $response;

        } catch (Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return $response_array;
        }
    
    }

    /**
     * Function Name : delete_account()
     * 
     * @uses Delete user account based on user id
     * 
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - Password and user id
     *
     * @return json with boolean output
     */
    public function delete_account(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'password' => '',
                ));

            if ($validator->fails()) {

                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {

                $user = User::find($request->id);

                if (!$user) {

                    throw new Exception(tr('no_user_detail_found'), 101);
                    
                }

                if($user->login_by != MANUAL) {

                    $allow = YES;

                } else {

                    if(Hash::check($request->password, $user->password)) {

                        $allow = YES;

                    } else {

                        $allow =  NO;

                        throw new Exception(Helper::get_error_message(108), 108);
                        
                    }

                }

                if($allow) {

                    if ($user->device_type) {

                        // Load Mobile Registers
                        subtract_count($user->device_type);
                        
                    }

                    $user->delete();

                    $response_array = array('success' => true , 'message' => tr('user_account_delete_success'));

                }

            }

            DB::commit();

    		return response()->json($response_array,200);

        } catch(Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return response()->json($response_array);
        }

	}

    /**
     * Function Name : wishlist_add()
     *
     * @uses To add wishlist of logged in user
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - Sub profile id & Video id
     *
     * @return response of wishlist
     */
    public function wishlist_add(Request $request) {

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
                    'sub_profile_id'=>'required|exists:sub_profiles,id'
                ),
                array(
                    'exists' => 'The :attribute doesn\'t exists please provide correct video id',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);

            } else {

                if (check_flag_video($request->admin_video_id,$request->sub_profile_id)) {

                    throw new Exception(tr('flagged_video'), 101);

                }

                $wishlist = Wishlist::where('user_id' , $request->sub_profile_id)
                            ->where('admin_video_id' , $request->admin_video_id)
                            ->first();

                if(count($wishlist) > 0) {

                    $wishlist->delete();

                    $response_array = ['success'=>true, 
                        'message'=> tr('wishlist_removed'),
                        'wishlist_status' => 0];
                    
                } else {
                    
                    $wishlist = new Wishlist();

                    $wishlist->user_id = $request->sub_profile_id;

                    $wishlist->sub_profile_id = $request->sub_profile_id;

                    $wishlist->admin_video_id = $request->admin_video_id;

                    $wishlist->status = DEFAULT_TRUE;

                    if ($wishlist->save()) {

                        $response_array = array('success' => true ,
                                'wishlist_id' => $wishlist->id ,
                                'wishlist_status' => $wishlist->status,
                                'message' => tr('wishlist_add_success'));

                    } else {

                        throw new Exception(tr('wishlist_not_save'), 101);
                        
                    }
                }
               
            }

            DB::commit();

            $response = response()->json($response_array, 200);

            return $response;

        } catch (Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return response()->json($response_array);
            
        }
    
    }

    /**
     * Function Name : wishlist_index()
     *
     * @uses To get all the lists based on logged in user id
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - Wishlist id
     *
     * @return respone with array of objects
     */
    public function wishlist_index(Request $request)  {

        try {

            $validator = Validator::make(
                $request->all(),
                array(
                    'skip' => 'required|numeric',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {

                if (!$request->has('sub_profile_id')) {

                    $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                    if ($sub_profile) {
                        
                        $sub_profile_id = $sub_profile->id;

                    } else  {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                } else  {

                    $subProfile = SubProfile::where('user_id', $request->id)
                                    ->where('id', $request->sub_profile_id)->first();

                    if (!$subProfile) {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                    $sub_profile_id = $request->sub_profile_id;
                 
                }
            

                $wishlist = Helper::wishlist($request->id,$request->skip, $take = Setting::get('admin_take_count'), $sub_profile_id);


                $user = User::find($request->id);

                $wishlist_video = [];

                if ($wishlist != null && !empty($wishlist)) {

                    foreach ($wishlist as $key => $value) {

                        $display_details = displayFullDetails($value->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $sub_profile_id);

                        if ($display_details) {
 
                            $wishlist_video[] = $display_details;

                        }

                    }
                }

                $total = count($wishlist_video);

                // $total = 0;

        		$response_array = array('success' => true, 'wishlist' => $wishlist_video , 'total' => $total);

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);

        }
    
    }

    /**
     * Function Name : wishlist_delete()
     * 
     * @uses To delete wishlist based on the logged in user id and video id
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
     * Function Name : history_add
     *
     * @uses To add history based on logged in user id
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - History Id
     *
     * @return response with history details
     */
    public function history_add(Request $request)  {

        Log::info("history_add - ".print_r($request->all(), true));

        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'admin_video_id' => 'required|integer|exists:admin_videos,id,status,'.VIDEO_PUBLISHED.',is_approved,'.VIDEO_APPROVED,
                    'sub_profile_id' => 'required|integer|exists:sub_profiles,id',
                ),
                array(
                    'exists' => 'The :attribute doesn\'t exists please provide correct video id',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);

            }

            if (check_flag_video($request->admin_video_id,$request->sub_profile_id)) {

                throw new Exception(tr('flagged_video'));

            }

            $user_history_details = UserHistory::where('user_id' , $request->id)
                            ->where('sub_profile_id' , $request->sub_profile_id)
                            ->where('admin_video_id' ,$request->admin_video_id)
                            ->first();

            if (!$user_history_details) {

                $rev_user = new UserHistory();

                $rev_user->user_id = $request->id;

                $rev_user->sub_profile_id = $request->sub_profile_id;

                $rev_user->admin_video_id = $request->admin_video_id;

                $rev_user->save();

            }

            $payperview = PayPerView::where('user_id', $request->id)
                            ->where('video_id',$request->admin_video_id)
                            ->where('status',DEFAULT_TRUE)
                            ->where('is_watched',DEFAULT_FALSE)
                            ->orderBy('id', 'desc')
                            ->first();

            $navigateback = 0;

            if($video = AdminVideo::find($request->admin_video_id)) {

                // Check the video have PPV -> if yes need to restrict the watch_count revenue

                if ($video->amount <= 0) {

                    \Log::info("uploaded_by ".$video->uploaded_by);

                    \Log::info("Viewer Count ".Setting::get('video_viewer_count'));

                    if($video->watch_count >= Setting::get('video_viewer_count') && is_numeric($video->uploaded_by)) {

                        $video_amount = Setting::get('amount_per_video');

                        // $video->watch_count = $video->watch_count + 1;

                        $video->redeem_amount += $video_amount;

                        Log::info("Uploaded By ".$video->uploaded_by);

                        if($moderator = Moderator::find($video->uploaded_by)) {

                            Log::info("Inside");

                            $moderator->total_user_amount += $video_amount;

                            $moderator->remaining_amount += $video_amount;

                            $moderator->total += $video_amount;

                            $moderator->save();

                        }

                        add_to_redeem($video->uploaded_by , $video_amount);

                    } 
                }

                $video->watch_count += 1;

                $video->save();

                if ($video->type_of_subscription == RECURRING_PAYMENT) {

                    $navigateback = 1;

                }
            
            }

            if ($payperview) {

                $payperview->is_watched = DEFAULT_TRUE;

                $payperview->save();

            }

            $response_array = array('success' => true , 'message'=>tr('added_history'), 'navigateback'=>$navigateback);

            Log::info("response_array --- ".print_r($response_array, true));

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            Log::info("response_array --- ".print_r($response_array, true));

            return response()->json($response_array);
        }
    
    }

    /**
     * Function Name : history_index()
     *  
     * @uses To get all the history details based on logged in user id
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - User Profile details
     *
     * @return Response with list of details
     */     
    public function history_index(Request $request) {

        try {

            $validator = Validator::make(
                $request->all(),
                array(
                    'skip' => 'required|numeric',
                )
            );

            if ($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error);
                
            } else {

                $sub_profile_id = 0;

                if (!$request->has('sub_profile_id')) {

                    $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                    if ($sub_profile) {

                        $sub_profile_id = $sub_profile->id;

                    } else {

                        throw new Exception(tr('sub_profile_details_not_found'));
                    }

                } else {

                    $subProfile = SubProfile::where('user_id', $request->id)
                                                ->where('id', $request->sub_profile_id)->first();

                    if (!$subProfile) {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                    $sub_profile_id = $request->sub_profile_id;

                }
                
        		//get wishlist

                $history = Helper::watch_list($sub_profile_id,$request->skip);

                $user = User::find($request->id);

                $history_video = [];

                if ($history != null && !empty($history)) {

                    foreach ($history as $key => $value) {
                        
                        $display_details = displayFullDetails($value->admin_video_id,$user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $sub_profile_id);

                        if($display_details) {

                            $history_video[] = $display_details;

                        }
 
                    }
                }

                $total = count($history_video);

        		$response_array = array('success' => true, 'history' => $history_video , 'total' => $total);

                return response()->json($response_array, 200);

            }

        } catch (Exception $e) {

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return response()->json($response_array);
        }   
    
    }

    /**
     * Function Name : history_delete()
     *
     * @uses To delete history based on login id
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param Object $request - History Id
     *
     * @return Json object based on history
     */
    public function history_delete(Request $request) {

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
                    'admin_video_id' => $request->status ? 'integer|exists:admin_videos,id' : 'required|integer|exists:admin_videos,id',
                    'sub_profile_id' => 'required|integer|exists:sub_profiles,id',
                ),
                array(
                    'exists' => 'The :attribute doesn\'t exists please add to history',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);
                
            } else {


                if($request->status) {

                    $history = UserHistory::where('user_id',$request->sub_profile_id)->delete();

                } else {

                    $history = UserHistory::where('admin_video_id' ,  $request->admin_video_id)
                        ->where('user_id', $request->sub_profile_id)
                        ->delete();
                }

                $response_array = array('success' => true, 'message'=>tr('delete_history_success'));
            }

            DB::commit();

            $response = response()->json($response_array, 200);

            return $response;

        } catch(Exception $e) {

            DB::rollback();

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return $response_array;
        }
    
    }

    /**
     * Function Name : get_categories
     *
     * @uses To get all the categories
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - As of now no attributes
     *
     * @return array of response
     */
    public function get_categories(Request $request) {

        $categories = get_categories();

        if($categories) {

            if ($categories != null && !empty($categories)) {

                $response_array = array('success' => true , 'categories' => $categories->toArray());

            } else {

                $response_array = array('success' => true , 'categories' => []);
            }
        
        } else {

            $response_array = array('success' => false,'error_messages' => Helper::get_error_message(135),'error_code' => 135);
        }

        $response = response()->json($response_array, 200);

        return $response;
    
    }


    /**
     * Function Name : get_sub_categories()
     *
     * @uses To get sub categories based on category id
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object $request - Category id
     *
     * @return response of array
     */
    public function get_sub_categories(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'category_id' => 'required|integer|exists:categories,id',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $sub_categories = get_sub_categories($request->category_id);

            if($sub_categories) {

                if ($sub_categories != null && !empty($sub_categories)) {

                    $response_array = array('success' => true , 'sub_categories' => $sub_categories->toArray());

                } else {

                    $response_array = array('success' => true , 'sub_categories' => []);

                }

            } else {
                $response_array = array('success' => false,'error_messages' => Helper::get_error_message(130),'error_code' => 130);
            }

        }

        $response = response()->json($response_array, 200);

        return $response;
    
    }

    /**
     * Function Name : home()
     * 
     * @uses To list out all wishlist, history, recommended videos, suggestion videos based on logged in user 
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @param object @request - User Id, skip , take and etc
     *
     * @return response of array
     */
    public function home(Request $request) {

        $videos = $wishlist = $recent =  $banner = $trending = $history = $suggestion =array();

        counter('home');

        if (!$request->has('sub_profile_id')) {

            $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

            if ($sub_profile) {

                $request->request->add([ 

                    'sub_profile_id' => $sub_profile->id,

                ]);

                $id = $sub_profile->id;

            } else {

                $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];

                return response()->json($response_array , 200);

            }

        } else {

            $subProfile = SubProfile::where('user_id', $request->id)
                        ->where('id', $request->sub_profile_id)->first();

            if (!$subProfile) {

                $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];
                
                return response()->json($response_array , 200);
                
            } else {

                $id = $subProfile->id;

            }

        } 

        $user = User::find($request->id);

        $banner['name'] = tr('mobile_banner_heading');

        $banner['key'] = BANNER;

        $banner['list'] = Helper::banner_videos($id);

        $continue_videos['name'] = tr('continue_watching_videos');

        $continue_videos['key'] = CONTINUE_WATCHING;

        $continnue_watching_videos = Helper::continue_watching_videos($request->sub_profile_id, $request->device_type, 0);

        $continue_watch_videos = [];

        foreach ($continnue_watching_videos as $key => $value) {

            $continue_video = displayFullDetails($value->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

            if ($continue_video) {
            
                $continue_watch_videos[] = $continue_video;

            }

        }

        $continue_videos['list'] = $continue_watch_videos;

        array_push($videos , $continue_videos);

        $wishlist['name'] = tr('mobile_wishlist_heading');

        $wishlist['key'] = WISHLIST;

        $skip = $request->skip ?: 0;

        $take = $request->take ?: (Setting::get('admin_take_count') ?: 12);

        $wishlists = Helper::wishlist($request->id, $skip, $take, $id);

        $wishlist_video = [];

        foreach ($wishlists as $key => $value) {
            
            $wishlist_video_details = displayFullDetails($value->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

            if($wishlist_video_details) {

                $wishlist_video[] = $wishlist_video_details;

            }

        }

        $wishlist['list'] = $wishlist_video;

        array_push($videos , $wishlist);

        $recent['name'] = tr('mobile_recent_upload_heading');

        $recent['key'] = RECENTLY_ADDED;

        $recents = Helper::recently_added(WEB, 0, $id);

        $recent_videos = [];

        foreach ($recents as $key => $value) {
            
            $recent_video_details = displayFullDetails($value->id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

            if($recent_video_details) {

                $recent_videos[] = $recent_video_details;

            }

        }

        $recent['list'] = $recent_videos;

        array_push($videos , $recent);

        $trending['name'] = tr('mobile_trending_heading');

        $trending['key'] = TRENDING;

        $trendings = Helper::trending(WEB, 0, $id);

        $trending_videos = [];

        foreach ($trendings as $key => $value) {
            
            $trending_video_details = displayFullDetails($value->id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

            if ($trending_video_details) {

                $trending_videos[] = $trending_video_details;

            }

        }

        $trending['list'] = $trending_videos;

        array_push($videos, $trending);

        $history['name'] = tr('mobile_watch_again_heading');

        $history['key'] = WATCHLIST;

        $history_videos = Helper::watch_list($request->sub_profile_id, 0);

        $histories = [];

        foreach ($history_videos as $key => $value) {
            
            $history_detail = displayFullDetails($value->id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $request->sub_profile_id);

            if ($history_detail) {

                $histories[] = $history_detail;

            }

        }

        $history['list'] = $histories;

        array_push($videos , $history);

        $suggestion['name'] = tr('mobile_suggestion_heading');

        $suggestion['key'] = SUGGESTIONS;

        $suggestion_videos = Helper::suggestion_videos(WEB, null, null, $id);

        $suggestions = [];

        foreach ($suggestion_videos as $key => $value) {
            
            $suggestion_details = displayFullDetails($value->id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

            if($suggestion_details) {

                $suggestions[] = $suggestion_details;

            }

        }

        $suggestion['list'] = $suggestions;

        array_push($videos , $suggestion);

        $recent_video = Helper::recently_video($count = 0, $request->sub_profile_id);

        // Log::info("recent_video".print_r($recent_video->admin_video_id, true));

        $get_video_details = ($recent_video) ? displayFullDetails($recent_video->admin_video_id, $request->id ?: 0, $user ? $user->user_type : 0, $request->device_type, $request->sub_profile_id) : '';

        $response_array = array('success' => true , 'data' => $videos , 'banner' => $banner, 'recent_video'=>$get_video_details);

        return response()->json($response_array , 200);

    }

    /**
     * Function Name : common()
     *
     * @uses To get common response from all the section of videos like recent, recommended, wishlist and history
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     *
     * @TODO : check
     *
     * @param object @request - User Id, skip , take and etc
     * 
     * @request response of array
     */  
    public function common(Request $request) {

        Log::info("common".print_r($request->all() , true));

        $validator = Validator::make(
            $request->all(),
            array(
                'key'=>'required',
                'skip' => 'required|numeric',
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return response()->json(['success'=>false, 'error_messages'=>$error_messages]);
            
        } else {

            $key = $request->key;

            $total = 18;

            switch($key) {

                case TRENDING:

                    $videos = Helper::trending(NULL,$request->skip);

                    break;

                case WISHLIST:

                    $videos = Helper::wishlist($request->id,$request->skip, NULL, $request->sub_profile_id);

                    $total = get_wishlist_count($request->id);

                    break;

                case SUGGESTIONS:

                    $videos = Helper::suggestion_videos(NULL,$request->skip);

                    break;
                case RECENTLY_ADDED:

                    $videos = Helper::recently_added(NULL,$request->skip);

                    break;

                case WATCHLIST:

                    $videos = Helper::watch_list($request->id,NULL,$request->skip);

                    $total = get_history_count($request->id);

                    break;

                default:

                    $videos = Helper::recently_added(NULL,$request->skip);
            }


            $response_array = array('success' => true , 'data' => $videos , 'total' => $total);

            return response()->json($response_array , 200);

        }

    }

    /**
     * Function Name : get_category_videos()
     *
     * @uses Based on category id , videos will dispaly
     *
     * @created: Vithya R
     * 
     * @edited: Shobana C
     * @TODO : check
     *
     * @param object @request - Category id
     *
     * @return array of response
     */
    public function get_category_videos(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'category_id' => 'required|integer|exists:categories,id',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );

        if ($validator->fails())  {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $data = array();

            $sub_categories = get_sub_categories($request->category_id);

            if($sub_categories) {

                foreach ($sub_categories as $key => $sub_category) {

                    $videos = Helper::sub_category_videos($sub_category->id);

                    if(count($videos) > 0) {

                        $results['sub_category_name'] = $sub_category->name;
                        $results['key'] = $sub_category->id;
                        $results['videos_count'] = count($videos);
                        $results['videos'] = $videos->toArray();

                        array_push($data, $results);
                    }
                }
            }

            $response_array = array('success' => true, 'data' => $data);
        }

        $response = response()->json($response_array, 200);

        return $response;

    }

    /**
     * Function Name : get_category_videos()
     *
     * @uses Based on category id , videos will dispaly
     *
     * @created:
     * 
     * @edited: 
     * @TODO : check
     *
     * @param object @request - Category id
     *
     * @return array of response
     */
    public function get_sub_category_videos(Request $request) {

        Log::info("get_sub_category_videos".print_r($request->all() , true));

        $validator = Validator::make(
            $request->all(),
            array(
                'sub_category_id' => 'required|integer|exists:sub_categories,id',
                'skip' => 'integer'
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );

        if ($validator->fails()) {
            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $data = array();

            $total = 18;

            if($videos = Helper::sub_category_videos($request->sub_category_id , NULL,$request->skip)) {
                $data = $videos->toArray();
            }

            $total = get_sub_category_video_count($request->sub_category_id);

            $response_array = array('success' => true, 'data' => $data , 'total' => $total);

        }

        $response = response()->json($response_array, 200);
        
        return $response;

    }

    /**
     * Function Name : single_video()
     *
     * @uses To get a single video page based on the id
     *
     * @created : vidhya R (11/12/2017)
     *
     * @edited : vidhya R (2018-07-21)
     *
     * @param object $request - Video Id
     *
     * @return response of single video details
     */

    public function single_video(Request $request) {

        try {

            $validator = Validator::make(
                $request->all(),
                array(
                    'admin_video_id' => 'required|integer|exists:admin_videos,id,is_approved,1',
                  // 'sub_profile_id'=>'required|sub_profiles,id'
                ),
                array(
                    'exists' => 'The :attribute doesn\'t exists',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

                throw new Exception($error_messages);
                
            } else {

                if (!$request->has('sub_profile_id')) {

                    $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                    if ($sub_profile) {

                        $request->request->add([ 
                            'sub_profile_id' => $sub_profile->id,
                        ]);
                    }

                }  else {

                    $subProfile = SubProfile::where('user_id', $request->id)
                                        ->where('id', $request->sub_profile_id)->first();

                    if (!$subProfile) {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                }

                if (check_flag_video($request->admin_video_id,$request->sub_profile_id)) {

                    throw new Exception(Helper::get_error_message(904), 904);

                }

                $data = Helper::get_video_details($request->admin_video_id);

                $trailer_video = $ios_trailer_video = $data->trailer_video;

                $video = $ios_video = $data->video;

                $video_pixels = $trailer_pixels = "";

                $trailer_video_rtmp_smil = $video_rtmp_smil = $trailer_subtitle_name = $video_subtitle_name = "";

                if($data->video_type == VIDEO_TYPE_UPLOAD && $data->video_upload_type == VIDEO_UPLOAD_TYPE_DIRECT) {

                    $trailer_subtitle_name = $data->trailer_subtitle ? get_video_end($data->trailer_subtitle) : "";

                    $video_subtitle_name = $data->video_subtitle ? get_video_end($data->video_subtitle) : "";

                    if(check_valid_url($data->trailer_video)) {

                        if ($data->trailer_video) {

                            // $trailer_video = Helper::convert_rtmp_to_secure(get_video_end($data->trailer_video) , $data->trailer_video);

                        
                           // $ios_trailer_video = Helper::convert_hls_to_secure(get_video_end($data->trailer_video) , $data->trailer_video);

                            $trailer_video = Setting::get('streaming_url') ? Setting::get('streaming_url').get_video_end($data->trailer_video) : $data->trailer_video;

                            if(Setting::get('HLS_STREAMING_URL')) {
                            
                                $ios_trailer_video = Setting::get('HLS_STREAMING_URL').get_video_end($data->trailer_video);

                            } else {

                                $ios_trailer_video = $data->trailer_video;

                            }

                        }
                        
                    }

                    if(check_valid_url($data->video)) {

                        // if(Setting::get('streaming_url'))
                        // $video = Helper::convert_rtmp_to_secure(get_video_end($data->video) , $data->video);
                            
                        // $ios_video = Helper::convert_hls_to_secure(get_video_end($data->video) , $data->video);
                        
                        $video = Setting::get('streaming_url') ? Setting::get('streaming_url').get_video_end($data->video) : $data->video;

                        $ios_video = Setting::get('HLS_STREAMING_URL') ? Setting::get('HLS_STREAMING_URL').get_video_end($data->video) : $data->video;
                    
                    }

                    if ($request->device_type == DEVICE_WEB) {

                        if (\Setting::get('streaming_url')) {
                            
                            if($data->trailer_video_resolutions) {

                                $trailer_video_rtmp_smil = get_video_end_smil($data->trailer_video).'.smil';

                                // $trailer_video = Helper::web_url().'/uploads/smil/'.$trailer_video_rtmp_smil;

                                $trailer_video = Helper::convert_smil_to_secure($trailer_video_rtmp_smil , $trailer_video);

                            } 

                            if ($data->video_resolutions) {

                                $video_rtmp_smil = get_video_end_smil($data->video).'.smil';

                                // $video = Helper::web_url().'/uploads/smil/'.$video_rtmp_smil;

                                Log::info("video_rtmp_smil".$video_rtmp_smil);

                                $video = Helper::convert_smil_to_secure($video_rtmp_smil , $video);
                            
                            }
                            
                        } else {


                            $video = $data->video_resize_path ? $data->video.','.$data->video_resize_path : $data->video;

                            $video_pixels = $data->video_resolutions ? 'original,'.$data->video_resolutions : 'original';

                            $trailer_video = $data->trailer_resize_path ? $data->trailer_video.','.$data->trailer_resize_path : $data->trailer_video;

                            $trailer_pixels = $data->trailer_video_resolutions ? 'original,'.$data->trailer_video_resolutions : 'original';
                        }
                    
                    }
                }

                if($data->video_type == VIDEO_TYPE_YOUTUBE) {

                    if ($request->device_type != DEVICE_WEB) {

                        $video = $ios_video = get_api_youtube_link($data->video);

                        $trailer_video =  $ios_trailer_video = get_api_youtube_link($data->trailer_video);

                    } else {

                        $video = $ios_video = get_youtube_embed_link($data->video);

                        $trailer_video =  $ios_trailer_video = get_youtube_embed_link($data->trailer_video);


                    }

                }

                $admin_video_images = AdminVideoImage::where('admin_video_id' , $request->admin_video_id)
                                    ->orderBy('is_default' , 'desc')
                                    ->get();

                if ($ratings = Helper::video_ratings($request->admin_video_id,0)) {

                    $ratings = $ratings->toArray();

                }

                $wishlist_status = Helper::wishlist_status($request->admin_video_id,$request->sub_profile_id);

                $history_status = Helper::history_status($request->sub_profile_id,$request->admin_video_id);

                $is_saved = Helper::download_status($request->admin_video_id, $request->id);

                $diff_bw_days = $is_saved ? calculateDays(date('Y-m-d'),$is_saved->expiry_date) : 0;

                $like_status = Helper::like_status($request->sub_profile_id,$request->admin_video_id);

                $share_link = Setting::get('ANGULAR_SITE_URL').'video/'.$request->admin_video_id;

                $user = User::find($request->id);

                $cnt = $this->watch_count($request)->getData();

                $likes_count = Helper::likes_count($request->admin_video_id);

                $video_play_duration = videoPlayDuration($request->admin_video_id, $request->sub_profile_id);

                $is_ppv_status = ($data->type_of_user == NORMAL_USER || $data->type_of_user == BOTH_USERS) ? ( ( $user->user_type == 0 ) ? DEFAULT_TRUE : DEFAULT_FALSE ) : DEFAULT_FALSE; 

                $genrenames = [];

                $genre_videos = [];

                if ($data->genre_id > 0) {

                    $genrenames = Genre::where('genres.sub_category_id' , $data->sub_category_id)
                                        ->leftJoin('admin_videos' , 'genres.id' , '=' , 'admin_videos.genre_id')
                                        ->select(
                                                'genres.id as genre_id',
                                                'genres.name as genre_name'
                                                )
                                        ->groupBy('admin_videos.genre_id')
                                        ->havingRaw("COUNT(admin_videos.id) > 0")
                                        ->orderBy('genres.updated_at', 'desc')
                                        ->where('genres.is_approved', DEFAULT_TRUE)
                                        ->get()->toArray();

                    if (count($genrenames)) {

                        $videos_query = AdminVideo::where('genre_id', $data->genre_id)
                                            // ->whereNotIn('admin_videos.genre_id', [$video->id])
                                            ->where('admin_videos.status' , 1)
                                            ->where('admin_videos.is_approved' , 1)
                                            ->orderBy('admin_videos.created_at', 'desc')
                                            ->whereNotIn('admin_videos.id', [$request->admin_video_id]);

                        if ($request->sub_profile_id) {
                            // Check any flagged videos are present
                            $flagVideos = getFlagVideos($request->sub_profile_id);

                            if($flagVideos) {
                                $videos_query->whereNotIn('admin_videos.id',$flagVideos);
                            }
                        }

                        if($request->device_type == DEVICE_WEB) {

                            // Check any flagged videos are present
                            $continue_watching_videos = continueWatchingVideos($request->sub_profile_id);
                            
                            if($continue_watching_videos) {

                                $videos_query->whereNotIn('admin_videos.id', $continue_watching_videos);

                            }

                        }

                        $seasons = $videos_query->skip(0)->take(6)
                                        ->get();
                        
                        
                        if(!empty($seasons) && $seasons != null) {

                            foreach ($seasons as $key => $value) {

                                $genre_videos[] = [
                                        'title'=>$value->title,
                                        'description'=>$value->description,
                                        'ratings'=>$value->ratings,
                                        'publish_time'=>date('F j y', strtotime($value->publish_time)),
                                        'duration'=>$value->duration,
                                        'watch_count'=>$value->watch_count,
                                        'default_image'=>$value->default_image,
                                        'admin_video_id'=>$value->id,
                                    ];
                            }
                        }
                    }

                }

                $ppv_status = VideoRepo::pay_per_views_status_check($user->id, $user->user_type, $data)->getData();

                $video_cast_crews = VideoCastCrew::select('cast_crew_id', 'name')
                    ->where('admin_video_id', $request->admin_video_id)
                    ->leftjoin('cast_crews', 'cast_crews.id', '=', 'video_cast_crews.cast_crew_id')
                    ->get()->toArray();

                $resolutions = [];

                if ($data->trailer_video_resolutions) {

                    $exp_resolution = explode(',', $data->trailer_video_resolutions);

                    $exp_resize_path = $data->trailer_resize_path ? explode(',', $data->trailer_resize_path) : [];

                    foreach ($exp_resolution as $key => $value) {
                        
                        $resolutions[$value] = isset($exp_resize_path[$key]) ? 
                        $exp_resize_path[$key] : $data->trailer_video;

                    }

                    $resolutions['original'] = $data->trailer_video;

                }


                $resolutions = $resolutions ? implode(',', $resolutions) : '';

                    
                if ($user->no_of_account >= $user->logged_in_account) {

                    $call_signout_option = 0;

                } else {

                    $call_signout_option = 1;

                }

                $response_array = array(
                        'success' => true,
                        'resolutions'=>$resolutions,
                        'user_type' => $user->user_type ? $user->user_type : 0,
                        'wishlist_status' => $wishlist_status,
                        'history_status' => $history_status,
                        'share_link' => $share_link,
                        'main_video' => $video,
                        'trailer_video' => $trailer_video,
                        'ios_video' => $ios_video,
                        'ios_trailer_video' => $ios_trailer_video,
                        'is_liked' => $like_status,
                        'currency' => Setting::get('currency') ? Setting::get('currency') : "$",
                        'likes' => number_format_short($likes_count),
                        'video_subtitle'=>$data->video_subtitle,
                        'trailer_subtitle'=>$data->trailer_subtitle,
                        'trailer_embed_link'=>route('embed_video', array('v_t'=>2, 'u_id'=>$data->unique_id)),
                        'video_embed_link'=>route('embed_video', array('v_t'=>1, 'u_id'=>$data->unique_id)),
                        'pay_per_view_status'=>$ppv_status->success,
                        'ppv_details'=>$ppv_status,
                        'is_ppv_subscribe_page'=>$is_ppv_status,
                        'video_images' => $admin_video_images,
                        'video' => $data,
                        'comments' => $ratings,
                        'watch_count'=>number_format_short($data->watch_count),
                        'trailer_subtitle_name' => $trailer_subtitle_name,
                        'video_subtitle_name' => $video_subtitle_name,
                        'trailer_video_rtmp_smil' => $trailer_video_rtmp_smil,
                        'video_rtmp_smil' => $video_rtmp_smil,
                        'video_play_duration'=>$video_play_duration,
                        'seek'=>seek($video_play_duration),
                        'genres'=>$genrenames,
                        'genre_videos'=>$genre_videos,
                        'genre_id'=>$data->genre_id,
                        'is_genre'=>$data->is_series,
                        'cast_crews'=>$video_cast_crews,
                        'video_pixels'=>$video_pixels,
                        'trailer_pixels'=>$trailer_pixels,
                        'message'=>$ppv_status->success ? tr('ppv_success') : '',
                        'resolutions'=>$resolutions,
                        'call_signout_option'=>$call_signout_option,
                        'download_status'=>$data->download_status,
                        'download_link'=>$data->video,
                        'is_saved'=>$is_saved ? $is_saved->status : 0,
                        'video_save_status'=>$is_saved ? $is_saved->download_status : 0,
                        'video_save_status_text'=>$is_saved ? downloadStatus($is_saved->download_status) : "",
                        'no_of_days'=>$diff_bw_days > 0 ? $diff_bw_days : 0
                );
            }

            $response = response()->json($response_array, 200);

            return $response;

        } catch (Exception $e) {

            $message = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$message, 'error_code'=> $code];

            return response()->json($response_array);
        }

    }

    /**
     * Function Name : search_video()
     *
     * @uses To search videos based on title
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - Title of the video (For Web Usage)
     *
     * @return response of the array 
     */
    public function search_video(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'key' => '',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $query = AdminVideo::where('admin_videos.is_approved' , 1)
                ->leftjoin('categories', 'categories.id', '=', 'admin_videos.category_id')
                ->leftjoin('sub_categories', 'sub_categories.id', '=', 'admin_videos.sub_category_id')
                ->where('title', 'like', '%' . $request->key . '%')
                ->whereNotIn('admin_videos.is_banner',[1])
                ->where('admin_videos.status' , 1)
                ->videoResponse()
                ->leftJoin('genres' , 'admin_videos.genre_id' , '=' , 'genres.id')
                ->where('categories.is_approved' , 1)
                ->where('sub_categories.is_approved' , 1)
                ->orderBy('admin_videos.created_at' , 'desc');

            $subProfile = SubProfile::where('user_id', $request->id)
                        ->where('id', $request->sub_profile_id)->first();

            if (!$subProfile) {

                return response()->json(['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')]);
                
            }

            if ($request->key) {

               if($request->skip >= 0 && $request->device_type == DEVICE_IOS) {

                    $query->skip($request->skip)->take(12);

                }

            } else {

                $query->skip(0)->take(6);
            }
                
            if ($request->sub_profile_id) {
                
                $flagVideos = getFlagVideos($request->sub_profile_id);

                if($flagVideos) {

                    $query->whereNotIn('admin_videos.id', $flagVideos);

                }

            }

            $videos = $query->get();

            $user = User::find($request->id);

            $results = [];

            if (!empty($videos) && $videos != null) {

                if($request->device_type == DEVICE_WEB) {

                    $chunk = $videos->chunk(4);

                    foreach ($chunk as $key => $value) {

                        $group = [];

                        foreach ($value as $key => $data) {
                         
                            $full_details = displayFullDetails($data->admin_video_id, $user ? $user->id : '', $user ? $user->user_type : '', $request->device_type, $request->sub_profile_id);

                            if($full_details) {

                                $group[] = $full_details;

                            }

                        }

                        array_push($results , $group);

                    }

                } else {

                    foreach ($videos as $key => $value) {

                        $full_details = displayFullDetails($value->admin_video_id, $user ? $user->id : '', $user ? $user->user_type : '', $request->device_type, $request->sub_profile_id);

                        if($full_details) {

                            $results[] = $full_details;

                        }

                    }
                    
                }

            }

            $response_array = array('success' => true, 'data' => $results, 'title'=>$request->key);
        }

        $response = response()->json($response_array, 200);

        return $response;

    }


    /**
     * Function Name : Privacy
     * 
     * @uses To display privacy & Policy of the site (Static page)
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - As of now no attributes
     *
     * @return content,title and type of the page
     */
    public function privacy(Request $request) {

        $page_data['type'] = $page_data['heading'] = $page_data['content'] = "";

        $page = Page::where('type', 'privacy')->first();

        if($page) {

            $page_data['type'] = "privacy";
            $page_data['heading'] = $page->heading;
            $page_data ['content'] = $page->description;
        }

        $response_array = array('success' => true , 'page' => $page_data);

        return response()->json($response_array,200);

    }

    /**
     * Function Name : about()
     * 
     * @uses To display about us page of the site (Static page)
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - As of now no attributes
     *
     * @return content,title and type of the page
     */

    public function about(Request $request) {

        $page_data['type'] = $page_data['heading'] = $page_data['content'] = "";

        $page = Page::where('type', 'about')->first();

        if($page) {

            $page_data['type'] = 'about';

            $page_data['heading'] = $page->heading;

            $page_data ['content'] = $page->description;
        }

        $response_array = array('success' => true , 'page' => $page_data);

        return response()->json($response_array,200);

    }

    /**
     * Function Name : terms()
     * 
     * @uses To display terms & condiitions of the site (Static page)
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - As of now no attributes
     *
     * @return content,title and type of the page
     */
    public function terms(Request $request) {

        $page_data['type'] = $page_data['heading'] = $page_data['content'] = "";

        $page = Page::where('type', 'terms')->first();

        if($page) {

            $page_data['type'] = "Terms";

            $page_data['heading'] = $page->heading;

            $page_data ['content'] = $page->description;
        }

        $response_array = array('success' => true , 'page' => $page_data);

        return response()->json($response_array,200);

    }

    /**
     * Function Name : settings()
     * 
     * @uses To enable/disable the push notification in mobile
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - push notification status
     *
     * @return boolean 
     */
    public function settings(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'status' => 'required',
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

        } else {

            $user = User::find($request->id);

            $user->push_status = $request->status;

            $user->save();

            if($request->status) {

                $message = tr('push_notification_enable');

            } else {

                $message = tr('push_notification_disable');

            }

            $response_array = array('success' => true, 'message' => $message , 'push_status' => $user->push_status);
        }

        $response = response()->json($response_array, 200);

        return $response;
    }

    /**
     * Function Name ; keyBasedDetails()
     * 
     * @uses To get videos based on the key like wishlist, recent uploads, recommended & history (mobile Usage)
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - key, skip, take and etc
     *
     * @return array of videos
     */
    public function keyBasedDetails(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'key'=>'required',
                'skip' => 'required|numeric',
                'take'=> $request->has('take') ? 'required|numeric' : 'numeric',
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return response()->json(['success'=>false, 'error_messages'=>$error_messages]);
            
        } else {

            $genres = [];

            if (!$request->has('take')) {

                $request->request->add(['take' => Setting::get('admin_take_count')]);

            }

            if (!$request->has('sub_profile_id')) {

                $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                if ($sub_profile) {

                    $request->request->add([ 

                        'sub_profile_id' => $sub_profile->id,

                    ]);

                    $id = $sub_profile->id;

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];

                    return response()->json($response_array , 200);

                }

            } else {

                $subProfile = SubProfile::where('user_id', $request->id)
                            ->where('id', $request->sub_profile_id)->first();

                if (!$subProfile) {

                    $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];
                    
                    return response()->json($response_array , 200);
                    
                } else {

                    $id = $subProfile->id;

                }

            } 

            $user = User::find($request->id);

            switch ($request->key) {

                case WISHLIST:

                    $model = Helper::wishlist($request->id,$request->skip, $request->take, $request->sub_profile_id);

                    $videos = [];

                    foreach ($model as $key => $data) {                    

                        $videos[] = displayFullDetails($data->admin_video_id, $user ? $user->id : '', $user ? $user->user_type : '',$request->device_type ,$request->sub_profile_id);;

                    }

                    $title = tr('mobile_wishlist_heading');

                    break;

                case CONTINUE_WATCHING:

                    $model = Helper::continue_watching_videos($request->sub_profile_id,$request->device_type,$request->skip);

                    $videos = [];

                    foreach ($model as $key => $value) {
                         
                            $videos[] = displayFullDetails($value->admin_video_id, $user ? $user->id : '', $user ? $user->user_type : '',$request->device_type ,$request->sub_profile_id);

                    }

                    $title = tr('continue_watching_videos');

                    break;

                case TRENDING:

                    $model = Helper::trending(NULL, $request->skip, $request->take, $request->sub_profile_id);

                    $videos = [];

                    foreach ($model as $key => $data) {                    

                        $videos[] = displayFullDetails($data->admin_video_id, $user ? $user->id : '', $user ? $user->user_type : '',$request->device_type ,$request->sub_profile_id);

                    }

                    $title = tr('mobile_trending_heading');

                    break;

                case RECENTLY_ADDED:

                    $model = Helper::recently_added(NULL, $request->skip, $request->take, $request->sub_profile_id);

                    $videos = [];

                    foreach ($model as $key => $data) {                    

                        $videos[] = displayFullDetails($data->admin_video_id, $user ? $user->id : '', $user ? $user->user_type : '',$request->device_type ,$request->sub_profile_id);;

                    }

                    $title = tr('mobile_recent_upload_heading');

                    break;

                case WATCHLIST:

                    $model = Helper::watch_list($request->sub_profile_id, NULL, $request->skip, $request->take);

                    $videos = [];

                    foreach ($model as $key => $data) {                    

                        $videos[] = displayFullDetails($data->admin_video_id, $user ? $user->id : '', $user ? $user->user_type : '',$request->device_type ,$request->sub_profile_id);

                    }

                    $title = tr('mobile_watch_again_heading');

                    break;

                case SUGGESTIONS:

                    $model = Helper::suggestion_videos(NULL, $request->skip, null, $request->sub_profile_id);

                    $videos = [];

                    foreach ($model as $key => $data) {                    

                        $videos[] = displayFullDetails($data->admin_video_id, $user ? $user->id : '', $user ? $user->user_type : '',$request->device_type ,$request->sub_profile_id);

                    }

                    $title = tr('mobile_suggestion_heading');

                    break;

                default:

                    Log::info("default Method");

                    $videos = [];

                    $title = "";

                    if (is_numeric($request->key)) {

                        $sub_category  = SubCategory::find($request->key);
                    
                        if ($sub_category) {

                            $title = $sub_category->name;

                            if ($sub_category->category) {

                                if ($sub_category->category->is_series) {

                                    Log::info("INSIDE CATEGORY");

                                    $genrenames = Genre::where('genres.sub_category_id' , $sub_category->id)
                                                    
                                                    ->leftJoin('admin_videos' , 'genres.id' , '=' , 'admin_videos.genre_id')
                                                    ->select(
                                                            'genres.id as genre_id',
                                                            'genres.name as genre_name',
                                                            'genres.image'
                                                            )
                                                    ->groupBy('admin_videos.genre_id')
                                                    ->havingRaw("COUNT(admin_videos.id) > 0")
                                                    ->orderBy('genres.updated_at', 'desc')
                                                    ->where('genres.is_approved', DEFAULT_TRUE)
                                                    ->get();

                                    foreach ($genrenames as $key => $genre) {

                                        $genres[] = ['genre_name'=>$genre->genre_name, 'genre_id'=>$genre->genre_id];

                                    }

                                    if (count($genres) > 0) {

                                        $seasons = AdminVideo::where('genre_id', $genres[0]['genre_id'])
                                                            // ->whereNotIn('admin_videos.genre_id', [$video->id])
                                                            ->where('admin_videos.status' , 1)
                                                            ->where('admin_videos.is_approved' , 1)
                                                            ->orderBy('admin_videos.created_at', 'desc')
                                                            ->skip(0)
                                                            ->take(Setting::get('admin_take_count'))
                                                            ->get();                                        
                                        if(!empty($seasons) && $seasons != null) {

                                            foreach ($seasons as $key => $value) {

                                                $videos[] = displayFullDetails($value->id, $user ? $user->id : '', $user ? $user->user_type : '',$request->device_type ,$request->sub_profile_id);
                                            }
                                        }
                                    }

                                } else {

                                    $sub_videos = sub_category_videos($request->key, null, $request->skip, $request->take);

                                    foreach ($sub_videos as $key => $val) {

                                        $video_detail = '';

                                        $videos[] = $video_detail = displayFullDetails($val->admin_video_id, $user ? $user->id : '', $user ? $user->user_type : '',$request->device_type ,$request->sub_profile_id);


                                        if(empty($title)) {

                                            if($video_detail) {

                                                $title = $video_detail['sub_category_name'];
                                                
                                            }
                                        }

                                    }
                                
                                }

                            } else {

                                $sub_videos = sub_category_videos($request->key, null, $request->skip, $request->take);

                                foreach ($sub_videos as $key => $val) {

                                    $video_detail = '';

                                    $videos[] = $video_detail = displayFullDetails($val->admin_video_id,$user ? $user->id : '', $user ? $user->user_type : '',$request->device_type , $request->sub_profile_id);


                                    if(empty($title)) {

                                        if($video_detail) {

                                            $title = $video_detail['sub_category_name'];
                                            
                                        }
                                    }

                                }
                            
                            }

                        } else {

                            $videos = [];
                  
                        }

                    }
                    
                    break;
            }


            $response_array = ['title'=> $title,'data' => $videos, 'success' => true, 'genres' => $genres];

            $response = response()->json($response_array, 200);

        }

        return $response;

    }

    /**
     * Function Name ; details()
     * 
     * @uses To get videos based on the key like wishlist, recent uploads, recommended & history (Web Usage)
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - key, skip, take and etc
     *
     * @return array of videos
     */

    public function details(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'key'=>'required',
                'skip' => 'required|numeric',
                'take'=> $request->has('take') ? 'required|numeric' : 'numeric',
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return response()->json(['success'=>false, 'error_messages'=>$error_messages]);
            
        } else {

            if (!$request->has('take')) {

                $request->request->add(['take' => Setting::get('admin_take_count')]);

            }

            $user = User::find($request->id);

            if (!$request->has('sub_profile_id')) {

                $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                if ($sub_profile) {

                    $request->request->add([ 

                        'sub_profile_id' => $sub_profile->id,

                    ]);

                    $id = $sub_profile->id;

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];


                    return response()->json($response_array , 200);

                }

            } else {

                $subProfile = SubProfile::where('user_id', $request->id)
                            ->where('id', $request->sub_profile_id)->first();

                if (!$subProfile) {

                    $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];
                    
                    return response()->json($response_array , 200);
                    
                } else {

                    $id = $subProfile->id;

                }

            } 

            $request->request->add(['sub_profile_id' => $request->sub_profile_id ?: $id]);

            switch ($request->key) {

                case WISHLIST:

                    $model = Helper::wishlist($request->id,$request->skip, $request->take, $request->sub_profile_id);

                    $chunk = $model->chunk(4);

                    $videos = [];

                    foreach ($chunk as $key => $value) {

                        $group = [];

                        foreach ($value as $key => $data) {
                         
                            $group[] = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);


                        }

                        $videos[] = $group;

                    }


                    $title = tr('mobile_wishlist_heading');

                    break;

                case TRENDING:

                    $model = Helper::trending(NULL, $request->skip, $request->take, $request->sub_profile_id);

                    $chunk = $model->chunk(4);

                    $videos = [];

                    foreach ($chunk as $key => $value) {

                        $group = [];

                        foreach ($value as $key => $data) {
                         
                            $group[] = displayFullDetails($data->id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                        }

                        $videos[] = $group;

                    }

                    $title = tr('mobile_trending_heading');

                    break;
                
                case KIDS:

                    $model = Helper::kids_videos(NULL, $request->skip, $request->take, $request->sub_profile_id);

                    $chunk = $model->chunk(4);

                    $videos = [];

                    foreach ($chunk as $key => $value) {

                        $group = [];

                        foreach ($value as $key => $data) {

                            $group[] = displayFullDetails($data->id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                        }

                        $videos[] = $group;

                    }

                    $title = tr('mobile_kids_heading');

                    break;

                case RECENTLY_ADDED:

                    $model = Helper::recently_added(NULL, $request->skip, $request->take, $request->sub_profile_id);

                    $chunk = $model->chunk(4);

                    $videos = [];

                    foreach ($chunk as $key => $value) {

                        $group = [];

                        foreach ($value as $key => $data) {
                         
                            $group[] = displayFullDetails($data->id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                        }

                        $videos[] = $group;

                    }

                    $title = tr('mobile_recent_upload_heading');

                    break;

                case WATCHLIST:

                    $model = Helper::watch_list($request->sub_profile_id, NULL, $request->skip, $request->take);

                    $chunk = $model->chunk(4);

                    $videos = [];

                    foreach ($chunk as $key => $value) {

                        $group = [];

                        foreach ($value as $key => $data) {
                         
                            $group[] = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                        }

                        $videos[] = $group;

                    }

                    $title = tr('mobile_watch_again_heading');

                    break;

                case SUGGESTIONS:

                    $model = Helper::suggestion_videos(NULL, $request->skip, null, $request->sub_profile_id);

                    $chunk = $model->chunk(4);

                    $videos = [];

                    foreach ($chunk as $key => $value) {

                        $group = [];

                        foreach ($value as $key => $data) {
                         
                            $group[] = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                        }

                        $videos[] = $group;

                    }

                    $title = tr('mobile_suggestion_heading');

                    break;
                
                case CONTINUE_WATCHING:

                    $model = Helper::continue_watching_videos($request->sub_profile_id,$request->device_type,$request->skip);

                    $chunk = $model->chunk(4);

                    $videos = [];

                    foreach ($chunk as $key => $value) {

                        $group = [];

                        foreach ($value as $key => $data) {
                         
                            $group[] = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                        }

                        $videos[] = $group;

                    }


                    $title = tr('continue_watching_videos');

                    break;

                default:

                    $videos = [];

                    $title = "";

                    if (is_numeric($request->key)){

                       $sub_videos = sub_category_videos($request->key, null, $request->skip, $request->take, $request->sub_profile_id);

                       // dd(count($sub_videos));

                       $chunk = $sub_videos->chunk(4);

                        foreach ($chunk as $key => $val) {

                            $group = [];

                            $video_detail = '';

                            foreach ($val as $key => $data) {
                             
                                $group[] = $video_detail = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                            }

                            if(empty($title)) {

                                if($video_detail) {


                                    $title = $video_detail['sub_category_name'];
                                    
                                }
                            }

                            $videos[] = $group;

                        }

                    }
                    
                    break;
            }


            $response_array = ['title'=> $title,'data'=>$videos, 'success'=>true];

            $response = response()->json($response_array, 200);
        }

        return $response;

    }

    /**
     * Function Name ; browse()
     * 
     * @uses Based on category id get all the sub category videos
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - key, skip, take and etc
     *
     * @return array of videos
     */

    public function browse(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'key'=>'required'
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return response()->json(['success'=>false, 'error_messages'=>$error_messages]);
            
        } else {

            $title = '';

            $videos = [];

            if ($request->key) {

                $videos = [];

                $catgory = Category::find($request->key);

                $user = User::find($request->id);

                if ($catgory) {

                    $sub_categories = get_sub_categories($request->key);

                    $sub_category = [];

                    foreach ($sub_categories as $key => $value) {

                       $sub_videos = sub_category_videos($value->id, WEB, 0 , 0 ,$request->sub_profile_id);

                       $chunk = $sub_videos->chunk(4);

                       $vid = [];

                        foreach ($chunk as $key => $val) {

                            $group = [];

                            foreach ($val as $key => $data) {
                             
                                $group[] = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $request->sub_profile_id);

                            }

                            $vid[] = $group;

                        }

                       $videos[$value->name] = $vid;

                       $sub_category[$value->name] = $value->id;


                    }   

                    $title = $catgory->name;

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>tr('category_not_found')];

                    return response()->json($response_array, 200);
                }

            } 

            $response_array = ['title'=> $title,'data'=>$videos, 'success'=>true, 'sub_category'=>$sub_category];

            $response = response()->json($response_array, 200);

        }

        return $response;

    }


    /**
     * Function Name ; getCategories()
     * 
     * @uses Get categories and split into chunks (6)
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - As of now no attribute
     *
     * @return array of array category
     */
    
    public function getCategories(Request $request) {


        Log::info("getCategories".print_r($request->all() , true));

        $categories = Category::where('categories.is_approved' , 1)
                    ->select('categories.id as id' , 'categories.name' , 'categories.picture' ,
                        'categories.is_series' ,'categories.status' , 'categories.is_approved')
                    ->leftJoin('admin_videos' , 'categories.id' , '=' , 'admin_videos.category_id')
                    ->where('admin_videos.status' , 1)
                    ->where('admin_videos.is_approved' , 1)
                    ->groupBy('admin_videos.category_id')
                    ->havingRaw("COUNT(admin_videos.id) > 0")
                    ->orderBy('name' , 'ASC')
                    ->get();

        if ($categories != null && !empty($categories)) {

            $model = ['success'=>true, 'data'=>$categories->chunk(6)];

        } else {

            $model = ['success'=>true, 'data'=>[]];

        }

        return response()->json($model);
   
    }

    /**
     * Function Name : activeProfiles()
     * 
     * @uses Based on user_id get all the sub profiles and Based on sub profile id get individual sub profile id
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - User ID, Sub Profile ID
     *
     * @return array of sub profiles / Single object sub profile
     */
    
    public function activeProfiles(Request $request) {

        $query = SubProfile::where('user_id', $request->id);

        if($request->sub_profile_id) {

            $query->whereNotIn('id', [$request->sub_profile_id]);

        }

        $model = $query->get();

        if ($model) {

            $no_of_account = $this->last_subscription($request)->getData();

            if ($no_of_account->data > count($model)) {

                if($request->device_type == DEVICE_ANDROID || $request->device_type == DEVICE_IOS) {

                    $items = [];

                    $plus = ['id'=>'add', 'picture'=>asset('images/plus.png'), "name"=>"Add Profile",'status'=>DEFAULT_FALSE];

                    foreach ($model as $key => $value) {
                       
                        array_push($items, $value);

                    }

                    array_push($items, $plus);

                    $model = $items;

                }

            }

            $response = ['success'=>true, 'data'=>$model];

        } else {

             $response = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];

        }

         return response()->json($response);

    }

    /**
     * Function Name : last_subscription()
     * 
     * @uses User Last subscription payment status
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - User ID
     *
     * @return response of subscription object or empty object
     */
    public function last_subscription(Request $request) {

        $model = UserPayment::where('user_id', $request->id)->orderBy('created_at', 'desc')->first();

        $response = ['success'=>true, 'data'=>($model) ? ($model->subscription ? $model->subscription->no_of_account : 1) : 1];

        return response()->json($response);

    }
 
    /**
     * Function Name : addProfile()
     * 
     * @uses Based on logged in user & Based on subscription user can add sub profile
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - User ID
     *
     * @return response of subscription object or empty object
     */
    public function addProfile(Request $request) {

       try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'id' => 'required',
                    'name'=>'required',
                    'picture' => 'mimes:jpeg,jpg,bmp,png',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                $response = ['success'=>false, 'message'=>$error_messages];

                throw new Exception($error_messages);

            } else {

                $UserPayment = UserPayment::where('user_id', $request->id)
                            ->orderBy('created_at', 'desc')
                            ->where('status', DEFAULT_TRUE)
                            ->first();

                if ($UserPayment) {

                    if ($UserPayment->subscription) {

                        if ($UserPayment->subscription->no_of_account <= ($UserPayment->user ? count($UserPayment->user->subProfile) : 0)) {

                            $response = ['success'=>false, 'error_messages'=>tr('account_exists')];

                            throw new Exception(tr('account_exists'));

                        }

                    }

                }

                $model = new SubProfile;

                $model->user_id = $request->id;

                $model->name = $request->name;

                $model->picture = asset('placeholder.png');

                if($request->hasFile('picture')) {

                    $model->picture = Helper::normal_upload_picture($request->file('picture'));

                } 

                $model->status = DEFAULT_FALSE;

                if($model->save()) {

                    $user = User::find($request->id);

                    $user->no_of_account += 1;

                    $user->save();

                } else {

                    throw new Exception(tr('sub_profile_not_save'));
                    
                }

                $response = ['success'=>true, 'data'=>$model, 'message'=>tr('add_profile_success')];
            }

            DB::commit();

            return response()->json($response);

        } catch (Exception $e) {

            DB::rollback();

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);

        }

    }

    /**
     * Function Name : edit_sub_profile()
     * 
     * @uses Based on logged in user , Edit sub profiles using sub profile id
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - User ID, name & sub profile id
     *
     * @return response of sub profile
     */
    public function edit_sub_profile(Request $request) {

        Log::info("edit_sub_profile".print_r($request->all(), true));

        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'id' => 'required',
                    'name'=>'required',
                    'sub_profile_id'=>'required',
                    'picture' => 'mimes:jpeg,jpg,bmp,png',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                $response = ['success'=>false, 'message'=>$error_messages];

                throw new Exception($error_messages);

            } else {

                $model = SubProfile::find($request->sub_profile_id);

                $model->name = $request->name;

                if($request->hasFile('picture')) {

                    Helper::delete_picture($model->picture, "/uploads/"); // Delete the old pic

                    $model->picture = Helper::normal_upload_picture($request->file('picture'));
                } 

                //$model->status = DEF;

                $model->save();

                $response = ['success'=>true, 'data'=>$model, 'message'=>tr('edit_profile_success')];
            }

            DB::commit();

            return response()->json($response);

        } catch (Exception $e) {

            DB::rollback();

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);

        }

    }

    /**
     * Function Name : view_sub_profile()
     * 
     * @uses Based on logged in user , View sub profiles using sub profile id
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - Sub profile id
     *
     * @return response of sub profile
     */
   
    public function view_sub_profile(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'sub_profile_id'=>'required'
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response = ['success'=>false, 'message'=>$error_messages];

        } else {

            $model = SubProfile::find($request->sub_profile_id);

            if ($model) {

                $response = ['success'=>true, 'data'=>$model];

            } else {

                $response = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];

            }
 
        }

        return response()->json($response);

    }

    /**
     * Function Name : delete_sub_profile()
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
    public function delete_sub_profile(Request $request) {

        try {

            DB::beginTransaction();

            $user = User::find($request->id);

            if ($user) {

                if (count($user->subProfile) == 1) {

                    throw new Exception(tr('sub_profile_not_delete'));
                    
                }

            } else {


                throw new Exception(tr('no_user_detail_found'));
                
            }

            $validator = Validator::make(
                $request->all(),
                array(
                    'sub_profile_id'=>'required'
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());


                throw new Exception($error_messages);

            } else {

                // This variable used to set next available sub profile as current profile

                $another_model_sub_profile_id = 0;

                $model = SubProfile::find($request->sub_profile_id);

                if ($model) {

                    $model_status = $model->status;

                    $model->delete();

                    $another_model = SubProfile::where('user_id', $request->id)->first();

                    if ($model_status == 1) {

                        if(count($another_model) > 0) {

                            $another_model->status = DEFAULT_TRUE;

                            $another_model->save();

                        }

                    }

                    $another_model_sub_profile_id = $another_model->id;
                    
                    $user->no_of_account -= 1;

                    if ($user->save()) {

                        /* $logged = UserLoggedDevice::where('user_id', $request->id)->first();

                        if ($logged) {

                            if ($logged->delete()) {

                                $user->logged_in_account -= 1;

                                $user->save();

                                $response = ['success'=>true, 'data' => $user ,  'sub_profile_id' => $another_model_sub_profile_id];

                            } else {

                                throw new Exception(tr('logged_in_device_not_delete'));
                                
                            }

                        } else {

                            $response = ['success'=>true , 'another_model_sub_profile_id' => $another_model_sub_profile_id, 'message'=>tr('delete_profile_success')];

                        } */

                        $response = ['success'=>true , 'another_model_sub_profile_id' => $another_model_sub_profile_id];


                    } else {

                        throw new Exception(tr('user_details_not_save'));
                        
                    }

                } else {

                    throw new Exception(tr('sub_profile_details_not_found'));
                    
                }
     
            }

            DB::commit();

            return response()->json($response);

        } catch(Exception $e) {

            DB::rollback();

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);

        }

    }

    /**
     * Function Name : active_plan()
     * 
     * @uses Based on logged in user , Get Active plan 
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - User Id 
     *
     * @return response of boolean with subscription details
     */
    public function active_plan(Request $request){

        $currency = Setting::get('currency');

        $model = UserPayment::select('user_payments.*', DB::raw("'$currency' as currency"))->where('user_id', $request->id)
                ->where('status', DEFAULT_TRUE)
                ->orderBy('created_at', 'desc')->first();

        if ($model) {

            $model->expiry_date = date('d-m-Y h:i A', strtotime($model->expiry_date));

            $response = ['success'=>true, 'data'=>$model, 'subscription' => $model->subscription];

        } else {

            $response = ['success'=>false, 'error_messages'=>tr('user_payment_not_found')];

        }

        return response()->json($response);

    }

    /**
     * Function Name : subscribed_plans()
     * 
     * @uses Based on logged in user , get his/her subscribed plans
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - User Id 
     *
     * @return response of boolean with plan details
     */

    public function subscribed_plans(Request $request){

        $model = UserPayment::where('user_id', $request->id)
                    ->where('status', DEFAULT_TRUE)
                    ->orderBy('created_at', 'desc')
                    ->get();

        $user = User::find($request->id);

        $plans = [];

        $amount = 0;

        if ($user) {

            if (!empty($model) && $model != null) {

                foreach ($model as $key => $value) {

                    $amount += $value->amount;
                    
                    $plans[] = [

                        'payment_id'=>$value->payment_id,

                        'plan_name'=>$value->subscription ? $value->subscription->title : '',

                        'no_of_month'=>$value->subscription ? $value->subscription->plan : '',

                        'no_of_account'=>$value->subscription ? $value->subscription->no_of_account : '',

                        'amount'=> $value->amount,

                        'expiry_date'=>date('d-m-Y h:i A', strtotime($value->expiry_date)),

                        'date'=>convertTimeToUSERzone($value->created_at, $user->timezone, 'd-m-Y'),

                        'currency'=>Setting::get('currency'),

                        'total_amount'=>$value->subscription_amount,

                        'coupon_amount'=>$value->coupon_amount,

                        'active_plan'=> strtotime($value->expiry_date) >= strtotime('Y-m-d') ? DEFAULT_TRUE : DEFAULT_FALSE,

                        'cancelled_status'=> $value->is_cancelled,

                        'coupon_code'=>$value->coupon_code,

                        'payment_mode'=>$value->payment_mode,

                    ];

                }

            }

            $response = ['success'=>true, 'plans'=>$plans, 'amount'=>$amount, 'currency'=>Setting::get('currency')];

        } else {

            $response = ['success'=>false, 'error_messages'=>tr('no_user_detail_found')];

        }

        return response()->json($response);

    }

    /**
     * Function Name : subscription_index()
     * 
     * @uses Get all subscription plans
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @return response of plan details
     */
    public function subscription_index(Request $request) {
        
	   $currency = Setting::get('currency');

        $query = Subscription::select('id',
                'title', 'description', 'plan','amount', 'no_of_account',
                'status', 'popular_status','created_at' , DB::raw("'$currency' as currency"))->where('status' , 1);

         if ($request->id) {

            $user = User::find($request->id);

            if ($user) {

               if ($user->one_time_subscription == DEFAULT_TRUE) {

                   $query->where('amount','>', 0);

               }

            } 

        }

        $model = $query->get();

        $model = (!empty($model) && $model != null) ? $model : [];

        $response_array = ['success'=>true, 'data'=>$model];

        return response()->json($response_array,200);
    
    }

    /**
     * Function Name : zero_plan()
     * 
     * @uses Save zero plan details based on logged in user (Only one time he can avail)
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param object $request - plan id, user iid and etc
     *
     * @return response of plan details
     */

    public function zero_plan(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'plan_id'=>'required:exists:subscriptions,id',
                    'coupon_code'=>'exists:coupons,coupon_code'
                ),
                 array(
                    'exists' => 'The :attribute doesn\'t exists',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                $response = ['success'=>false, 'message'=>$error_messages];

                throw new Exception($error_messages);

            } else {

                if ($request->plan_id) {

                    // Load model
                    $plan = Subscription::find($request->plan_id);

                    $user = User::find($request->id);

                    $total = $plan->amount;

                    $coupon_amount = 0;

                    $coupon_reason = '';

                    $is_coupon_applied = COUPON_NOT_APPLIED;

                    if ($request->coupon_code) {

                        $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                        if ($coupon) {

                            if ($coupon->status == COUPON_INACTIVE) {

                                $coupon_reason = tr('coupon_code_declined');

                            } else {

                                $check_coupon = $this->check_coupon_applicable_to_user($user, $coupon)->getData();

                                if ($check_coupon->success) {

                                    $is_coupon_applied = COUPON_APPLIED;

                                    if ($coupon->amount_type == PERCENTAGE) {

                                        $coupon->amount = amount_convertion($coupon->amount, $plan->amount);

                                    }

                                    if ($coupon->amount < $plan->amount) {

                                        $total = $plan->amount - $coupon->amount;

                                    } else {

                                        $total = 0;
                                        
                                    }

                                    $coupon_amount = $coupon->amount;


                                    // Create user applied coupon

                                    if($check_coupon->code == 2002) {

                                        $user_coupon = UserCoupon::where('user_id', $user->id)
                                                ->where('coupon_code', $request->coupon_code)
                                                ->first();

                                        // If user coupon not exists, create a new row

                                        if ($user_coupon) {

                                            if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                                $user_coupon->no_of_times_used += 1;

                                                $user_coupon->save();

                                            }

                                        }

                                    } else {

                                        $user_coupon = new UserCoupon;

                                        $user_coupon->user_id = $user->id;

                                        $user_coupon->coupon_code = $request->coupon_code;

                                        $user_coupon->no_of_times_used = 1;

                                        $user_coupon->save();

                                    }


                                } else {

                                    $coupon_reason = $check_coupon->error_messages;

                                }

                            }

                        }

                    }

                    if ($plan->amount <= 0 || $total <= 0) {

                        // save video payment for onetime

                        $model = new UserPayment;

                        $previous_payment = UserPayment::where('user_id' , $request->user_id)->where('status', DEFAULT_TRUE)->orderBy('id', 'desc')->first();

                        if ($previous_payment) {

                            if (strtotime($previous_payment->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

                             $model->expiry_date = date('Y-m-d H:i:s', strtotime("+{$plan->plan} months", strtotime($previous_payment->expiry_date)));

                            } else {

                                $model->expiry_date = date('Y-m-d H:i:s',strtotime("+{$plan->plan} months"));

                            }

                        } else {
                            $model->expiry_date = date('Y-m-d H:i:s',strtotime("+{$plan->plan} months"));
                        }

                    
                        $model->subscription_id = $request->plan_id;

                        $model->user_id = $request->id;

                        $model->payment_id =  $request->coupon_code ? ($total == 0 ? 'COUPON-DISCOUNT' : "Free Plan") : 'Free plan';

                        $model->amount = $total;

                        $model->status =  PAID_STATUS;

                        $model->is_coupon_applied = $is_coupon_applied;

                        $model->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';

                        $model->coupon_amount = $coupon_amount;

                        $model->subscription_amount = $plan->amount;

                        $model->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

                        $model->payment_mode = "-";

                        $model->save();

                        if ($model) {

                            if ($user) {

                                $user->user_type = DEFAULT_TRUE;

                                $user->one_time_subscription = DEFAULT_TRUE;

                                $user->amount_paid += 0;

                                $user->expiry_date = $model->expiry_date;

                                $user->no_of_days = 0;

                                $user->save();

                                $response_array = ['success' => true , 'model' => $model, 'plan'=>$plan, 'user'=>['token'=>$user->token],
                                'message'=>tr('success_subscription')];

                            } else {

                                throw new Exception(tr('no_user_detail_found'));
                                
                            }

                        } else {

                            $response_array = ['success' => false , 'error_messages' => Helper::error_message(146) , 'error_code' => 146];

                            throw new Exception(Helper::error_message(146));

                        }

                    } else {

                        throw new Exception(tr('zero_plan_not_found'));
                        
                    }

                } else {

                    $response_array = ['success' => false , 'error_messages' => Helper::error_message(146) , 'error_code' => 146];

                    throw new Exception(Helper::error_message(146), 146);

                }

            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);
        }

    }

    /**
     * Function Name : site_settings()
     * 
     * @uses Get all the settings table values (Key & Value)
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @return response of settings details
     */
    public function site_settings() {

        $settings = Settings::get();

        return response()->json($settings, 200); 
    }

    /**
     * Function Name : allPages()
     * 
     * @uses List out all the static pages like abotu us, terms , privacy & policy and etc
     *
     * @created : vidhya R
     *
     * @edited : 
     *
     * @return response of page details with chunks
     */

    public function allPages() {

        $all_pages = Page::all();

        $chunks = $all_pages->chunk(4);

        $chunks->toArray();

        return response()->json($chunks, 200);

    }

    /**
     * Function Name : getPage()
     * 
     * @uses Get Page Based on type
     * 
     * @created : vidhya R
     *
     * @edited : 
     *
     * @param string $id - Page Id
     *
     * @return response of page details
     */
    public function getPage($id) {

        $page = Page::where('id', $id)->first();

        return response()->json($page, 200);

    }

    /**
     * Function Name : check_social()
     * 
     * @uses Check whether the social login buttons need to display or not
     * 
     * @created : 
     *
     * @edited : 
     *
     * @return response of page details
     */
    public function check_social() {

        $facebook_client_id = envfile('FB_CLIENT_ID');
        $facebook_client_secret = envfile('FB_CLIENT_SECRET');
        $facebook_call_back = envfile('FB_CALL_BACK');

        $google_client_id = envfile('GOOGLE_CLIENT_ID');
        $google_client_secret = envfile('GOOGLE_CLIENT_SECRET');
        $google_call_back = envfile('GOOGLE_CALL_BACK');

        $fb_status = false;

        if (!empty($facebook_client_id) && !empty($facebook_client_secret) && !empty($facebook_call_back)) {

            $fb_status = true;

        }

        $google_status = false;

        if (!empty($google_client_id) && !empty($google_client_secret) && !empty($google_call_back)) {

            $google_status = true;

        }

        return response()->json(['fb_status'=>$fb_status, 'google_status'=>$google_status]);
    }

    /**
     * Function Name : genre_video()
     * 
     * @uses Get Genre Video Details based on genre id
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - Genre id
     * 
     * @return response of Genre video details
     */
    public function genre_video(Request $request) {

        Log::info("genre_video".print_r($request->all() , true));

        $model = Genre::find($request->genre_id);

        if ($model) {

            $ios_video = $model->video;

            if(check_valid_url($model->video)) {

                if(Setting::get('streaming_url'))
                    $model->video = Setting::get('streaming_url').get_video_end($model->video);

                if(Setting::get('HLS_STREAMING_URL'))
                    $ios_video = Setting::get('HLS_STREAMING_URL').get_video_end($model->video);
            }

            $response_array = ['success' => true , 'model' => $model, 'ios_video'=>$ios_video];

        } else {

            $response_array = ['success' => false , tr('genre_not_found')];
        }

        return response()->json($response_array);

    }

    /**
     * Function Name : genre_list()
     * 
     * @uses Get Genre list based on genre id
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - Genre id
     * 
     * @return response of Genre video details with html repsonse
     */
    public function genre_list(Request $request) {

        $user_details = User::find($request->id);

        $seasons = $genre_videos = AdminVideo::where('genre_id', $request->genre_id)
                            // ->whereNotIn('admin_videos.genre_id', [$video->id])
                            ->where('admin_videos.status' , 1)
                            ->where('admin_videos.is_approved' , 1)
                            ->orderBy('admin_videos.created_at', 'desc')
                            // ->skip(0)
                            // ->take(4)
                            ->get();

        foreach ($genre_videos as $key => $value) {
            # code...

            $ppv_status = VideoRepo::pay_per_views_status_check($request->id ?: "", $user_details ? $user_details->user_type : '', $value)->getData();

            $value->pay_per_view_status = $ppv_status->success;
        }

        $view = \View::make('admin.seasons.season_videos')->with('genre_videos', $genre_videos)->render();

        $response_array = ['success' => true , 'data' => $genre_videos ? $view : tr('no_genre')];

        return response()->json($response_array);

    }

    /**
     * Function Name : searchAll()
     * 
     * @uses Search videos based on title
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - Term (Search key)
     * 
     * @return response of searched videos
     */
    public function searchAll(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'id'=>'required',
                'term' => 'required',
              //  'sub_profile_id'=>'required',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );
    
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

            return response()->json($response_array);
        
        } else {

            $q = $request->term;

            if (!$request->sub_profile_id) {

                $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                if ($sub_profile) {

                    $request->request->add([ 

                        'sub_profile_id' => $sub_profile->id,

                    ]);

                    $id = $sub_profile->id;

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];

                    return response()->json($response_array , 200);

                }
            } else {

                $id = $request->sub_profile_id;
            }


            \Session::set('user_search_key' , $q);

            $items = array();
            
            $results = Helper::search_video($q, 1, 0, $id);

            if($results) {

                foreach ($results as $i => $key) {

                    $check = $i+1;

                    if($check <=10) {
     
                        array_push($items,$key->title);

                    } if($check == 10 ) {

                        array_push($items,"View All" );
                    }
                
                }

            }

            return response()->json($items);
        }     
    
    }

    /**
     * Function Name : notifications()
     * 
     * @uses Display New uploaded videos notification 
     *
     * @created : Shobana
     *
     * @edited : -
     *
     * @param object $request - user id
     * 
     * @return response of searched videos
     */
    public function notifications(Request $request) {

        $count = Notification::where('status', 0)->where('user_id', $request->id)->count();

        $take_count = $request->take ? $request->take : Setting::get('admin_take_count');

        $model = Notification::where('notifications.user_id', $request->id)
                ->select('admin_videos.default_image', 'notifications.admin_video_id', 'admin_videos.title', 'notifications.updated_at', 'admin_videos.status', 'admin_videos.id')
                ->leftJoin('admin_videos', 'admin_videos.id', '=', 'notifications.admin_video_id')
                ->leftJoin('categories', 'categories.id', '=', 'admin_videos.category_id')
                ->leftJoin('sub_categories', 'categories.id', '=', 'sub_categories.category_id')
                ->where('admin_videos.status', 1)
                ->where('admin_videos.is_approved', 1)
                ->where('admin_videos.is_approved', 1)
                ->where('categories.is_approved', 1)
                ->where('sub_categories.is_approved', 1)
                ->skip($request->skip)
                ->take($take_count)
                ->orderBy('notifications.updated_at', 'desc')->get();

        $datas = [];

        $user = User::find($request->id);

        if (!empty($model) && $model != null) {

            foreach ($model as $key => $value) {

                $ppv_status = VideoRepo::pay_per_views_status_check($request->id, $user ? $user->user_type : '', $value->adminVideo)->getData();

                $is_spam = check_flag_video($value->admin_video_id, $request->sub_profile_id);

                $datas[] = ['admin_video_id'=>$value->admin_video_id, 
                            'img'=>$value->default_image, 
                            'title'=>$value->title, 
                            'is_spam'=> $is_spam, 
                            'time'=>$value->updated_at->diffForHumans(),
                            'pay_per_view_status'=>$ppv_status->success,
                            'ppv_details'=>$ppv_status];

            }
        }

        if($request->device_type != DEVICE_WEB) {

            Notification::where('status', 0)->where('user_id', $request->id)->update(['status'=>DEFAULT_TRUE]);

        }

        $response_array = ['success'=>true, 'count'=>$count, 'data'=>$datas];

        return response()->json($response_array);

    }


    /**
     * Function Name : notifications_count()
     * 
     * @uses Get the notification count
     *
     * @created : Shobana
     *
     * @edited :  -
     *
     * @param object $request - As of no attribute
     * 
     * @return response of boolean
     */
    public function notifications_count(Request $request) {
            
        $count = Notification::where('status', 0)->where('user_id', $request->id)->count();

        $response_array = ['success'=>true, 'count'=>$count];

        return response()->json($response_array);

    }

    /**
     * Function Name : red_notifications()
     * 
     * @uses Once click in bell all the notification status will change into read 
     *
     * @created : Shobana
     *
     * @edited : -
     *
     * @param object $request - As of no attribute
     * 
     * @return response of boolean
     */
    public function red_notifications(Request $request) {

        $model = Notification::where('status', 0)->where('user_id', $request->id)->get();

        foreach ($model as $key => $value) {

            $value->status = 1;

            $value->save();

        }

        return response()->json(true);

    }

    /**
     * Function Name : stripe_payment()
     * 
     * @uses User pay the subscription plan amount through stripe payment
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - User id, Subscription id
     * 
     * @return response of success/failure message
     */
    public function stripe_payment(Request $request) {
        
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), 
                array(
                    'subscription_id' => 'required|exists:subscriptions,id',
                    'coupon_code'=>'exists:coupons,coupon_code',

                ),  array(

                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'subscription_id.exists' => tr('subscription_not_exists'),

                ));

            if($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages, 101);

            } else {

                $subscription = Subscription::find($request->subscription_id);

                $user = User::find($request->id);

                if ($subscription) {

                    $total = $subscription->amount;

                    $coupon_amount = 0;

                    $coupon_reason = '';

                    $is_coupon_applied = COUPON_NOT_APPLIED;

                    if ($request->coupon_code) {

                        $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                        if ($coupon) {

                            if ($coupon->status == COUPON_INACTIVE) {

                                $coupon_reason = tr('coupon_inactive_reason');

                            } else {

                                $check_coupon = $this->check_coupon_applicable_to_user($user, $coupon)->getData();

                                if ($check_coupon->success) {

                                    $is_coupon_applied = COUPON_APPLIED;

                                    $amount_convertion = $coupon->amount;

                                    if ($coupon->amount_type == PERCENTAGE) {

                                        $amount_convertion = amount_convertion($coupon->amount, $subscription->amount);

                                    }

                                    // If the subscription amount less than coupon amount , then substract the amount.

                                    if ($amount_convertion < $subscription->amount) {

                                        $total = $subscription->amount - $amount_convertion;

                                        $coupon_amount = $amount_convertion;

                                    } else {

                                        // If the coupon amount greater than subscription amount, then assign to zero.

                                        // throw new Exception(Helper::get_error_message1(156),156);

                                        $total = 0;

                                        $coupon_amount = $amount_convertion;
                                        
                                    }

                                    if($check_coupon->code == 2002) {

                                        $user_coupon = UserCoupon::where('user_id', $user->id)
                                                ->where('coupon_code', $request->coupon_code)
                                                ->first();

                                        // If user coupon not exists, create a new row

                                        if ($user_coupon) {

                                            if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                                $user_coupon->no_of_times_used += 1;

                                                $user_coupon->save();

                                            }

                                        }

                                    } else {

                                        $user_coupon = new UserCoupon;

                                        $user_coupon->user_id = $user->id;

                                        $user_coupon->coupon_code = $request->coupon_code;

                                        $user_coupon->no_of_times_used = 1;

                                        $user_coupon->save();

                                    }


                                } else {

                                    $coupon_reason = $check_coupon->error_messages;
                                    
                                }

                            }

                        } else {

                            $coupon_reason = tr('coupon_delete_reason');
                        }

                    }

                    if ($user) {

                        $check_card_exists = User::where('users.id' , $request->id)
                                        ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                        ->where('cards.id' , $user->card_id)
                                        ->where('cards.is_default' , DEFAULT_TRUE);

                        if($check_card_exists->count() != 0) {

                            $user_card = $check_card_exists->first();

                            if ($total <= 0) {

                                
                                $previous_payment = UserPayment::where('user_id' , $request->id)
                                            ->where('status', DEFAULT_TRUE)->orderBy('created_at', 'desc')->first();


                                $user_payment = new UserPayment;

                                if($previous_payment) {

                                    if (strtotime($previous_payment->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

                                     $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$subscription->plan} months", strtotime($previous_payment->expiry_date)));

                                    } else {

                                        $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));

                                    }


                                } else {
                                   
                                    $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$subscription->plan." months"));
                                }



                                $user_payment->payment_id = ($request->coupon_code) ? 'COUPON-DISCOUNT' : "free plan";

                                $user_payment->user_id = $request->id;

                                $user_payment->subscription_id = $request->subscription_id;

                                $user_payment->status = PAID_STATUS;
	
	                            $user_payment->payment_mode = CARD;

                                // Coupon details

                                $user_payment->is_coupon_applied = $is_coupon_applied;

                                $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';

                                $user_payment->coupon_amount = $coupon_amount;

                                $user_payment->subscription_amount = $subscription->amount;

                                $user_payment->amount = $total;

                                $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

                                if ($user_payment->save()) {

                                    $user->one_time_subscription = 1;

                                    $user->user_type = 1;

                                    $user->save();
                                    
                                    $data = ['id' => $user->id , 'token' => $user->token, 'no_of_account'=>$subscription->no_of_account , 'payment_id' => $user_payment->payment_id];

                                    $response_array = ['success' => true, 'message'=>tr('payment_success') , 'data' => $data];

                                } else {

                                    throw new Exception(tr(Helper::get_error_message(902)), 902);

                                }


                            } else {

                                $stripe_secret_key = Setting::get('stripe_secret_key');

                                $customer_id = $user_card->customer_id;

                                if($stripe_secret_key) {

                                    \Stripe\Stripe::setApiKey($stripe_secret_key);

                                } else {

                                    throw new Exception(Helper::get_error_message(902), 902);

                                }

                                try {

                                    $total = number_format((float)$total, 2, '.', '');

                                   $user_charge =  \Stripe\Charge::create(array(
                                      "amount" => $total * 100,
                                      "currency" => "usd",
                                      "customer" => $customer_id,
                                    ));

                                   $payment_id = $user_charge->id;
                                   $amount = $user_charge->amount/100;
                                   $paid_status = $user_charge->paid;

                                    if($paid_status) {

                                        $previous_payment = UserPayment::where('user_id' , $request->id)
                                            ->where('status', DEFAULT_TRUE)->orderBy('created_at', 'desc')->first();

                                        $user_payment = new UserPayment;

                                        if($previous_payment) {

                                            $expiry_date = $previous_payment->expiry_date;
                                            $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime($expiry_date. "+".$subscription->plan." months"));

                                        } else {
                                            
                                            $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$subscription->plan." months"));
                                        }


                                        $user_payment->payment_id  = $payment_id;

                                        $user_payment->user_id = $request->id;

                                        $user_payment->subscription_id = $request->subscription_id;

                                        $user_payment->status = PAID_STATUS;

                                        $user_payment->payment_mode = CARD;

                                        // Coupon details

                                        $user_payment->is_coupon_applied = $is_coupon_applied;

                                        $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';

                                        $user_payment->coupon_amount = $coupon_amount;

                                        $user_payment->subscription_amount = $subscription->amount;

                                        $user_payment->amount = $total;

                                        $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;


                                        if ($user_payment->save()) {

                                            $user->user_type = 1;

                                            $user->save();
                                            
                                            $data = ['id' => $user->id , 'token' => $user->token, 'no_of_account'=>$subscription->no_of_account , 'payment_id' => $user_payment->payment_id];

                                            $response_array = ['success' => true, 'message'=>tr('payment_success') , 'data' => $data];

                                        } else {

                                             throw new Exception(tr(Helper::get_error_message(902)), 902);

                                        }


                                    } else {

                                        $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(903) , 'error_code' => 903);

                                        throw new Exception(Helper::get_error_message(903), 903);

                                    }

                                } catch(\Stripe\Error\RateLimit $e) {

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);


                                } catch(\Stripe\Error\Card $e) {

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (\Stripe\Error\InvalidRequest $e) {
                                    // Invalid parameters were supplied to Stripe's API
                                   
                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (\Stripe\Error\Authentication $e) {

                                    // Authentication with Stripe's API failed

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (\Stripe\Error\ApiConnection $e) {

                                    // Network communication with Stripe failed

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (\Stripe\Error\Base $e) {
                                  // Display a very generic error to the user, and maybe send
                                    
                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (Exception $e) {
                                    // Something else happened, completely unrelated to Stripe

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);
                                }

                            }

                        } else {
     
                            throw new Exception(Helper::get_error_message(901), 901);
                            
                        }

                    } else {

                        throw new Exception(tr('no_user_detail_found'));
                        
                    }

                } else {

                    throw new Exception(Helper::get_error_message(901), 901);

                }         

                
            }

            DB::commit();

            return response()->json($response_array , 200);

        } catch (Exception $e) {

            DB::rollback();

            $error = $e->getMessage();

            $code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$error, 'error_code'=>$code];

            return response()->json($response_array);
        }
    
    }

    /**
     * Function Name : ppv_end()
     * 
     * @uses Once video end (complete) at the time this api will ping and change pay per view status as one.
     * 
     *  --- Status 1 - The user is completely watched the video
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - Admin video id
     * 
     * @return response of success/failure message
     */
    public function ppv_end(Request $request) {

        $validator = Validator::make($request->all(), 
            array(
                'admin_video_id' => 'required|exists:admin_videos,id',
            ),  array(
                'exists' => 'The :attribute doesn\'t exists',
            ));

        if($validator->fails()) {

            $errors = implode(',', $validator->messages()->all());
            
            $response_array = ['success' => false, 'error_messages' => $errors, 'error_code' => 101];

            return response()->json($response_array);

        } else {

            // Load Payperview
            $payperview = PayPerView::where('user_id', $request->id)
                            ->where('video_id',$request->admin_video_id)
                            ->where('status',DEFAULT_TRUE)
                            ->orderBy('id', 'desc')
                            ->where('is_watched', DEFAULT_FALSE)
                            ->first();

            if ($payperview) {

                $payperview->is_watched = DEFAULT_TRUE;

                $payperview->save();

            }

            $response_array = ['success'=>true];

            return response()->json($response_array);

        }

    }

    /**
     * Function Name : stripe_ppv()
     * 
     * @uses Pay the payment for Pay per view through stripe
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - Admin video id
     * 
     * @return response of success/failure message
     */
    public function stripe_ppv(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), 
                array(
                    'admin_video_id' => 'required|exists:admin_videos,id',
                    'coupon_code'=>'exists:coupons,coupon_code,status,1',
                ),  array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'admin_video_id.exists' => tr('video_not_exists'),
                ));

            if($validator->fails()) {

                $errors = implode(',', $validator->messages()->all());
                
                $response_array = ['success' => false, 'error_messages' => $errors, 'error_code' => 101];

                throw new Exception($errors);

            } else {

                $userModel = User::find($request->id);

                if ($userModel) {

                    if ($userModel->card_id) {

                        $user_card = Card::find($userModel->card_id);

                        if ($user_card && $user_card->is_default) {

                            $video = AdminVideo::find($request->admin_video_id);

                            if($video) {

                                
                                $total = $video->amount;

                                $coupon_amount = 0;

                                $coupon_reason = '';

                                $is_coupon_applied = COUPON_NOT_APPLIED;

	                        
				                $coupon_amount = 0; 

                                if ($request->coupon_code) {

                                    $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                                    if ($coupon) {

                                        if ($coupon->status == COUPON_INACTIVE) {

                                            $coupon_reason = tr('coupon_inactive_reason');

                                        } else {

                                            $check_coupon = $this->check_coupon_applicable_to_user($userModel, $coupon)->getData();

                                            if ($check_coupon->success) {

                                                $is_coupon_applied = COUPON_APPLIED;

                                                $amount_convertion = $coupon->amount;

                                                if ($coupon->amount_type == PERCENTAGE) {

                                                    $amount_convertion = amount_convertion($coupon->amount, $video->amount);

                                                }

                                                if ($amount_convertion < $video->amount) {

                                                    $total = $video->amount - $amount_convertion;

                                                    $coupon_amount = $amount_convertion;

                                                } else {

                                                    $total = 0;

                                                    $coupon_amount = $amount_convertion;
                                                    
                                                }

                                                 // Create user applied coupon

                                                if($check_coupon->code == 2002) {

                                                    $user_coupon = UserCoupon::where('user_id', $userModel->id)
                                                            ->where('coupon_code', $request->coupon_code)
                                                            ->first();

                                                    // If user coupon not exists, create a new row

                                                    if ($user_coupon) {

                                                        if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                                            $user_coupon->no_of_times_used += 1;

                                                            $user_coupon->save();

                                                        }

                                                    }

                                                } else {

                                                    $user_coupon = new UserCoupon;

                                                    $user_coupon->user_id = $userModel->id;

                                                    $user_coupon->coupon_code = $request->coupon_code;

                                                    $user_coupon->no_of_times_used = 1;

                                                    $user_coupon->save();

                                                } 

                                            } else {

                                                $coupon_reason = $check_coupon->error_messages;
                                                
                                            }

                                        }

                                    } else {

                                        $coupon_reason = tr('coupon_delete_reason');
                                    }

                                }


                                if ($total <= 0) {

                                    $user_payment = new PayPerView;
                                    $user_payment->payment_id  =  $is_coupon_applied ? 'COUPON-DISCOUNT' : tr('no_ppv');
                                    $user_payment->user_id = $request->id;
                                    $user_payment->video_id = $request->admin_video_id;
                                    $user_payment->status = PAID_STATUS;
                                    $user_payment->is_watched = NOT_YET_WATCHED;
                                    $user_payment->paid_date = date('Y-m-d');


                                    if ($video->type_of_user == NORMAL_USER) {

                                        $user_payment->type_of_user = tr('normal_users');

                                    } else if($video->type_of_user == PAID_USER) {

                                        $user_payment->type_of_user = tr('paid_users');

                                    } else if($video->type_of_user == BOTH_USERS) {

                                        $user_payment->type_of_user = tr('both_users');
                                    }


                                    if ($video->type_of_subscription == ONE_TIME_PAYMENT) {

                                        $user_payment->type_of_subscription = tr('one_time_payment');

                                    } else if($video->type_of_subscription == RECURRING_PAYMENT) {

                                        $user_payment->type_of_subscription = tr('recurring_payment');

                                    }

                                    $user_payment->payment_mode = CARD;

                                    // Coupon details

                                    $user_payment->is_coupon_applied = $is_coupon_applied;

                                    $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                                    $user_payment->coupon_amount = $coupon_amount;

                                    $user_payment->ppv_amount = $video->amount;

                                    $user_payment->amount = $total;

                                    $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

                                    $user_payment->save();

                                    // Commission Spilit 
                                    // if(is_numeric($video->uploaded_by)) {

                                        if($user_payment->amount > 0) { 

                                            // Do Commission spilit  and redeems for moderator

                                            Log::info("ppv_commission_spilit started");

                                            PaymentRepo::ppv_commission_split($video->id , $user_payment->id , $video->uploaded_by);

                                            Log::info("ppv_commission_spilit END"); 
                                            
                                        }

                                        

                                        \Log::info("ADD History - add_to_redeem");

                                    // } 


                                    $data = ['id'=> $request->id, 'token'=> $userModel->token , 'payment_id' => $user_payment->payment_id];

                                    $response_array = array('success' => true, 'message'=>tr('payment_success'),'data'=> $data);

                                } else {

                                    // Get the key from settings table

                                    $stripe_secret_key = Setting::get('stripe_secret_key');

                                    $customer_id = $user_card->customer_id;
                                    
                                    if($stripe_secret_key) {

                                        \Stripe\Stripe::setApiKey($stripe_secret_key);

                                    } else {

                                        $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(902) , 'error_code' => 902);

                                        throw new Exception(Helper::get_error_message(902));
                                        
                                    }

                                try {

                                       $user_charge =  \Stripe\Charge::create(array(
                                          "amount" => $total * 100,
                                          "currency" => "usd",
                                          "customer" => $customer_id,
                                        ));

                                       $payment_id = $user_charge->id;
                                       $amount = $user_charge->amount/100;
                                       $paid_status = $user_charge->paid;
                                       
                                       if($paid_status) {

                                            $user_payment = new PayPerView;
                                            $user_payment->payment_id  = $payment_id;
                                            $user_payment->user_id = $request->id;
                                            $user_payment->video_id = $request->admin_video_id;
                                            $user_payment->status = PAID_STATUS;
                                            $user_payment->is_watched = NOT_YET_WATCHED;
                                            $user_payment->paid_date = date('Y-m-d');
                                            $user_payment->amount = $total;
                                            $user_payment->coupon_amount = $coupon_amount;
                                            $user_payment->ppv_amount = $video->amount;
                                            $user_payment->coupon_code = $request->coupon_code;

                                            $user_payment->payment_mode = CARD;

                                            if ($video->type_of_user == NORMAL_USER) {

                                                $user_payment->type_of_user = tr('normal_users');

                                            } else if($video->type_of_user == PAID_USER) {

                                                $user_payment->type_of_user = tr('paid_users');

                                            } else if($video->type_of_user == BOTH_USERS) {

                                                $user_payment->type_of_user = tr('both_users');
                                            }


                                            if ($video->type_of_subscription == ONE_TIME_PAYMENT) {

                                                $user_payment->type_of_subscription = tr('one_time_payment');

                                            } else if($video->type_of_subscription == RECURRING_PAYMENT) {

                                                $user_payment->type_of_subscription = tr('recurring_payment');

                                            }

                                             // Coupon details

                                            $user_payment->is_coupon_applied = $is_coupon_applied;

                                            $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                                            $user_payment->coupon_amount = $coupon_amount;

                                            $user_payment->ppv_amount = $video->amount;

                                            $user_payment->amount = $total;

                                            $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;
                                                                  
                                            $user_payment->save();

                                            // Commission Spilit 

                                            // if(is_numeric($video->uploaded_by)) {

                                                if($user_payment->amount > 0) { 

                                                    // Do Commission spilit  and redeems for moderator

                                                    Log::info("ppv_commission_spilit started");

                                                    PaymentRepo::ppv_commission_split($video->id , $user_payment->id , $video->uploaded_by);

                                                    Log::info("ppv_commission_spilit END");
                                                    
                                                }

                                        
                                                \Log::info("ADD History - add_to_redeem");

                                            // } 

                                            $data = ['id'=> $request->id, 'token'=> $userModel->token , 'payment_id' => $payment_id];

                                            $response_array = array('success' => true, 'message'=>tr('payment_success'),'data'=> $data);

                                        } else {

                                            $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(902) , 'error_code' => 902);

                                            throw new Exception(tr('no_video_found'));

                                        }

                                } catch(\Stripe\Error\RateLimit $e) {

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);


                                } catch(\Stripe\Error\Card $e) {

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (\Stripe\Error\InvalidRequest $e) {
                                    // Invalid parameters were supplied to Stripe's API
                                   
                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (\Stripe\Error\Authentication $e) {

                                    // Authentication with Stripe's API failed

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (\Stripe\Error\ApiConnection $e) {

                                    // Network communication with Stripe failed

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (\Stripe\Error\Base $e) {
                                  // Display a very generic error to the user, and maybe send
                                    
                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (Exception $e) {
                                    // Something else happened, completely unrelated to Stripe

                                    $error_message = $e->getMessage();

                                    $error_code = $e->getCode();

                                    $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                    return response()->json($response_array);

                                } catch (\Stripe\StripeInvalidRequestError $e) {

                                        Log::info(print_r($e,true));

                                        $response_array = array('success' => false , 'error_messages' => $e->getMessage() ,'error_code' => 903);

                                       return response()->json($response_array , 200);
                                    
                                    }

                                }

                            
                            } else {

                                $response_array = array('success' => false , 'error_messages' => tr('no_video_found'));

                                throw new Exception(tr('no_video_found'));
                                
                            }

                        } else {

                            $response_array = array('success' => false , 'error_messages' => tr('no_default_card_available'));

                            throw new Exception(tr('no_default_card_available'));

                        }

                    } else {

                        $response_array = array('success' => false , 'error_messages' => tr('no_default_card_available'));

                        throw new Exception(tr('no_default_card_available'));

                    }

                } else {

                    throw new Exception(tr('no_user_detail_found'));
                    

                }

            }

            DB::commit();

            return response()->json($response_array,200);

        } catch (Exception $e) {

            DB::rollback();

            $message = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$message, 'error_code'=>$error_code];

            return response()->json($response_array);

        }
        
    }

    /**
     * Function Name : pay_ppv()
     * 
     * @uses Pay the payment for Pay per view through zero amount
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - Admin video id
     * 
     * @return response of success/failure message
     */
    public function pay_ppv(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), 
                array(
                    'admin_video_id' => 'required|exists:admin_videos,id',
                    'coupon_code'=>'exists:coupons,coupon_code',
                ),  array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'admin_video_id.exists' => tr('livevideo_not_exists'),
                ));

            if($validator->fails()) {

                $errors = implode(',', $validator->messages()->all());
                
                $response_array = ['success' => false, 'error_messages' => $errors, 'error_code' => 101];

                throw new Exception($errors);

            } else {

                $userModel = User::find($request->id);

                if ($userModel) {

                    $video = AdminVideo::find($request->admin_video_id);

                    if($video) {
                    
                        $coupon_amount = 0; 

                        $coupon_reason = '';

                        $is_coupon_applied = COUPON_NOT_APPLIED;

                        if ($request->coupon_code) {

                            $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                            if ($coupon) {

                                if ($coupon->status == COUPON_INACTIVE) {

                                    $coupon_reason = tr('coupon_inactive_reason');

                                } else {

                                    $check_coupon = $this->check_coupon_applicable_to_user($userModel, $coupon)->getData();

                                    if ($check_coupon->success) {

                                        $is_coupon_applied = COUPON_APPLIED;

                                        $amount_convertion = $coupon->amount;

                                        if ($coupon->amount_type == PERCENTAGE) {

                                            $amount_convertion = amount_convertion($coupon->amount, $video->amount);

                                        }

                                        if ($amount_convertion < $video->amount) {

                                            $total = $video->amount - $amount_convertion;

                                            $coupon_amount = $amount_convertion;

                                        } else {

                                            $total = 0;

                                            $coupon_amount = $amount_convertion;
                                            
                                        }

                                        // Create user applied coupon

                                        if($check_coupon->code == 2002) {

                                            $user_coupon = UserCoupon::where('user_id', $userModel->id)
                                                    ->where('coupon_code', $request->coupon_code)
                                                    ->first();

                                            // If user coupon not exists, create a new row

                                            if ($user_coupon) {

                                                if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                                    $user_coupon->no_of_times_used += 1;

                                                    $user_coupon->save();

                                                }

                                            }

                                        } else {

                                            $user_coupon = new UserCoupon;

                                            $user_coupon->user_id = $userModel->id;

                                            $user_coupon->coupon_code = $request->coupon_code;

                                            $user_coupon->no_of_times_used = 1;

                                            $user_coupon->save();

                                        }

                                    } else {

                                        $coupon_reason = $check_coupon->error_messages;
                                        
                                    }

                                }

                            } else {

                                $coupon_reason = tr('coupon_delete_reason');

                            }
                        }

                        $user_payment = new PayPerView;

                        $user_payment->payment_id  = $request->payment_id ? $request->payment_id : ($is_coupon_applied ? 'COUPON-DISCOUNT' : tr('no_ppv'));

                        $user_payment->status = PAID_STATUS;
                                
                        $user_payment->is_watched = NOT_YET_WATCHED;
                        
                        $user_payment->paid_date = date('Y-m-d');

                        $user_payment->user_id = $request->id;

                        $user_payment->video_id = $request->admin_video_id;
            
                        $user_payment->payment_mode = PAYPAL;

                        if ($video->type_of_user == NORMAL_USER) {

                            $user_payment->type_of_user =  tr('normal_users');

                        } else if($video->type_of_user == PAID_USER) {

                            $user_payment->type_of_user = tr('paid_users');

                        } else if($video->type_of_user == BOTH_USERS) {

                            $user_payment->type_of_user = tr('both_users');
                        }


                        if ($video->type_of_subscription == ONE_TIME_PAYMENT) {

                            $user_payment->type_of_subscription = tr('one_time_payment');

                        } else if($video->type_of_subscription == RECURRING_PAYMENT) {

                            $user_payment->type_of_subscription = tr('recurring_payment');

                        }

                        // Coupon details

                        $user_payment->is_coupon_applied = $is_coupon_applied;

                        $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                        $user_payment->coupon_amount = $coupon_amount;

                        $user_payment->ppv_amount = $video->amount;

                        $user_payment->amount = $total;

                        $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

                        $user_payment->save();

                        // Commission Spilit 
                        if(is_numeric($video->uploaded_by)) {

                            if($user_payment->amount > 0) { 

                                // Do Commission spilit  and redeems for moderator

                                Log::info("ppv_commission_spilit started");

                                PaymentRepo::ppv_commission_split($video->id , $user_payment->id , $video->uploaded_by);

                                Log::info("ppv_commission_spilit END"); 
                                
                            }

                            \Log::info("ADD History - add_to_redeem");

                        } 


                        $data = ['id'=> $request->id, 'token'=> $userModel->token , 'payment_id' => $user_payment->payment_id];

                        $response_array = array('success' => true, 'message'=>tr('payment_success'),'data'=> $data);

                    
                    } else {

                        $response_array = array('success' => false , 'error_messages' => tr('no_video_found'));

                        throw new Exception(tr('no_video_found'));
                        
                    }

               
                } else {

                    throw new Exception(tr('no_user_detail_found'));
                    

                }

            }

            DB::commit();

            return response()->json($response_array,200);

        } catch (Exception $e) {

            DB::rollback();

            $message = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$message, 'error_code'=>$error_code];

            return response()->json($response_array);

        }
        
    }

    /**
     * Function Name : paypal_ppv()
     * 
     * @uses Pay the payment for Pay per view through paypal
     * 
     * @created : 
     *
     * @edited : 
     *
     * @usage - WEB USAGE, @purpose - if the ppv amoutnt 0 then called this api
     *
     * @param object $request - Admin video id
     * 
     * @return response of success/failure message
     */
    public function paypal_ppv(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'admin_video_id'=>'required|exists:admin_videos,id',
                    'payment_id'=>'required',
                    'coupon_code'=>'exists:coupons,coupon_code',
                ),  array(
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'admin_video_id.exists' => tr('livevideo_not_exists'),
                ));

            if ($validator->fails()) {
                // Error messages added in response for debugging
                $errors = implode(',',$validator->messages()->all());

                $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

                throw new Exception($errors);

            } else {

                $video = AdminVideo::find($request->admin_video_id);

                $user = User::find($request->id);

                $total = $video->amount;

                $coupon_amount = 0;

                $coupon_reason = '';

                $is_coupon_applied = COUPON_NOT_APPLIED;
                  
                if ($request->coupon_code) {

                    $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                    if ($coupon) {
                        
                        if ($coupon->status == COUPON_INACTIVE) {

                            $coupon_reason = tr('coupon_inactive_reason');

                        } else {

                            $check_coupon = $this->check_coupon_applicable_to_user($user, $coupon)->getData();

                            if ($check_coupon->success) {

                                $is_coupon_applied = COUPON_APPLIED;

                                $amount_convertion = $coupon->amount;

                                if ($coupon->amount_type == PERCENTAGE) {

                                    $amount_convertion = amount_convertion($coupon->amount, $video->amount);

                                }

                                if ($amount_convertion < $video->amount) {

                                    $total = $video->amount - $amount_convertion;

                                    $coupon_amount = $amount_convertion;

                                } else {

                                    $total = 0;

                                    $coupon_amount = $amount_convertion;
                                    
                                }

                                // Create user applied coupon

                                if($check_coupon->code == 2002) {

                                    $user_coupon = UserCoupon::where('user_id', $user->id)
                                            ->where('coupon_code', $request->coupon_code)
                                            ->first();

                                    // If user coupon not exists, create a new row

                                    if ($user_coupon) {

                                        if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                            $user_coupon->no_of_times_used += 1;

                                            $user_coupon->save();

                                        }

                                    }

                                } else {

                                    $user_coupon = new UserCoupon;

                                    $user_coupon->user_id = $user->id;

                                    $user_coupon->coupon_code = $request->coupon_code;

                                    $user_coupon->no_of_times_used = 1;

                                    $user_coupon->save();

                                }

                            } else {

                                $coupon_reason = $check_coupon->error_messages;
                                
                            }

                        }

                    } else {

                        $coupon_reason = tr('coupon_delete_reason');
                    }

                }

                $user_payment = new PayPerView;
                
                $user_payment->payment_id  = $request->payment_id ? $request->payment_id : ($is_coupon_applied ? 'COUPON-DISCOUNT' : tr('no_ppv'));

                $user_payment->status = PAID_STATUS;
                        
                $user_payment->is_watched = NOT_YET_WATCHED;
                
                $user_payment->paid_date = date('Y-m-d');

                $user_payment->user_id = $request->id;

                $user_payment->video_id = $request->admin_video_id;
	
	            $user_payment->payment_mode = PAYPAL;

                if ($video->type_of_user == NORMAL_USER) {

                    $user_payment->type_of_user =  tr('normal_users');

                } else if($video->type_of_user == PAID_USER) {

                    $user_payment->type_of_user = tr('paid_users');

                } else if($video->type_of_user == BOTH_USERS) {

                    $user_payment->type_of_user = tr('both_users');
                }


                if ($video->type_of_subscription == ONE_TIME_PAYMENT) {

                    $user_payment->type_of_subscription = tr('one_time_payment');

                } else if($video->type_of_subscription == RECURRING_PAYMENT) {

                    $user_payment->type_of_subscription = tr('recurring_payment');

                }

                // Coupon details

                $user_payment->is_coupon_applied = $is_coupon_applied;

                $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : '';

                $user_payment->coupon_amount = $coupon_amount;

                $user_payment->ppv_amount = $video->amount;

                $user_payment->amount = $total;

                $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

                $user_payment->save();

                if($user_payment) {

                    if($user_payment->amount > 0) { 

                        // Do Commission spilit  and redeems for moderator

                        Log::info("ppv_commission_spilit started");

                        PaymentRepo::ppv_commission_split($video->id , $user_payment->id , $video->uploaded_by);

                        Log::info("ppv_commission_spilit END"); 
                        
                    }

                    \Log::info("ADD History - add_to_redeem");

                }

                $viewerModel = User::find($request->id);

                $response_array = ['success'=>true, 'message'=>tr('payment_success'), 
                                    'data'=>['id'=>$request->id,
                                     'token'=>$viewerModel ? $viewerModel->token : '']];

            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            $message = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$message, 'error_code'=>$error_code];

            return response()->json($response_array);
        }

    }

    /**
     * Function Name : card_details()
     * 
     * @uses List of card details based on logged in user id
     * 
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - user id
     * 
     * @return list of cards
     */
    public function card_details(Request $request) {

        $cards = Card::select('user_id as id','id as card_id','customer_id',
                'last_four', 'card_token', 'is_default','month','year',
            \DB::raw('DATE_FORMAT(created_at , "%e %b %y") as created_date'))
            ->where('user_id', $request->id)->get();



        $cards = (!empty($cards) && $cards != null) ? $cards : [];

        $response_array = ['success'=>true, 'data'=>$cards];

        return response()->json($response_array, 200);
    }

    /**
     * Function Name : payment_card_add()
     * 
     * @uses Add Payment card based on logged in user id
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - user id
     * 
     * @return card details objet
     */
    public function payment_card_add(Request $request) {

        $validator = Validator::make($request->all(), 
            array(
                'number' => 'numeric',
                'card_token'=>'required',
            )
            );

        if($validator->fails()) {

            $errors = implode(',', $validator->messages()->all());
            
            $response_array = ['success' => false, 'error_messages' => $errors, 'error_code' => 101];

            return response()->json($response_array);

        } else {

            $userModel = User::find($request->id);

            $stripe_secret_key = \Setting::get('stripe_secret_key');

            if($stripe_secret_key) {

                \Stripe\Stripe::setApiKey($stripe_secret_key);

            } else {

                $response_array = ['success'=>false, 'error_messages'=>tr('add_card_is_not_enabled')];

                return response()->json($response_array);
            }

            try {

                // Get the key from settings table
                
                $customer = \Stripe\Customer::create([
                        "card" => $request->card_token,
                        "email" => $userModel->email
                    ]);

                if($customer) {

                    $customer_id = $customer->id;

                    $card_details_from_stripe = $customer->sources->data ? $customer->sources->data[0]: [];

                    $cards = new Card;
                    $cards->user_id = $userModel->id;
                    $cards->customer_id = $customer_id;
                    $cards->last_four = $card_details_from_stripe->last4;
                    $cards->card_token = $card_details_from_stripe->id ?: "NO-TOKEN";
                    $cards->month = $card_details_from_stripe->exp_month ?: "01";
                    $cards->year = $card_details_from_stripe->exp_year ?: "01";

                    // Check is any default is available
                    $check_card = Card::where('user_id', $userModel->id)->first();

                    if($check_card)
                        $cards->is_default = 0;
                    else
                        $cards->is_default = 1;
                    
                    $cards->save();

                    if($userModel && $cards->is_default) {

                        $userModel->payment_mode = 'card';

                        $userModel->card_id = $cards->id;

                        $userModel->save();
                    }

                    $data = [
                            'user_id'=>$request->id, 
                            'id'=>$request->id, 
                            'token'=>$userModel->token,
                            'card_id'=>$cards->id,
                            'customer_id'=>$cards->customer_id,
                            'last_four'=>$cards->last_four, 
                            'card_token'=>$cards->card_token, 
                            'is_default'=>$cards->is_default,
                            'month' => $cards->month,
                            'year' => $cards->year
                            ];

                    $response_array = array('success' => true,'message'=>tr('add_card_success'), 
                        'data'=> $data);

                    return response()->json($response_array);

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>tr('Could not create client ID')];

                    throw new Exception(tr('Could not create client ID'));
                    
                }
            
            } catch(Exception $e) {

                $response_array = ['success'=>false, 'error_messages'=>$e->getMessage()];

                return response()->json($response_array);

            }

        }

    }    

    /**
     * Function Name : default_card()
     * 
     * @uses Change the card as default card
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - user id, card id
     * 
     * @return card details object
     */
    public function default_card(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'card_id' => 'required|integer|exists:cards,id,user_id,'.$request->id,
            ),
            array(
                'exists' => 'The :attribute doesn\'t belong to user:'.$request->id
            )
        );

        if($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages' => $error_messages, 'error_code' => 101);

        } else {

            $user = User::find($request->id);
            
            $old_default = Card::where('user_id' , $request->id)->where('is_default', DEFAULT_TRUE)->update(array('is_default' => DEFAULT_FALSE));

            $card = Card::where('id' , $request->card_id)->update(array('is_default' => DEFAULT_TRUE));

            if($card) {

                if($user) {

                    $user->card_id = $request->card_id;

                    $user->save();
                }

                $response_array = Helper::null_safe(array('success' => true, 'data'=>['id'=>$request->id,'token'=>$user->token], 'message'=>tr('default_card_success')));

            } else {

                $response_array = array('success' => false , 'error_messages' => tr('something_error'));

            }
        }
        return response()->json($response_array , 200);
    
    }

    /**
     * Function Name : delete_card()
     * 
     * @uses Delete the card who has logged in (Based on User Id, Card Id)
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - user id, card id
     * 
     * @return success/failure message
     */
    public function delete_card(Request $request) {
    
        $card_id = $request->card_id;

        $validator = Validator::make(
            $request->all(),
            array(
                'card_id' => 'required|integer|exists:cards,id,user_id,'.$request->id,
            ),
            array(
                'exists' => 'The :attribute doesn\'t belong to user:'.$request->id
            )
        );

        if ($validator->fails()) {
            
            $error_messages = implode(',', $validator->messages()->all());
            
            $response_array = array('success' => false , 'error_messages' => $error_messages , 'error_code' => 101);
        
        } else {

            $user = User::find($request->id);

            // The user can delete the all the cards

            // if ($user->card_id == $card_id) {

            //     $response_array = array('success' => false, 'error_messages'=> tr('card_default_error'));

            // } else {

                Card::where('id',$card_id)->delete();

                if($user) {

                    $cards = Card::where('user_id' , $request->id)->count();

                    if ($cards > 1) {


                    } else {

                        if($check_card = Card::where('user_id' , $request->id)->first()) {

                            $check_card->is_default =  DEFAULT_TRUE;

                            $user->card_id = $check_card->id;

                            $check_card->save();

                        } else { 

                            $user->payment_mode = COD;

                            $user->card_id = DEFAULT_FALSE;
                        }

                    }

                    $user->save();
                }

                $response_array = array('success' => true, 
                        'message'=>tr('card_deleted'), 
                        'data'=> ['id'=>$request->id,'token'=>$user->token, 'position'=>$request->position]);

            // }
            
        }
    
        return response()->json($response_array , 200);
   
    }

    /**
     * Function Name : subscription_plans()
     * 
     * @uses List out all the subscription plans (Mobile Usage)
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - As of now no attributes
     * 
     * @return list of subscriptions
     */
    public function subscription_plans(Request $request) {

        $currency = Setting::get('currency');

        $query = Subscription::select('id as subscription_id',
                'title', 'description', 'plan','amount', 'no_of_account',
                'status', 'popular_status','created_at' , DB::raw("'$currency' as currency"))
                ->where('status' , DEFAULT_TRUE);

        if ($request->id) {

            $user = User::find($request->id);

            if ($user) {

               if ($user->one_time_subscription == DEFAULT_TRUE) {

                   $query->where('amount','>', 0);

               }

            } 

        }

        $skip = $request->skip ?: 0;

        $take = Setting::get('admin_take_count') ?: 12;

        $model = $query->orderBy('amount' , 'asc')->skip($skip)->take($take)->get();

        $model = (!empty($model) && $model != null) ? $model : [];

        $response_array = ['success'=>true, 'data'=>$model];

        return response()->json($response_array, 200);

    }

    /**
     * Function Name : subscribedPlans()
     * 
     * @uses List out all the subscribed pans based on the user id
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - As of now no attributes
     * 
     * @return list of subscribed plans
     */
    public function subscribedPlans(Request $request){

        $currency = Setting::get('currency');

        $base_query = UserPayment::where('user_id' , $request->id)
                    ->leftJoin('subscriptions', 'subscriptions.id', '=', 'subscription_id')
                    ->select('user_id as id',
                            'subscription_id',
                            'user_payments.payment_id',
                            'user_payments.id as user_subscription_id',
                            'subscriptions.title as title',
                            'subscriptions.description as description',
                            'subscriptions.plan',
                            'user_payments.amount as amount',
                            'user_payments.subscription_amount',
                            'user_payments.coupon_amount',
                            'user_payments.coupon_code',
			                 'payment_mode',
                            'no_of_account',
                            'popular_status',
                            'is_cancelled',
                            'user_payments.status as payment_status',
                            \DB::raw('DATE_FORMAT(user_payments.expiry_date , "%e %b %Y") as expiry_date'),
                            'user_payments.created_at as date',
                            DB::raw("'$currency' as currency"))
                    ->orderBy('user_payments.created_at', 'desc');
                    

        if(isset($request->skip)) {

            $skip = $request->skip ?: 0;

            $take = $request->take ?: ( Setting::get('admin_take_count') ?: 12);

            $base_query = $base_query->skip($skip)->take($take);
        }

        $model = $base_query->get();

        $model = (!empty($model) && $model != null) ? $model : [];


        $data = [];

        $last = UserPayment::select('user_payments.*', DB::raw("'$currency' as currency"))->where('user_id', $request->id)
            ->where('status', DEFAULT_TRUE)
            ->orderBy('created_at', 'desc')->first();

        foreach ($model as $key => $value) {

            
            $data[] = [

                'user_subscription_id'=>$value->user_subscription_id,

                'id'=>$value->id,

                'subscription_id'=>$value->subscription_id,

                'payment_id'=>$value->payment_id,

                'title'=>$value->title,

                'description'=>$value->description,

                'plan'=>$value->plan,

                'no_of_account'=>$value->subscription ? $value->subscription->no_of_account : '',

                'amount'=> $value->amount,

                'expiry_date'=>date('d-m-Y h:i A', strtotime($value->expiry_date)),

                'created_at'=>$value->date,

                'currency'=>Setting::get('currency'),

                'total_amount'=>$value->subscription_amount,

                'popular_status'=>$value->popular_status,

                'coupon_amount'=>$value->coupon_amount,

                'coupon_code'=>$value->coupon_code,

	        'active_plan'=> $last ? (($last->id == $value->user_subscription_id) ? (strtotime($value->expiry_date) >= strtotime('Y-m-d') ? DEFAULT_TRUE : DEFAULT_FALSE) : DEFAULT_FALSE) : DEFAULT_FALSE,

                'cancelled_status'=> $value->is_cancelled,

                'payment_status'=>$value->payment_status,
                
                'payment_mode'=>$value->payment_mode

            ];

        }

        $response_array = ['success'=>true, 'data'=>$data];

        return response()->json($response_array);

    }

    /**
     * Function Name : check_coupon_applicable_to_user()
     *
     * @uses To check the coupon code applicable to the user or not
     *
     * @created: Shobana Chandrasekar
     *
     * @edited:
     *
     * @param objects $coupon - Coupon details
     *
     * @param objects $user - User details
     *
     * @return response of success/failure message
     */
    public function check_coupon_applicable_to_user($user, $coupon) {

        try {

            $sum_of_users = UserCoupon::where('coupon_code', $coupon->coupon_code)->sum('no_of_times_used');

            if ($sum_of_users < $coupon->no_of_users_limit) {


            } else {

                throw new Exception(tr('total_no_of_users_maximum_limit_reached'));
                
            }

            $user_coupon = UserCoupon::where('user_id', $user->id)
                ->where('coupon_code', $coupon->coupon_code)
                ->first();

            // If user coupon not exists, create a new row

            if ($user_coupon) {

                if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                   // $user_coupon->no_of_times_used += 1;

                   // $user_coupon->save();

                    $response_array = ['success'=>true, 'message'=>tr('add_no_of_times_used_coupon'), 'code'=>2002];

                } else {

                    throw new Exception(tr('per_users_limit_exceed'));
                }

            } else {

                $response_array = ['success'=>true, 'message'=>tr('create_a_new_coupon_row'), 'code'=>2001];

            }

            return response()->json($response_array);

        } catch (Exception $e) {

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage()];

            return response()->json($response_array);
        }

    }

    /**
     * Function Name : pay_now()
     * 
     * @uses Pay the payment of subscription plan using paypal (Mobile Usage) 
     *
     * @created : Shobana C
     *
     * @edited : 
     *
     * @param object $request - payment id, subscription id
     * 
     * @return resposne of success/failure message
     */
    public function pay_now(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                array(
                    'subscription_id'=>'required|exists:subscriptions,id',
                    'payment_id'=>'required',
                    'coupon_code'=>'exists:coupons,coupon_code',
                ),  array(
                   'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'subscription_id.exists' => tr('subscription_not_exists'),
                ));

            if ($validator->fails()) {
                // Error messages added in response for debugging
                $errors = implode(',',$validator->messages()->all());

                $response_array = ['success' => false,'error_messages' => $errors,'error_code' => 101];

                throw new Exception($errors);

            } else {

                $user = User::find($request->id);

                $subscription = Subscription::find($request->subscription_id);

                $total = $subscription->amount;

                $coupon_amount = 0;

                $coupon_reason = '';

                $is_coupon_applied = COUPON_NOT_APPLIED;

                if ($request->coupon_code) {

                    $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

                    if ($coupon) {

                        if ($coupon->status == COUPON_INACTIVE) {

                            $coupon_reason = tr('coupon_inactive_reason');

                        } else {

                            $check_coupon = $this->check_coupon_applicable_to_user($user, $coupon)->getData();

                            if ($check_coupon->success) {

                                $is_coupon_applied = COUPON_APPLIED;

                                $amount_convertion = $coupon->amount;

                                if ($coupon->amount_type == PERCENTAGE) {

                                    $amount_convertion = amount_convertion($coupon->amount, $subscription->amount);

                                }


                                if ($amount_convertion < $subscription->amount) {

                                    $total = $subscription->amount - $amount_convertion;

                                    $coupon_amount = $amount_convertion;

                                } else {

                                    // throw new Exception(Helper::get_error_message(156),156);

                                    $total = 0;

                                    $coupon_amount = $amount_convertion;
                                    
                                }

                                // Create user applied coupon

                                if($check_coupon->code == 2002) {

                                    $user_coupon = UserCoupon::where('user_id', $user->id)
                                            ->where('coupon_code', $request->coupon_code)
                                            ->first();

                                    // If user coupon not exists, create a new row

                                    if ($user_coupon) {

                                        if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                                            $user_coupon->no_of_times_used += 1;

                                            $user_coupon->save();

                                        }

                                    }

                                } else {

                                    $user_coupon = new UserCoupon;

                                    $user_coupon->user_id = $user->id;

                                    $user_coupon->coupon_code = $request->coupon_code;

                                    $user_coupon->no_of_times_used = 1;

                                    $user_coupon->save();

                                }

                            } else {

                                $coupon_reason = $check_coupon->error_messages;

                            }

                        }

                    } else {

                        $coupon_reason = tr('coupon_delete_reason');
                    }
                }

                $model = UserPayment::where('user_id' , $request->id)->where('status', DEFAULT_TRUE)->orderBy('id', 'desc')->first();

                $user_payment = new UserPayment;

                if ($model) {

                    if (strtotime($model->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

                     $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$subscription->plan} months", strtotime($model->expiry_date)));

                    } else {

                        $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));

                    }

                } else {

                    $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));

                }

                $user_payment->payment_id  = $request->payment_id;

                $user_payment->user_id = $request->id;

                $user_payment->amount = $total;

                $user_payment->status = PAID_STATUS;

                $user_payment->subscription_id = $request->subscription_id;
		
                $user_payment->payment_mode = PAYPAL;

                // Coupon details

                $user_payment->is_coupon_applied = $is_coupon_applied;

                $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';

                $user_payment->coupon_amount = $coupon_amount;

                $user_payment->subscription_amount = $subscription->amount;

                $user_payment->amount = $total;

                $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;


                $user_payment->save();

                if($user_payment) {

                    if ($user_payment->user) {

                        if ($user_payment->amount <= 0) {

                            $user_payment->user->one_time_subscription = DEFAULT_TRUE;

                        }

                        $user_payment->user->user_type = SUBSCRIBED_USER;

                        $user_payment->user->save();

                    } else {

                        throw new Exception(tr('no_user_detail_found'));
                        
                    }

                } else {

                    throw new Exception(tr('user_payment_not_save'));
                    
                }

                $response_array = ['success'=>true, 'message'=>tr('payment_success'), 
                        'data'=>[
                            'id'=>$request->id,
                            'token'=>$user_payment->user ? $user_payment->user->token : '',
                            'no_of_account'=>$subscription->no_of_account
                            ]];

            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            $message = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success'=>false, 'error_messages'=>$message, 'error_code'=>$error_code];

            return response()->json($response_array);
        }

    }

    /**
     * Function Name : likevideo()
     * 
     * @uses Like videos in each single video based on logged in user id
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - video id & sub profile id
     * 
     * @return resposne of success/failure message with count of like and dislike
     */
    public function likevideo(Request $request) {

        $validator = Validator::make($request->all() , [
            'admin_video_id' => 'required|exists:admin_videos,id,status,'.VIDEO_PUBLISHED.',is_approved,'.VIDEO_APPROVED,
            'sub_profile_id'=>'required|exists:sub_profiles,id',
            ], array(
                'exists' => 'The :attribute doesn\'t exists',
            ));

         if ($validator->fails()) {

            $errors = implode(',', $validator->messages()->all());

            $response_array = array('success' => false , 'error_messages'=> $errors ,  'error_code' => 101);

        } else {

            $model = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                    ->where('user_id',$request->id)
                    ->where('sub_profile_id',$request->sub_profile_id)
                    ->first();

            $like_count = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                ->where('like_status', DEFAULT_TRUE)
                ->count();

            $dislike_count = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                ->where('dislike_status', DEFAULT_TRUE)
                ->count();

            if (!$model) {

                $model = new LikeDislikeVideo;

                $model->admin_video_id = $request->admin_video_id;

                $model->user_id = $request->id;

                $model->sub_profile_id = $request->sub_profile_id;

                $model->like_status = DEFAULT_TRUE;

                $model->dislike_status = DEFAULT_FALSE;

                $model->save();

                $response_array = ['success'=>true, 'like_count'=>$like_count+1, 'dislike_count'=>$dislike_count, 'delete'=>0];

            } else {

                if($model->dislike_status) {

                    $model->like_status = DEFAULT_TRUE;

                    $model->dislike_status = DEFAULT_FALSE;

                    $model->save();

                    $response_array = ['success'=>true, 'like_count'=>$like_count+1, 'dislike_count'=>$dislike_count-1];


                } else {

                    $model->delete();

                    $response_array = ['success'=>true, 'like_count'=>$like_count-1, 'dislike_count'=>$dislike_count, 'delete'=>1];

                }

            }

        }

        return response()->json($response_array);

    }

    /**
     * Function Name : dislikevideo()
     * 
     * @uses DisLike videos in each single video based on logged in user id
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - video id & sub profile id
     * 
     * @return resposne of success/failure message with count of like and dislike
     */
    public function dislikevideo(Request $request) {

        $validator = Validator::make($request->all() , [
            'admin_video_id' => 'required|exists:admin_videos,id,status,'.VIDEO_PUBLISHED.',is_approved,'.VIDEO_APPROVED,
            'sub_profile_id'=>'required|exists:sub_profiles,id',
            ], array(
                'exists' => 'The :attribute doesn\'t exists',
            ));

         if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

        } else {

            $model = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                    ->where('user_id',$request->id)
                    ->where('sub_profile_id',$request->sub_profile_id)
                    ->first();
           
            $like_count = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                ->where('like_status', DEFAULT_TRUE)
                ->count();

            $dislike_count = LikeDislikeVideo::where('admin_video_id', $request->admin_video_id)
                ->where('dislike_status', DEFAULT_TRUE)
                ->count();

            if (!$model) {

                $model = new LikeDislikeVideo;

                $model->admin_video_id = $request->admin_video_id;

                $model->user_id = $request->id;

                $model->sub_profile_id = $request->sub_profile_id;

                $model->like_status = DEFAULT_FALSE;

                $model->dislike_status = DEFAULT_TRUE;

                $model->save();

                $response_array = ['success'=>true, 'like_count'=>$like_count, 'dislike_count'=>$dislike_count+1, 'delete'=>0];

            } else {

                if($model->like_status) {

                    $model->like_status = DEFAULT_FALSE;

                    $model->dislike_status = DEFAULT_TRUE;

                    $model->save();

                    $response_array = ['success'=>true, 'like_count'=>$like_count-1, 'dislike_count'=>$dislike_count+1];

                } else {

                    $model->delete();

                    $response_array = ['success'=>true, 'like_count'=>$like_count, 'dislike_count'=>$dislike_count-1, 'delete'=>1];

                }

            }

        }

        return response()->json($response_array);

    }

    /**
     * Function Name : spam_videos()
     * 
     * @uses List of spam videos
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - sub profile id
     * 
     * @return array of spam videos
     */
    public function spam_videos(Request $request) {

        $validator = Validator::make($request->all() , [
            'sub_profile_id'=>'required|exists:sub_profiles,id',
            ], array(
                'exists' => 'The :attribute doesn\'t exists',
            ));

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

        } else {


            $subProfile = SubProfile::where('user_id', $request->id)
                        ->where('id', $request->sub_profile_id)->first();

            if (!$subProfile) {

                $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];
                
                return response()->json($response_array , 200);
                
            } 

            $user = User::find($request->id);

            if ($request->device_type != DEVICE_WEB) {

                $query = Flag::where('flags.user_id', $request->id)
                    ->where('flags.sub_profile_id', $request->sub_profile_id)
                    ->leftJoin('admin_videos' , 'flags.video_id' , '=' , 'admin_videos.id')
                    ->where('admin_videos.is_approved' , 1)
                    ->where('admin_videos.status' , 1);

                if ($request->skip) {

                    $model = $query->skip($request->skip)->take(Setting::get('admin_take_count'))->get();

                } else {

                    $model = $query->get();
                }
                

                $flag_video = [];

                if (!empty($model) && $model != null) {

                    foreach ($model as $key => $value) {

                        $displayDetails = displayFullDetails($value->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $request->sub_profile_id);

                        if ($displayDetails) {
                        
                            $flag_video[] = $displayDetails;

                        }

                    }
                }

            } else {

                $model = Flag::where('flags.user_id', $request->id)
                    ->select('flags.*', 'admin_videos.id as admin_video_id')
                    ->where('flags.sub_profile_id', $request->sub_profile_id)
                    ->leftJoin('admin_videos' , 'flags.video_id' , '=' , 'admin_videos.id')
                    ->where('admin_videos.is_approved' , 1)
                    ->where('admin_videos.status' , 1)
                    ->skip($request->skip)->take($request->take)->get();

                $chunk = $model->chunk(4);

                $flag_video = [];

                foreach ($chunk as $key => $value) {


                    $group = [];

                    foreach ($value as $key => $data) {

                        $displayDetails = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $request->sub_profile_id);

                        if ($displayDetails) {
                     
                            $group[] = $displayDetails;

                        }


                    }

                    $flag_video[] = $group;

                }


            }

            $response_array = ['success'=>true, 'data'=>$flag_video];
        }

        return response()->json($response_array);

    }

    /**
     * Function Name : add_spam()
     * 
     * @uses Spam videos based on each single video based on logged in user id, If they flagged th video they wont see in any of the pages except spam videos page
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - sub profile id, video id
     * 
     * @return spam video details
     */
    public function add_spam(Request $request) {

        $validator = Validator::make($request->all(), [
            'admin_video_id' => 'required|exists:admin_videos,id',
            'sub_profile_id'=>'required|exists:sub_profiles,id',
            'reason' => 'required',
        ], array(
                'exists' => 'The :attribute doesn\'t exists',
            ));

        
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json($response_array);
        }

        $subProfile = SubProfile::where('user_id', $request->id)
                        ->where('id', $request->sub_profile_id)->first();

        if (!$subProfile) {

            $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];

            return response()->json($response_array);
            
        }

        $spam_video = Flag::where('user_id', $request->id)->where('video_id', $request->admin_video_id)->where('sub_profile_id', $request->sub_profile_id)->first();

        if (!$spam_video) {

            
            $data = $request->all();

            
            $data['user_id'] = $request->id;

            $data['video_id'] =$request->admin_video_id;

            $data['sub_profile_id'] = $request->sub_profile_id;
            
            $data['status'] = DEFAULT_TRUE;

            
            if (Flag::create($data)) {

                return response()->json(['success'=>true, 'message'=>tr('report_video_success_msg')]);

            } else {
                
                return response()->json(['success'=>true, 'message'=>tr('admin_published_video_failure')]);
            }

        } else {

            return response()->json(['success'=>true, 'message'=>tr('report_video_success_msg')]);

        }

    }

    /**
     * Function Name : reasons()
     * 
     * @uses List of reasons to display while spam video
     *
     * @created : Shobana C
     *
     * @edited : 
     *
     * @return array of reasons
     */
    
    public function reasons() {

        $reasons = getReportVideoTypes();

        return response()->json(['success'=>true, 'data'=>$reasons]);
    }

    /**
     * Function Name : remove_spam()
     * 
     * @uses Remove Spam videos based on each single video based on logged in user id, You can see the videos in all the pages
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - sub profile id, video id
     * 
     * @return spam video details
     */
    public function remove_spam(Request $request) {

        $validator = Validator::make($request->all(), [
            // 'admin_video_id' => 'exists:admin_videos,id',
            'sub_profile_id'=>'required|exists:sub_profiles,id',
        ], array(
                'exists' => 'The :attribute doesn\'t exists',
            ));
        
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json($response_array);
        }

        if($request->status == 1) {

            $model = Flag::where('user_id', $request->id)
                ->where('sub_profile_id', $request->sub_profile_id)->delete();

            return response()->json(['success'=>true, 'message'=>tr('unmark_report_video_success_msg')]);

        } else {

            $admin_video_details = AdminVideo::where('id', $request->admin_video_id)->first();
                

            if(!$admin_video_details) {

                $response_array = array('success' => false, 'error_messages'=> Helper::get_error_message(157) , 'error_code' => 157);


                return response()->json($response_array , 200);
                
            }
    
            $model = Flag::where('user_id', $request->id)
                ->where('sub_profile_id', $request->sub_profile_id)
                ->where('video_id', $request->admin_video_id)
                ->first();

            if ($model) {

                $model->delete();

                return response()->json(['success'=>true, 'message'=>tr('unmark_report_video_success_msg')]);

            } else {
                
                return response()->json(['success'=>true, 'message'=>tr('admin_published_video_failure')]);
            }
        }
    }

    /**
     * Function Name : watch_count()
     * 
     * @uses Each and every video once the user click the video player, the count will increase
     *
     * @created : 
     *
     * @edited : 
     *     
     * @param object $request - video id
     * 
     * @return spam video details
     */
    public function watch_count(Request $request) {

        if($video = AdminVideo::where('id',$request->admin_video_id)
                ->where('status',1)
                ->first()) {

            // $video->watch_count += 1;

            // $video->save();

            Log::info($video->watch_count);

            return response()->json([
                    'success'=>true, 
                    'data'=>[ 'watch_count' => number_format_short($video->watch_count)]]);

        } else {

            return response()->json(['success'=>false, 'error_messages'=>tr('no_video_found')]);

        }

    }

    /**
     * Function Name : plan_detail()
     *
     * @uses Display plan detail based on plan id
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $param - User id, token and plan id
     *
     * @return response of object
     */
    public function plan_detail(Request $request) {

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:subscriptions,id',            
        ], array(
                'exists' => 'The :attribute doesn\'t exists',
            ));
        
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json($response_array);
        }
        
        $currency = Setting::get('currency');

        $model = Subscription::select('subscriptions.*', DB::raw("'$currency' as currency"))->find($request->plan_id);

        if ($model) {

            return response()->json(['success'=>true, 'data'=>$model]);

        } else {
            
            return response()->json(['success'=>false, 'message'=>tr('subscription_not_found')]);
        }

    } 

    /**
     * Function Name : logout()
     *
     * @uses Delete logged device while logout user
     *
     * @created : 
     *
     * @edited : 
     *
     * @param interger $request - User Id
     *
     * @return boolean  succes/failure message
     */
    public function logout(Request $request) {

        try {

            DB::beginTransaction();

            if ($request->signout_from_all_device ==  SIGNOUT_ALL_DEVICE_ENABLE) {

                $model = UserLoggedDevice::where('user_id', $request->id)->get();

                foreach ($model as $key => $value) {

                    $value->delete();

                }

                $user = User::find($request->id);

                if ($user) {

                    $user->logged_in_account = 0;

                    $user->token = Helper::generate_token();

                    $user->token_expiry = Helper::generate_token_expiry();

                     if ($user->save()) {

                        $response_array = ['success'=>true , 'message' => Helper::get_message(104) , 'coed' => 104];
                            
                    } else {

                        throw new Exception(tr('user_details_not_save'));

                    }


                } else {

                    throw new Exception(tr('no_user_detail_found'));
                    
                }

            } else {

                $model = UserLoggedDevice::where('user_id', $request->id)->first();

                $response_array = ['success'=>true , 'message' => Helper::get_message(104) , 'coed' => 104];

                if ($model) {

                    if ($model->delete()) {

                        $user = User::find($request->id);

                        if ($user) {

                            $user->logged_in_account -= 1;

                            if ($user->save()) {

                                $response_array = ['success' => true , 'message' => Helper::get_message(104) , 'coed' => 104];
                                    
                            } else {

                                throw new Exception(tr('user_details_not_save'));

                            }

                        } else {

                            throw new Exception(tr('no_user_detail_found'));
                            
                        }

                    } else {

                        throw new Exception(tr('logged_in_device_not_delete'));
                        
                    }

                }

            }

            DB::commit();

            return response()->json($response_array);

        } catch(Exception $e) {

            $error_messages = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false, 'error_messages' => $error_messages , 'error_code' => $error_code];

            return response()->json($response_array);
        }

    }

    /**
     * Function Name : check_token_valid()
     *
     * @uses To check the token is valid for the user or not
     * 
     * @created : 
     *
     * @edited : 
     *     
     * @param object $request - User id and token
     *
     * @return Object with success message
     */
    public function check_token_valid(Request $request) {

        return response()->json(['data'=>$request->all(), 'success'=>true]);

    }

    /**
     * Function Nmae : ppv_list()
     * 
     * @uses to list out  all the paid videos by logged in user using PPV
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - User id, token 
     *
     * @return response of array with message
     */
    public function ppv_list(Request $request) {

        $skip = $request->skip ?: 0;

        $take = $request->take ?: (Setting::get('admin_take_count') ?: 12);

        $model = PayPerView::select('pay_per_views.id as pay_per_view_id',
                'video_id as admin_video_id',
                'admin_videos.title',
                'admin_videos.description',
                'pay_per_views.amount',
                'pay_per_views.ppv_amount',
                'pay_per_views.coupon_amount',
                'pay_per_views.coupon_code',
                'pay_per_views.status as video_status',
                'admin_videos.default_image as picture',
                'pay_per_views.type_of_subscription',
                'pay_per_views.type_of_user',
                'pay_per_views.payment_id',
                'pay_per_views.payment_mode',
                'pay_per_views.user_id',
                 DB::raw('DATE_FORMAT(pay_per_views.created_at , "%e %b %y") as paid_date'))
                ->leftJoin('admin_videos', 'admin_videos.id', '=', 'pay_per_views.video_id')
                ->whereRaw("(pay_per_views.amount > 0 or pay_per_views.coupon_amount > 0) and pay_per_views.user_id = {$request->id}")
                ->orderBy('pay_per_views.created_at', 'desc')
                ->skip($skip)
                ->take($take)
                ->get();

        $data = [];

        foreach ($model as $key => $value) {
            
            $data[] = ['pay_per_view_id'=>$value->pay_per_view_id,
                    'admin_video_id'=>$value->admin_video_id,
                    'title'=>$value->title,
                    'amount'=>$value->amount,
                    'video_status'=>$value->video_status,
                    'paid_date'=>$value->paid_date,
                    'coupon_amount'=>$value->coupon_amount,
                    'total_amount'=>$value->ppv_amount,
                    'currency'=>Setting::get('currency'),
                    'picture'=>$value->picture,
                    'default_image'=>$value->picture,
                    'mobile_image'=>$value->picture,
                    'type_of_subscription'=>$value->type_of_subscription,
                    'type_of_user'=>$value->type_of_user,
                    'payment_id'=>$value->payment_id,
                    'coupon_code'=>$value->coupon_code,
                    'payment_mode'=>$value->payment_mode];

        }

        return response()->json(['success'=>true,'data'=>$data]);

    } 

    /**
     * Function Name : continue_watching_videos
     * 
     * @uses : Displayed partially seen videos by the users based on their profile
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - USer id, token & sub profile id
     *
     * @return response of array 
     */
    public function continue_watching_videos(Request $request) {

        try {

            $validator = Validator::make(
                $request->all(),
                array(
                    'skip' => 'required|numeric',
                    'device_type'=>'in:'.DEVICE_WEB.','.DEVICE_IOS.','.DEVICE_ANDROID,
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages);
                
            } else {

                if (!$request->has('sub_profile_id')) {

                    $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                    if ($sub_profile) {
                        
                        $sub_profile_id = $sub_profile->id;

                    } else  {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                } else  {

                    $subProfile = SubProfile::where('user_id', $request->id)
                                    ->where('id', $request->sub_profile_id)->first();

                    if (!$subProfile) {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                    $sub_profile_id = $request->sub_profile_id;
                 
                }
            

                $video_list = Helper::continue_watching_videos($sub_profile_id,$request->device_type,$request->skip);

                $videos = [];

                $user = User::find($request->id);

                if (count($video_list) > 0) {

                    foreach ($video_list as $key => $value) {
                        
                        $videos[] = displayFullDetails($value->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $sub_profile_id);

                    }
                }

               // $total = count($videos);

                $response_array = array('success' => true, 'data' => $videos);

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);

        }

    }

    /**
     * Function Name : save_continue_watching_video
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
    public function save_continue_watching_video(Request $request) {

        // If user watching the video, we shouldn't allow user get logout.

        check_token_expiry($request->id);

        try {

            $validator = Validator::make(
                $request->all(),
                array(
                    'admin_video_id' => 'required|exists:admin_videos,id',
                    'duration'=>'required',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages);
                
            } else {

                if (!$request->has('sub_profile_id')) {

                    $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                    if ($sub_profile) {
                        
                        $sub_profile_id = $sub_profile->id;

                    } else  {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                } else  {

                    $subProfile = SubProfile::where('user_id', $request->id)
                                    ->where('id', $request->sub_profile_id)->first();

                    if (!$subProfile) {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                    $sub_profile_id = $request->sub_profile_id;
                 
                }

                $model = ContinueWatchingVideo::where('sub_profile_id', $sub_profile_id)->where('admin_video_id', $request->admin_video_id)->first();

                if (!$model) {

                    $model = new ContinueWatchingVideo;

                }

                $video = AdminVideo::where('is_approved' , 1)->where('status' , 1)->where('id', $request->admin_video_id)->first();

                if ($video) {

                    $duration = "";

                    $explode_duration = explode(':', $request->duration);

                    if (count($explode_duration) == 3) {

                        $duration = $request->duration;

                    }

                    if (count($explode_duration) == 2) {

                        $duration = "00:".$explode_duration[0].':'.$explode_duration[1];

                    }

                    if (!$duration) {

                        throw new Exception(tr('duration_not_proper_format'));
                        
                    }

                    $model->user_id = $request->id;

                    $model->sub_profile_id = $sub_profile_id;

                    $model->admin_video_id = $video->id;

                    $model->status = DEFAULT_TRUE; 

                    $model->is_genre = $video->genre_id > 0 ? DEFAULT_TRUE : DEFAULT_FALSE;


                    if ($model->is_genre) {

                        $genre = Genre::where('status', DEFAULT_TRUE)->where('is_approved', DEFAULT_TRUE)->where('id', $video->genre_id)->first();

                        if (!$genre) {

                            throw new Exception(tr('genre_not_found'));
                            
                        }

                        $model->position = $video->position;

                        $model->genre_position = $genre->position;

                    } else {

                        $model->position = 0;

                        $model->genre_position = 0;
                    }

                    $model->duration = $duration;

                    $model->duration_in_seconds = 10;

                    $model->save();

                } else {

                    throw new Exception(tr('video_not_approved_by_admin'));

                }  

                $response_array = array('success' => true, 'data' => $model);

                return response()->json($response_array, 200);
            }

        } catch (Exception $e) {

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);

        }

    }

    /**
     * Function Name : onCompleteVideo 
     *
     * @uses Delete the row if it is completed based on type of video
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - User id, token, sub_profile_id & admin_video_id
     *
     * @return response of success/failure message
     */
    public function on_complete_video(Request $request) {

        try {  

            $validator = Validator::make(
                $request->all(),
                array(
                    'admin_video_id' => 'required|exists:admin_videos,id',
                )
            );

            if ($validator->fails()) {

                $error_messages = implode(',', $validator->messages()->all());

                throw new Exception($error_messages);
                
            } else {

                if (!$request->has('sub_profile_id')) {

                    $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                    if ($sub_profile) {
                        
                        $sub_profile_id = $sub_profile->id;

                    } else  {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                } else  {

                    $subProfile = SubProfile::where('user_id', $request->id)
                                    ->where('id', $request->sub_profile_id)->first();

                    if (!$subProfile) {

                        throw new Exception(tr('sub_profile_details_not_found'));
                        
                    }

                    $sub_profile_id = $request->sub_profile_id;
                 
                }

                $model = ContinueWatchingVideo::where('sub_profile_id', $sub_profile_id)
                    ->where('admin_video_id', $request->admin_video_id)->first();

                if (!$model) {

                   throw new Exception(tr('continue_watching_video_not_found'));

                }

                $video = AdminVideo::where('is_approved' , DEFAULT_TRUE)
                        ->where('status' , DEFAULT_TRUE)
                        ->where('id', $request->admin_video_id)
                        ->first();

                if ($video) {

                    if($model->is_genre) {

                        if ($video->genre_id > 0) {

                            $video_position = $model->position + 1;

                            $genre_position = $model->genre_position;

                            $available = $this->check_next_genre($video_position, $genre_position, $video, $request->sub_profile_id)->getData();
                                
                            if ($available->admin_video_id) {

                                $model->admin_video_id = $available->admin_video_id;

                                $model->position = $available->position;

                                $model->genre_position = $available->genre_position;

                                $model->duration = $available->duration;

                                $model->save();

                            } else {

                                $model->delete();

                            }

                        } else {

                            // throw new Exception(tr('genre_not_found'));

                            $model->delete();

                        }

                    } else {

                        // throw new Exception(tr('video_not_approved_by_admin'));

                        $model->delete();

                    }  

                } else {

                  // throw new Exception(tr('video_not_approved_by_admin'));

                    $model->delete();

                     
                }

                $response_array = array('success' => true);

                return response()->json($response_array, 200);
            
            }

        } catch (Exception $e) {

            $e = $e->getMessage();

            $response_array = ['success'=>false, 'error_messages'=>$e];

            return response()->json($response_array);

        }
                            
    }

    /**
     * Function Name : apply_coupon_subscription()
     *
     * @uses Apply coupon to subscription if the user having coupon codes
     *
     * @created: Shobana Chandrasekar
     *
     * @edited:
     *
     * @param object $request - User details, subscription details
     *
     * @return response of coupon details with amount
     *
     */
    public function apply_coupon_subscription(Request $request) {

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|exists:coupons,coupon_code',  
            'subscription_id'=>'required|exists:subscriptions,id'          
        ], array(
               'coupon_code.exists' => tr('coupon_code_not_exists'),
            'subscription_id.exists' => tr('subscription_not_exists'),
            ));
        
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json($response_array);
        }
        

        $model = Coupon::where('coupon_code', $request->coupon_code)->first();

        if ($model) {

            if ($model->status) {

                $user = User::find($request->id);

                $check_coupon = $this->check_coupon_applicable_to_user($user, $model)->getData();

                if ($check_coupon->success) {

                    if(strtotime($model->expiry_date) >= strtotime(date('Y-m-d'))) {

                        $subscription = Subscription::find($request->subscription_id);

                        if($subscription) {

                            if($subscription->status) {

                                $amount_convertion = $model->amount;

                                if ($model->amount_type == PERCENTAGE) {

                                    $amount_convertion = amount_convertion($model->amount, $subscription->amount);

                                }

                                if ($subscription->amount > $amount_convertion && $amount_convertion > 0) {

                                    $amount = $subscription->amount - $amount_convertion;

                                    $response_array = ['success'=> true, 'data'=>['remaining_amount'=>$amount,
                                    'coupon_amount'=>$amount_convertion,
                                    'coupon_code'=>$model->coupon_code,
                                    'original_coupon_amount'=>$model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount]];

                                } else {

                                    // $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(156), 'error_code'=>156];
                                    $amount = 0;
                                    $response_array = ['success'=> true, 'data'=>['remaining_amount'=>$amount,
                                    'coupon_amount'=>$amount_convertion,
                                    'coupon_code'=>$model->coupon_code,
                                    'original_coupon_amount'=> $model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount]];

                                }

                            } else {

                                $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(155), 'error_code'=>155];

                            }

                        } else {

                            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(154), 'error_code'=>154];
                        }

                    } else {

                        $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(159), 'error_code'=>159];

                    }

                } else {

                    $response_array = ['success'=> false, 'error_messages'=>$check_coupon->error_messages];
                }

            } else {

                $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(153), 'error_code'=>153];
            }



        } else {

            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(152), 'error_code'=>152];

        }

        return response()->json($response_array);

    }

    /**
     * Function Name : apply_coupon_ppv()
     *
     * @uses Apply coupon to PPV if the user having coupon codes
     *
     * @created: Shobana Chandrasekar
     *
     * @edited: 
     *
     * @param object $request - User details, ppv video details
     *
     * @return response of coupon details with amount
     *
     */
    public function apply_coupon_ppv(Request $request) {

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|exists:coupons,coupon_code',  
            'admin_video_id'=>'required|exists:admin_videos,id'          
        ], array(
                 'coupon_code.exists' => tr('coupon_code_not_exists'),
                'admin_video_id.exists' => tr('video_not_exists'),
            ));
        
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages'=>$error_messages , 'error_code' => 101);

            return response()->json($response_array);
        }
        

        $model = Coupon::where('coupon_code', $request->coupon_code)->first();

        if ($model) {

            if ($model->status) {

                $user = User::find($request->id);

                $video = AdminVideo::where('admin_videos.is_approved' , VIDEO_APPROVED)
                ->where('admin_videos.status' , VIDEO_PUBLISHED)->where('id',$request->admin_video_id)
                ->first();

                $check_coupon = $this->check_coupon_applicable_to_user($user, $model)->getData();

                if ($check_coupon->success) {

                    if(strtotime($model->expiry_date) >= strtotime(date('Y-m-d'))) {

                        if($video) {

                            $amount_convertion = $model->amount;

                            if ($model->amount_type == PERCENTAGE) {

                                $amount_convertion = amount_convertion($model->amount, $video->amount);

                            }

                            if ($video->amount > $amount_convertion && $amount_convertion > 0) {

                                $amount = $video->amount - $amount_convertion;

                                $response_array = ['success'=> true, 'data'=>[
                                    'remaining_amount'=>$amount,
                                    'coupon_amount'=>$amount_convertion,
                                    'coupon_code'=>$model->coupon_code,
                                    'original_coupon_amount'=> $model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount
                                    ]];

                            } else {

                               // $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(158), 'error_code'=>158];

                                $amount = $video->amount - $amount_convertion;

                                $response_array = ['success'=> true, 'data'=>[
                                    'remaining_amount'=>0,
                                    'coupon_amount'=>$amount_convertion,
                                    'coupon_code'=>$model->coupon_code,
                                    'original_coupon_amount'=> $model->amount_type == PERCENTAGE ? $model->amount.'%' : Setting::get('currency').$model->amount
                                    ]];

                            }

                        } else {

                            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(157), 'error_code'=>157];
                        }

                    } else {

                        $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(159), 'error_code'=>159];

                    }

                } else {

                    $response_array = ['success'=> false, 'error_messages'=>$check_coupon->error_messages];

                }

            } else {

                $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(153), 'error_code'=>153];
            }            

        } else {

            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(152), 'error_code'=>152];

        }

        return response()->json($response_array);

    }

    /**
     * Function Name : autorenewal_cancel
     *
     * @uses To prevent automatic subscriptioon, user have option to cancel subscription
     *
     * @created Shobana C
     *
     * @edited Vidhya R
     *
     * @param object $request - USer details & payment details
     *
     * @return boolean response with message
     */
    public function autorenewal_cancel(Request $request) {

        $user_payment = UserPayment::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->orderBy('created_at', 'desc')->first();

        if($user_payment) {

            // Check the subscription is already cancelled

            if($user_payment->is_cancelled == AUTORENEWAL_CANCELLED) {

                $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(164) , 'error_code' => 164];

                return response()->json($response_array , 200);

            }

            $user_payment->is_cancelled = AUTORENEWAL_CANCELLED;

            $user_payment->cancel_reason = $request->cancel_reason;

            $user_payment->save();

            $response_array = ['success'=> true, 'message'=>tr('cancel_subscription_success')];

        } else {

            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(163), 'error_code'=>163];

        }

        return response()->json($response_array);

    }


    /**
     * Function Name : check_next_genre()
     *
     * @uses To check next genre available or not
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - User id, token , genre_id and etc
     *
     * @return response of Boolean 
     */
    public function check_next_genre($video_position, $genre_position, $video, $sub_profile_id) {

        $continous_watching_videos = continueWatchingVideos($sub_profile_id);

        $next_video = AdminVideo::where('is_approved' , DEFAULT_TRUE)
                    ->where('status' , DEFAULT_TRUE)
                    ->where('genre_id', $video->genre_id)
                    ->where('position', $video_position)
                    ->first();

        if (!$next_video) {

            $genre_position += 1;

            $next_genre = Genre::select('admin_videos.id as admin_video_id',
                        'genres.position as genre_position',
                        'admin_videos.position as video_position')->where('genres.status', DEFAULT_TRUE)
                        ->where('genres.is_approved', DEFAULT_TRUE)
                        ->leftJoin('admin_videos' , 'admin_videos.genre_id' , '=' , 'genres.id')
                        ->where('genres.sub_category_id', $video->sub_category_id)
                        ->where('genres.position', $genre_position)
                        ->where('admin_videos.status', DEFAULT_TRUE)
                        ->where('admin_videos.is_approved', DEFAULT_TRUE)
                        ->havingRaw("COUNT(admin_videos.id) > 0")
                        ->orderBy("admin_videos.position", 'asc')
                        ->whereNotIn('admin_videos.id', $continous_watching_videos)
                        ->first();

            if ($next_genre) {

                $response_array = ['admin_video_id' => $next_genre->admin_video_id,
                    'position'=>$next_genre->video_position,
                    'genre_position'=>$next_genre->genre_position,
                    'duration' => "00:00:00"];

            } else {

                $response_array = ['admin_video_id'=>''];

            }

        } else {

            if (!in_array($next_video->id, $continous_watching_videos)) {

                $response_array = ['admin_video_id' => $next_video->id,
                        'position'=>$next_video->position,
                        'genre_position'=>$genre_position,
                        'duration' => "00:00:00"];

            } else {

                $response_array = ['admin_video_id'=>''];
                
            }
        }


        return response()->json($response_array);

    }

    /**
     * Function Name : email_notification()
     *
     * @uses To enable/disable email notifications
     *
     * @created : 
     *
     * @edited : 
     *
     * @param object $request - User id, token & Email Notification status
     *
     * @return response of success/failure message
     */
    public function email_notification(Request $request) {

        $user = User::find($request->id);

        if($user) {

            $user->email_notification = $request->notification;

            if ($user->save()) {

                $message = $user->email_notification ? tr('notification_will_update') : tr('notification_will_not_update');

                $response_array = ['success'=>true, 'message'=>$message];

            } else {

                $response_array = ['success'=>false, 'error_messages'=>tr('user_details_not_save')];
            }

        } else {

            $response_array = ['success'=>false, 'error_messages'=>tr('no_user_detail_found')];
        }

        return response()->json($response_array);
    
    }
                      
    /**
     * Function Name : genres_videos()
     *
     * @uses - Andriod & IOS - To list out the genres title list and first genre videos list.
     *
     * @created : 
     *
     * @edited : 
     *
     * @param Object $request - User id, token and so on
     *
     * @return respone of json array
     */
    public function genres_videos(Request $request) {

        Log::info("genres_videos".print_r($request->all() , true));

        $validator = Validator::make($request->all(), [
            'genre_id'=>'required|exists:genres,id',
            'sub_category_id'=>'required|exists:sub_categories,id',
        ]);

        if ($validator->fails()) {

            $errors = implode(',', $validator->messages()->all());

            $response_array = ['success' => false , 'error_messages' => $errors];

            return response()->json($response_array);
        }


        $genrenames = Genre::where('sub_category_id' , $request->sub_category_id)
                        ->select(
                                'genres.id as genre_id',
                                'genres.name as genre_name',
                                'genres.image'
                                )
                        ->orderBy('genres.updated_at', 'desc')
                        ->where('is_approved', DEFAULT_TRUE)
                        ->get();

        $genre_names = [];

        foreach ($genrenames as $key => $genre) {

            $genre_names[] = ['genre_name'=>$genre->genre_name, 'genre_id'=>$genre->genre_id];

        }

        $videos_query = AdminVideo::where('genre_id', $request->genre_id)
                            // ->whereNotIn('admin_videos.genre_id', [$video->id])
                            ->where('admin_videos.status' , 1)
                            ->where('admin_videos.is_approved' , 1)
                            ->orderBy('admin_videos.created_at', 'desc');
                           

        if ($request->sub_profile_id) {
            // Check any flagged videos are present
            $flagVideos = getFlagVideos($request->sub_profile_id);

            if($flagVideos) {
                $videos_query->whereNotIn('admin_videos.id',$flagVideos);
            }
        }


        if($request->admin_video_id) {

            $videos_query->whereNotIn('admin_videos.id', [$request->admin_video_id]);

        }

        if($request->device_type == DEVICE_WEB) {

            // Check any flagged videos are present
            $continue_watching_videos = continueWatchingVideos($request->sub_profile_id);
            
            if($continue_watching_videos) {

                $videos_query->whereNotIn('admin_videos.id', $continue_watching_videos);

            }

            $seasons = $videos_query->paginate(12);

        } else {

            $seasons = $videos_query->skip($request->skip)->take(Setting::get('admin_take_count'))
                        ->get();
        }
        
        $model = [];
        
        if(!empty($seasons) && $seasons != null) {

            foreach ($seasons as $key => $value) {

                $model[] = [
                        'title'=>$value->title,
                        'description'=>$value->description,
                        'ratings'=>$value->ratings,
                        'publish_time'=>date('F j y', strtotime($value->publish_time)),
                        'duration'=>$value->duration,
                        'watch_count'=>$value->watch_count,
                        'default_image'=>$value->default_image,
                        'admin_video_id'=>$value->id,
                    ];
            }
        }

        $response_array = ['success' => true , 'data' => $model, 'genres'=>$genre_names];

        return response()->json($response_array);

    }


   /**
     * Function Name : autorenewal_enable
     *
     * @uses To prevent automatic subscriptioon, user have option to cancel subscription
     *
     * @created Shobana C
     *
     * @edited Vidhya R
     *
     * @param object $request - USer details & payment details
     *
     * @return boolean response with message
     */
    public function autorenewal_enable(Request $request) {

        $user_payment = UserPayment::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->orderBy('created_at', 'desc')->first();


        if($user_payment) {

        // Check the subscription is already cancelled

            if($user_payment->is_cancelled == AUTORENEWAL_ENABLED) {
        
                $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(165) , 'error_code' => 165];

                return response()->json($response_array , 200);
            
            }

            $user_payment->is_cancelled = AUTORENEWAL_ENABLED;
          
            $user_payment->save();

            $response_array = ['success'=> true, 'message'=> Helper::get_message(122) , 'code' => 122];

        } else {

            $response_array = ['success'=> false, 'error_messages'=>Helper::get_error_message(163), 'error_code'=>163];

        }

        return response()->json($response_array);

    }

   /**
    * Function Name : cast_crews_videos()
    *
    * @uses To load videos based on cast & crews
    *
    * @created_by Shobana Chandrasekar
    *
    * @edited_by
    *
    * @param object $request - user & crews details
    *
    * @return response of json details
    */
   public function cast_crews_videos(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'skip' => 'required|numeric',
                'take'=> $request->has('take') ? 'required|numeric' : 'numeric',
                'cast_crew_id'=>'required|exists:cast_crews,id'
            ),[

                'cast_crew_id.exists'=>tr('cast_crew_not_found')

            ]
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return response()->json(['success'=>false, 'error_messages'=>$error_messages]);
            
        } else {

            if (!$request->has('take')) {

                $request->request->add(['take' => Setting::get('admin_take_count')]);

            }

            if (!$request->has('sub_profile_id')) {

                $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                if ($sub_profile) {

                    $request->request->add([ 

                        'sub_profile_id' => $sub_profile->id,

                    ]);

                    $id = $sub_profile->id;

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];


                    return response()->json($response_array , 200);

                }

            } else {

                $subProfile = SubProfile::where('user_id', $request->id)
                            ->where('id', $request->sub_profile_id)->first();

                if (!$subProfile) {

                    $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];
                    
                    return response()->json($response_array , 200);
                    
                } else {

                    $id = $subProfile->id;

                }

            } 

            $cast = CastCrew::find($request->cast_crew_id);

            $user = User::find($request->id);

            $video_cast_crews = VideoCastCrew::where('cast_crew_id', $request->cast_crew_id)->get()->pluck('admin_video_id')->toArray();

            $videos_query = AdminVideo::where('admin_videos.is_approved' , VIDEO_APPROVED)
                            ->leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
                            ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
                            ->leftJoin('genres' , 'admin_videos.genre_id' , '=' , 'genres.id')
                            ->whereIn('admin_videos.id', $video_cast_crews)
                            ->where('admin_videos.status' , VIDEO_PUBLISHED)
                            ->whereNotIn('admin_videos.is_banner',[BANNER_VIDEO])
                            ->videoResponse()
                            ->orderby('admin_videos.created_at' , 'desc');
                            
            if ($request->sub_profile_id) {
                // Check any flagged videos are present
                $flagVideos = getFlagVideos($request->sub_profile_id);

                if($flagVideos) {
                    $videos_query->whereNotIn('admin_videos.id',$flagVideos);
                }
            }


            // Check any flagged videos are present
            $continue_watching_videos = continueWatchingVideos($request->sub_profile_id);
            
            if($continue_watching_videos) {

                $videos_query->whereNotIn('admin_videos.id', $continue_watching_videos);

            }

            
            $videos = $videos_query->skip($request->skip)->take(Setting::get('admin_take_count'))
                            ->get();
        

            if ($request->device_type == DEVICE_WEB) {

                $chunk = $videos->chunk(4);

                $datas = [];

                foreach ($chunk as $key => $value) {

                    $group = [];

                    foreach ($value as $key => $data) {
                     
                        $group[] = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                    }

                    $datas[] = $group;

                }

            } else {


                $datas = [];

                foreach ($videos as $key => $data) {
                 
                    $datas[] = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                }


            }
           

            return response()->json(['success'=>true, 'data'=>$datas, 'cast'=>$cast]);

        }

   }

   /**
    * Function Name : kids_videos()
    *
    * @uses To load only kids section videos 
    *
    * @created_by Shobana Chandrasekar
    *
    * @edited_by
    *
    * @param object $request - user & kids details
    *
    * @return response of json details
    */
   public function kids_videos(Request $request) {

            $validator = Validator::make(
            $request->all(),
            array(
                'skip' => 'required|numeric',
                'take'=> $request->has('take') ? 'required|numeric' : 'numeric',
            )
        );

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            return response()->json(['success'=>false, 'error_messages'=>$error_messages]);
            
        } else {

            if (!$request->has('take')) {

                $request->request->add(['take' => Setting::get('admin_take_count')]);

            }

            if (!$request->has('sub_profile_id')) {

                $sub_profile = SubProfile::where('user_id', $request->id)->where('status', DEFAULT_TRUE)->first();

                if ($sub_profile) {

                    $request->request->add([ 

                        'sub_profile_id' => $sub_profile->id,

                    ]);

                    $id = $sub_profile->id;

                } else {

                    $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];


                    return response()->json($response_array , 200);

                }

            } else {

                $subProfile = SubProfile::where('user_id', $request->id)
                            ->where('id', $request->sub_profile_id)->first();

                if (!$subProfile) {

                    $response_array = ['success'=>false, 'error_messages'=>tr('sub_profile_details_not_found')];
                    
                    return response()->json($response_array , 200);
                    
                } else {

                    $id = $subProfile->id;

                }

            } 


            $videos_query = AdminVideo::where('admin_videos.is_approved' , VIDEO_APPROVED)
                            ->leftJoin('categories' , 'admin_videos.category_id' , '=' , 'categories.id')
                            ->leftJoin('sub_categories' , 'admin_videos.sub_category_id' , '=' , 'sub_categories.id')
                            ->leftJoin('genres' , 'admin_videos.genre_id' , '=' , 'genres.id')
                            ->where('admin_videos.status' , VIDEO_PUBLISHED)
                            ->where('categories.is_approved', CATEGORY_APPROVED)
                            ->where('sub_categories.is_approved', SUB_CATEGORY_APPROVED)
                            ->where('admin_videos.is_kids_video',KIDS_SECTION_YES)
                            ->videoResponse()
                            ->orderby('admin_videos.created_at' , 'desc');
                            
            if ($request->sub_profile_id) {
                // Check any flagged videos are present
                $flagVideos = getFlagVideos($request->sub_profile_id);

                if($flagVideos) {
                    $videos_query->whereNotIn('admin_videos.id',$flagVideos);
                }
            }


            // Check any flagged videos are present
            $continue_watching_videos = continueWatchingVideos($request->sub_profile_id);
            
            if($continue_watching_videos) {

                $videos_query->whereNotIn('admin_videos.id', $continue_watching_videos);

            }

            
            $videos = $videos_query->skip($request->skip)->take(Setting::get('admin_take_count'))
                            ->get();

            $user = User::find($request->id);
        

            if ($request->device_type == DEVICE_WEB) {

                $chunk = $videos->chunk(4);

                $datas = [];

                foreach ($chunk as $key => $value) {

                    $group = [];

                    foreach ($value as $key => $data) {
                     
                        $display_details = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                        if ($display_details) {
 
                            $group[] = $display_details;

                        }

                    }

                    $datas[] = $group;

                }

            } else {


                $datas = [];

                foreach ($videos as $key => $data) {
                 
                    $display_details = displayFullDetails($data->admin_video_id, $user ? $user->id : 0, $user ? $user->user_type : 0, $request->device_type, $id);

                    if ($display_details) {

                        $datas[] = $display_details;

                    }


                }


            }
           

            return response()->json(['success'=>true, 'data'=>$datas]);

        }


   }


    /**
     * Function Name : video_dowload_status()
     *
     * To update the video status while downloading
     * 
     * @created by - shobana
     *
     * @updated by - shobana
     *
     * @param object $request - User details
     *
     * @return response of details
     */
    public function video_dowload_status(Request $request) {

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
                    'admin_video_id' => 'required|exists:admin_videos,id,status,'.APPROVED,
                    'status'=>'required|in:'.DOWNLOAD_INITIATE_STAUTS.','.DOWNLOAD_PROGRESSING_STAUTS.','.DOWNLOAD_PAUSE_STAUTS.','.DOWNLOAD_COMPLETE_STAUTS.','.DOWNLOAD_CANCEL_STAUTS.','.DOWNLOAD_DELETE_STAUTS
                )
            );
        
            if ($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);
            
            } else {

                $download =  OfflineAdminVideo::where('admin_video_id', $request->admin_video_id)
                        ->where('user_id', $request->id)
                        ->first();

                if ($request->status == DOWNLOAD_DELETE_STAUTS) {

                    if($download) {

                        $download->delete();

                        return $response_array = ['success'=>true, 'code'=>200, 'messages'=>tr('download_deleted')];

                    } else {

                        throw new Exception(tr('download_error'));
                        
                    }

                }

                if (!$download) {

                    $download = new OfflineAdminVideo;

                }

                $download->user_id = $request->id;

                $download->download_status = $request->status;

                $download->admin_video_id = $request->admin_video_id;

                if ($download->download_status == DOWNLOAD_COMPLETE_STAUTS) {

                    $download->status = YES;

                } else {

                    $download->status = NO;

                }

                if ($download->save()) {

                    switch ($download->download_status) {

                        case DOWNLOAD_INITIATE_STAUTS:
                            
                            $message = tr('download_initiated');

                            break;
                        case DOWNLOAD_PROGRESSING_STAUTS:
                            
                            $message = tr('download_progressing');

                            break;

                        case DOWNLOAD_PAUSE_STAUTS:
                            
                            $message = tr('download_paused');

                            break;

                        case DOWNLOAD_COMPLETE_STAUTS:
                            
                            $message = tr('download_completed');

                            $download->download_date = date('Y-m-d'); // Current Date

                            $expiry_days = Setting::get('download_video_expiry_days');

                            $download->expiry_date = date('Y-m-d', strtotime("+{$expiry_days} days"));

                            $download->save();

                            break;

                        case DOWNLOAD_CANCEL_STAUTS:
                            
                            $message = tr('download_cancelled');

                            break;

                        case DOWNLOAD_DELETE_STAUTS:
                            
                            $message = tr('download_deleted');

                            break;
                        
                        default:
                            
                            $message = tr('download_status_updated');

                            break;

                    }

                    $response_array = ['success'=>true, 'code'=>200, 'messages'=>$message];

                } else {

                    throw new Exception(tr('download_error'), 101);
                    
                }
                
            }

            return response()->json($response_array);

        } catch(Exception $e) {

            $error = $e->getMessage();

            $error_code = $e->getCode();

            $response_array = ['success' => false , 'error' => $error , 'error_code' => $error_code];

            return response()->json($response_array , 200);
        }

    }

   /**
    * Function Name : downloaded_videos()
    *
    * @uses To list out offline videos based on users
    *
    * @created_by Shobana Chandrasekar
    *
    * @edited_by
    *
    * @param object $request - user & video details
    *
    * @return response of json details
    */
   public function downloaded_videos(Request $request) {

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

                $model = OfflineAdminVideo::where('user_id', $request->id)
                                ->skip($request->skip)
                                ->take(Setting::get('admin_take_count'))
                                ->get();

                $data = [];

                foreach ($model as $key => $value) {

                    $data[] = mobileResponseDetails($value->admin_video_id, $value->user_id, $request->sub_profile_id);

                }

            }

            $response_array = array('success' => true, 'data'=>$data);

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            $response_array = ['success'=>false, 'error_messages'=>$e->getMessage(), 'error_code'=>$e->getCode()];

            return response()->json($response_array);

        }

   }


}