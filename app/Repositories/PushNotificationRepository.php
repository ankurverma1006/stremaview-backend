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

use Validator;

use Hash;

use Log;

use Setting;

use App\AdminVideo;

use DB;

use Exception;

use Auth;

use File;

use LaravelFCM\Message\OptionsBuilder;

use LaravelFCM\Message\PayloadDataBuilder;

use LaravelFCM\Message\PayloadNotificationBuilder;

use FCM;

use App\User;

class PushNotificationRepository {

	/**
 	 * Function Name : push_notification_android
 	 *
 	 * @uses To check the status of the pay per view in each video
 	 *
 	 * @created - Vidhya R
 	 * 
 	 * @updated - Vidhya R
 	 *
 	 * @param object $request
 	 *
 	 * @return 
 	 */

	public static function send_push_notification($push_notification_type , $title , $message , $data = [] , $user_id = 0) {

		Log::info("send_push_notification".$push_notification_type);

		if(Setting::get('is_push_notification') == ON) {

			if($push_notification_type == PUSH_TO_ALL) {

				$android_register_ids = User::where('status' , USER_APPROVED)->where('device_token' , '!=' , "")->where('device_type' , DEVICE_ANDROID)->where('push_status' , ON)->pluck('device_token')->toArray();

	            self::push_notification_android($android_register_ids , $title , $message , $data);

	            $ios_register_ids = User::where('status' , USER_APPROVED)->where('login_by' , 'DEVICE_IOS')->where('push_status' , ON)->select('device_token')->get()->toArray();

	            // PushRepo::push_notification_android($ios_register_ids , $title , $message);

			}

		} else {

			Log::info("Push notification disabled by Admin");
			
		}
 	
 	}

	/**
 	 * Function Name : push_notification_android
 	 *
 	 * @uses To check the status of the pay per view in each video
 	 *
 	 * @created - Vidhya R
 	 * 
 	 * @updated - Vidhya R
 	 *
 	 * @param object $request
 	 *
 	 * @return 
 	 */

 	public static function push_notification_android($register_ids , $title , $message , $data = []) {

 		if(envfile('FCM_SERVER_KEY') && envfile('FCM_SENDER_ID') && Setting::get('is_push_notification') == ON) {

			Log::info("push_notification_android".$title);

	 		// Check the register ids 

	 		if(!$register_ids || !$title || !$message) {

	 			return false;

	 		}

	 		$optionBuilder = new OptionsBuilder();

			$optionBuilder->setTimeToLive(60*20);

			$notificationBuilder = new PayloadNotificationBuilder($title);

			$notificationBuilder->setBody($message)->setSound('default');

			$dataBuilder = new PayloadDataBuilder();

			// $dataBuilder->addData();
			$dataBuilder->addData($data);

			$option = $optionBuilder->build();
			$notification = $notificationBuilder->build();
			$data = $dataBuilder->build();

			$token = $register_ids;

			$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

			$downstreamResponse->numberSuccess();

			$downstreamResponse->numberFailure();

			$downstreamResponse->numberModification();

			//return Array - you must remove all this tokens in your database
			$downstreamResponse->tokensToDelete();

			//return Array (key : oldToken, value : new token - you must change the token in your database )
			$downstreamResponse->tokensToModify();

			//return Array - you should try to resend the message to the tokens in the array
			$downstreamResponse->tokensToRetry();

			// return Array (key:token, value:errror) - in production you should remove from your database the tokens

			// Log::info("downstreamResponse".print_r($downstreamResponse , true));

			return true;

		} else {

			return false;
		}
 	
 	}

 	/**
 	 * Function Name : push_notification_ios
 	 *
 	 * @uses To check the status of the pay per view in each video
 	 *
 	 * @created - Vidhya R
 	 * 
 	 * @updated - Vidhya R
 	 *
 	 * @param object $request
 	 *
 	 * @return 
 	 */

 	public static function push_notification_ios($register_ids , $title , $message , $data = []) {

 		if(Setting::get('is_push_notification') == ON) {

	 		// Check the register ids 

	 		if(!$register_ids || !$title || !$message) {

	 			return false;

	 		}

	 		foreach($register_ids as $register_id) {
	           
	           Helper::send_notification($register_id,$title,$message , $data);

	 		}

			return true;

	 	} else {

	 		return false;
	 	}

 	
 	}
}