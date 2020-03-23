<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Socialite;
use App\User;
use Hash;
use Log;
use App\Helpers\Helper;

use App\SubProfile;

use Setting;

class SocialAuthController extends Controller
{
    public function redirect(Request $request)
    {
        return Socialite::driver($request->provider)->redirect();
    }

    public function callback(Request $request ,$provider)
	{

		if($provider == "twitter") {
    		
    		if($request->has('denied')) {
		    	
		    	return redirect()->away(Setting::get('ANGULAR_SITE_URL'));

    		}

    	} else {

	    	if(!$request->has('code') || $request->has('denied')) {

			    return redirect()->away(Setting::get('ANGULAR_SITE_URL'));
			}

		}

		session()->put('state', $request->input('state'));

		$social_user = \Socialite::driver($provider)->user();

		if($social_user) {
			
			Log::info("Check Socialite START");

			$user = User::where('social_unique_id' , $social_user->id)->where('login_by' , $provider)->first();


			if(!count($user)) {

				Log::info("Check Socialite - USER CREATE");

				$user = new User;
				$user->social_unique_id = $social_user->id;
				$user->login_by = $provider;

				if($social_user->name) {
					$user->name = $social_user->name;
				} else {
					$user->name = "Dummy";
				}

				if($social_user->email && !User::where('email',$social_user->email)->first()) {
					$user->email = $social_user->email;
				} else {
					$user->email = "social".uniqid().Setting::get('social_email_suffix', '@streamhash.com');
				}

				// Save Dummy details
				$user->picture =  asset('placeholder.png');

				if(in_array($provider, array('facebook','twitter'))) {
					if($social_user->avatar_original) {
						$user->picture = $social_user->avatar_original;
					}
				}

				$user->is_activated = DEFAULT_TRUE;
				$user->password = Hash::make($social_user->id);
	            $user->token = Helper::generate_token();
	            $user->token_expiry = Helper::generate_token_expiry();
				$user->save();


                $sub_profile = new SubProfile;

                $sub_profile->user_id = $user->id;

                $sub_profile->name = $user->name;

                $sub_profile->picture = $user->picture;

                $sub_profile->status = DEFAULT_TRUE;

                $sub_profile->save();

                $sub_profile_id = $sub_profile->id;

			} else {
				Log::info("Check Socialite - USER CREATE ELSE PORTION");

			}

			if (count($user) > 0) {

				if ($user->is_activated) {

	            } else {

	                // throw new Exception(tr('user_login_decline'));

	                return redirect()->away(Setting::get('ANGULAR_SITE_URL')."social_login_failure");
	            
	            }

				$user->token_expiry = Helper::generate_token_expiry();

				if ($user->save()) {

					$sub_profile = SubProfile::where('user_id', $user->id)->where('status', DEFAULT_TRUE)->first();

					if (!$sub_profile) {

						$sub_profile = new SubProfile;

		                $sub_profile->user_id = $user->id;

		                $sub_profile->name = $user->name;

		                $sub_profile->picture = $user->picture;

		                $sub_profile->status = DEFAULT_TRUE;

		                $sub_profile->save();

		                $sub_profile_id = $sub_profile->id;

					}

				}
			}

			Log::info("Check Socialite END");

		    return redirect()->away(Setting::get('ANGULAR_SITE_URL')."social_login/".$user->id.'/'.$user->token);

		} else {

			return redirect()->away(Setting::get('ANGULAR_SITE_URL')."signin");

		}

	}
}
