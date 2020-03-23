<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

use App\Helpers\Helper;

use Setting;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected $redirectAfterLogout = '/login';

    /**
     * The Login form view that should be used.
     *
     * @var string
     */

    protected $loginView = 'user.auth.login';

    /**
     * The Register form view that should be used.
     *
     * @var string
     */

    protected $registerView = 'user.auth.register';


    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {        
        $User = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'timezone' => $data['timezone'],
            'picture' => asset('placeholder.png'),
            'is_activated' => 1,
            'login_by' => 'manual',
            'device_type' => 'web',
        ]);

        // Check the default subscription and save the user type 

        user_type_check($User->id);

        register_mobile('web');

        if(!Setting::get('email_verify_control')) {

            $User->is_verified = 1;

            $User->save();
        }
        
        // Send welcome email to the new user:
        $subject = tr('user_welcome_title');
        $email_data = $User;
        $page = "emails.welcome";
        $email = $data['email'];
        $result = Helper::send_email($page,$subject,$email,$email_data);
        
        return $User;
    }


    protected function authenticated(Request $request, User $user) {

        if(\Auth::check()) {

            if($user = User::find(\Auth::user()->id)) {

                // Check Admin Enabled the email verification

                if(Setting::get('email_verify_control')) {

                    if(!$user->is_verified) {

                        \Auth::logout();

                        // Check the verification code expiry

                        Helper::check_email_verification("" , $user->id, $error);

                        return redirect(route('user.login.form'))->with('flash_error', tr('email_verify_alert'));

                    }

                }

                $user->login_by = 'manual';
                $user->timezone = $request->has('timezone') ? $request->timezone : '';
                $user->save();
            }   
        };
       return redirect()->intended($this->redirectPath());
    }

}
