<?php

/**************************************************
* Repository Name: PaymentRepository
*
* Purpose: This repository used to do all functions related payments.
*
* @author: vidhyar2612
*
* Date Created: 23 Dec 2017
**************************************************/

namespace App\Repositories;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use Validator;

use Hash;

use Log;

use Setting;

use Session;

use App\User;

use App\UserPayment;

use App\AdminVideo;

use App\PayPerView;

use App\Subscription;

use App\Coupon;

use App\UserCoupon;

class PaymentRepository {

    /**
     * @uses to store the payment failure
     *
     * @param $user_id
     *
     * @param $subscription_id
     *
     * @param $reason
     *
     * @param $payment_id = After payment - if any configuration failture or timeout
     *
     * @return boolean response
     */

    public static function subscription_payment_failure_save($user_id = 0 , $subscription_id = 0 , $reason = "" , $payment_id = "") {

        /*********** DON't REMOVE LOGS **************/

        // Log::info("1- Subscription ID".$subscription_id);

        // Log::info("2- USER ID".$user_id);
        
        // Log::info("3- MESSAGE ID".$reason);

        // Check the user_id and subscription id not null

        /************ AFTER user paid, if any configuration failture *******/

        if($payment_id) {

            $user_payment_details = UserPayment::where('payment_id',$payment_id)->first();

            $user_payment_details->reason = "After_Payment"." - ".$reason;

            $user_payment_details->save();

            return true;

        }

        /************ Before user payment, if any configuration failture or TimeOut *******/

        if(!$user_id || !$subscription_id) {

            Log::info('Payment failure save - USER ID and Subscription ID not found');

            return false;

        }

        // Get the user payment details

        $user_payment = new UserPayment();

        $user_payment->expiry_date = date('Y-m-d H:i:s');

        $user_payment->payment_id  = "Payment-Failed";
        
        $user_payment->user_id = $user_id;
        
        $user_payment->subscription_id = $subscription_id;
        
        $user_payment->status = 0;

        $user_payment->reason = $reason ? $reason : "";

        $user_payment->save();

        return true;
        

    }

    /**
     * @uses to store the PPV payment failure
     *
     * @param $user_id
     *
     * @param $admin_video_id
     *
     * @param $payment_id
     *
     * @param $reason
     *
     * @param $payment_id = After payment - if any configuration failture or timeout
     *
     * @return boolean response
     */

	public static function ppv_payment_failure_save($user_id = 0 , $admin_video_id = 0 , $reason = "" , $payment_id = "") {

        /*********** DON't REMOVE LOGS **************/

        // Log::info("1- Subscription ID".$subscription_id);

        // Log::info("2- USER ID".$user_id);
        
        // Log::info("3- MESSAGE ID".$reason);

	    // Check the user_id and subscription id not null

        /************ AFTER user paid, if any configuration failture  or timeout *******/

        if($payment_id) {

            $ppv_payment_details = PayPerView::where('payment_id',$payment_id)->first();

            $ppv_payment_details->reason = "After_Payment"." - ".$reason;

            $ppv_payment_details->save();

            return true;

        }

        /************ Before user payment, if any configuration failture or TimeOut *******/

        if(!$user_id || !$admin_video_id) {

            Log::info('Payment failure save - USER ID and Subscription ID not found');

            return false;

        }


        $ppv_user_payment_details = new PayPerView;

        $ppv_user_payment_details->expiry_date = date('Y-m-d H:i:s');

        $ppv_user_payment_details->payment_id  = "Payment-Failed";

        $ppv_user_payment_details->user_id = $user_id;

        $ppv_user_payment_details->video_id = $admin_video_id;

        $ppv_user_payment_details->reason = "BEFORE-".$reason;

        // @todo 

        

        $ppv_user_payment_details->save();

        return true;
	    

	}

    /**
     * @uses to store the payment with commission split 
     *
     * @param $admin_video_id
     *
     * @param $payperview_id
     *
     * @param $moderator_id
     * 
     * @return boolean response
     */

    public static function ppv_commission_split($admin_video_id = "" , $payperview_id = "" , $moderator_id = "") {

        if(!$admin_video_id || !$payperview_id || !$moderator_id) {

            return false;
        }

        /***************************************************
         *
         * commission details need to update in following sections 
         *
         * admin_videos table - how much earnings for particular video
         *
         * pay_per_views - On Payment how much commission has calculated 
         *
         * Moderator - If video uploaded_by moderator means need add commission amount to their redeems
         *
         ***************************************************/

        // Get the details

        $admin_video_details = AdminVideo::find($admin_video_id);

        if(count($admin_video_details) == 0 ) {

            Log::info('ppv_commission_split - AdminVideo Not Found');

            return false;
        }

        $ppv_details = PayPerView::find($payperview_id);

        if(count($ppv_details) == 0 ) {

            Log::info('ppv_commission_split - PayPerView Not Found');

            return false;

        }

        $total = $admin_amount = $ppv_details->amount;

        $moderator_amount = 0;

        // Do commission split for moderator videos, otherwise the amount will go only admin 

        if(is_numeric($admin_video_details->uploaded_by)) {

            // Commission split 
            
            $admin_commission = Setting::get('admin_commission')/100;

            $admin_amount = $total * $admin_commission;

            $moderator_amount = $total - $admin_amount;

        }
        // Update video earnings

        $admin_video_details->admin_amount = $admin_video_details->admin_amount + $admin_amount;

        $admin_video_details->user_amount = $admin_video_details->user_amount+$moderator_amount;

        $admin_video_details->save();

        // Update PPV Details

        if($ppv_details = PayPerView::find($payperview_id)) {

            $ppv_details->currency = Setting::get('currency');

            $ppv_details->admin_amount = $admin_amount;

            $ppv_details->moderator_amount = $moderator_amount;

            $ppv_details->save();
        
        }

        // Check the video uploaded by moderator or admin (uploaded_by = admin , uploaded_by = moderator ID)

        if(is_numeric($admin_video_details->uploaded_by)) {

            add_to_redeem($admin_video_details->uploaded_by , $moderator_amount , $admin_amount);

        } else {

            Log::info("No Redeems - ");
        }

        return true;

    }

    public static function check_coupon_code($request, $user_details, $original_total) {

        $coupon_amount = 0; $coupon_reason = ""; $total = $original_total; $is_coupon_applied =COUPON_NOT_APPLIED;

        $coupon_details = Coupon::where('coupon_code', $request->coupon_code)->first();

        if (!$coupon_details) {

            $coupon_reason = tr('coupon_delete_reason');

            goto couponend;
        
        }

        if ($coupon_details->status == COUPON_INACTIVE) {

            $coupon_reason = tr('coupon_inactive_reason');

            goto couponend;

        }

        $check_coupon = self::check_coupon_applicable_to_user($user_details, $coupon_details)->getData();

        if($check_coupon->success == false) {

            $coupon_reason = $check_coupon->error_messages;

            goto couponend;

        }

        $is_coupon_applied = COUPON_APPLIED;

        $converted_coupon_amount = $coupon_details->amount;

        // $original_total = ""; // Either subscription or PPV

        if ($coupon_details->amount_type == PERCENTAGE) {

            $converted_coupon_amount = amount_convertion($coupon_details->amount, $original_total);

        }

        // If the Module amount less than coupon amount , then substract the amount.

        if ($converted_coupon_amount < $original_total) {

            $total = $original_total - $converted_coupon_amount;

            $coupon_amount = $converted_coupon_amount;

        } else {

            // If the coupon amount greater than Module amount, then assign to zero.

            $total = 0;

            $coupon_amount = $converted_coupon_amount;
            
        }

        if($check_coupon->code == 2002) {

            $user_coupon = UserCoupon::where('user_id', $user_details->id)->where('coupon_code', $request->coupon_code)->first();

            // If user coupon not exists, create a new row

            if ($user_coupon) {

                if ($user_coupon->no_of_times_used < $coupon_details->per_users_limit) {

                    $user_coupon->no_of_times_used += 1;

                    $user_coupon->save();

                }

            }

        } else {

            $user_coupon = new UserCoupon;

            $user_coupon->user_id = $user_details->id;

            $user_coupon->coupon_code = $request->coupon_code;

            $user_coupon->no_of_times_used = 1;

            $user_coupon->save();

        }

        couponend:

        $data = ['coupon_amount' => $coupon_amount, 'coupon_reason' => $coupon_reason, 'total' => $total, 'is_coupon_applied' => $is_coupon_applied];

        return $data;
        
    }

    /**
     * @method check_coupon_applicable_to_user()
     *
     * @uses To check the coupon code applicable to the user or not
     *
     * @created vithya
     *
     * @updated
     *
     * @param objects $coupon - Coupon details
     *
     * @param objects $user - User details
     *
     * @return response of success/failure message
     */
    public static function check_coupon_applicable_to_user($user, $coupon) {

        try {

            $no_of_times_used = UserCoupon::where('coupon_code', $coupon->coupon_code)->sum('no_of_times_used');

            if ($no_of_times_used < $coupon->no_of_users_limit) {

            } else {

                throw new Exception(tr('total_no_of_users_maximum_limit_reached'), 101);
                
            }

            $user_coupon = UserCoupon::where('user_id', $user->id)->where('coupon_code', $coupon->coupon_code)->first();

            // If user coupon not exists, create a new row

            if (!$user_coupon) {

                $response_array = ['success' => true, 'message' => tr('create_a_new_coupon_row'), 'code' => 2001];

                return response()->json($response_array);

            }

            if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                $response_array = ['success' => true, 'message' => tr('add_no_of_times_used_coupon'), 'code'=>2002];

            } else {

                throw new Exception(tr('per_users_limit_exceed'), 101);
            }


            return response()->json($response_array);

        } catch (Exception $e) {

            $response_array = ['success' => false, 'error_messages' => $e->getMessage(), 'error_code' => $e->getCode()];

            return response()->json($response_array);
        }

    }

    /**
     * @method subscriptions_payment_save()
     *
     * @uses subscription payment record update
     *
     * @created vithya
     *
     * @updated
     *
     * @param objects $subscription_details
     *
     * @param objects $user_details
     *
     * @return response of success/failure message
     */
    
    public static function subscriptions_payment_save($request, $subscription_details, $user_details) {

        $previous_payment = UserPayment::where('user_id' , $request->id)->where('status', PAID_STATUS)->orderBy('created_at', 'desc')->first();

        $user_payment = new UserPayment;

        $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription_details->plan} months"));

        if($previous_payment) {

            if (strtotime($previous_payment->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

                $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$subscription_details->plan} months", strtotime($previous_payment->expiry_date)));

            }

        }

        $user_payment->payment_id = $request->payment_id ?: "FREE-".uniqid();

        $user_payment->user_id = $request->id;

        $user_payment->subscription_id = $request->subscription_id;

        $user_payment->status = PAID_STATUS;

        $user_payment->payment_mode = $request->payment_mode;

        $user_payment->is_current = YES;

        // Coupon details

        $user_payment->is_coupon_applied = $request->is_coupon_applied;

        $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';

        $user_payment->coupon_amount = $request->coupon_amount;

        $user_payment->subscription_amount = $subscription_details->amount;

        $user_payment->amount = $request->total;

        $user_payment->coupon_reason = $request->is_coupon_applied == COUPON_APPLIED ? '' : $request->coupon_reason;

        if ($user_payment->save()) {

            $user_details->one_time_subscription = $subscription_details->amount <= 0 ? YES : NO;

            $user_details->user_type = PAID_USER;

            $user_details->save();
            
            $data = [
                        'id' => $user_details->id , 
                        'token' => $user_details->token, 
                        'no_of_account'=>$subscription_details->no_of_account , 
                        'payment_id' => $user_payment->payment_id
                    ];

            $response_array = ['success' => true, 'message' => tr('payment_success') , 'data' => $data];

        } else {

            $response_array = ['success' => false, 'error_messages' => Helper::get_error_message(902), 'error_code' => 902];

        }

        return $response_array;
    }

    /**
     * @method ppv_payment_save()
     *
     * @uses PPV payment record update
     *
     * @created vithya
     *
     * @updated
     *
     * @param objects $admin_video_details
     *
     * @param objects $user_details - User details
     *
     * @return response of success/failure message
     */
    
    public static function ppv_payment_save($request, $admin_video_details, $user_details) {

        $ppv_details = new PayPerView;
                
        $ppv_details->payment_id  = $request->payment_id ?: ($request->is_coupon_applied ? 'COUPON-DISCOUNT' : tr('no_ppv'));

        $ppv_details->status = PAID_STATUS;
                
        $ppv_details->is_watched = NOT_YET_WATCHED;
        
        $ppv_details->paid_date = date('Y-m-d');

        $ppv_details->user_id = $request->id;

        $ppv_details->video_id = $request->admin_video_id;

        $ppv_details->payment_mode = $request->payment_mode;

        $ppv_details->type_of_user = type_of_user($admin_video_details->type_of_user);

        $ppv_details->type_of_subscription = type_of_subscription($admin_video_details->type_of_subscription);

        // Coupon details

        $ppv_details->is_coupon_applied = $request->is_coupon_applied;

        $ppv_details->coupon_code = $request->coupon_code ?: '';

        $ppv_details->coupon_amount = $request->coupon_amount;

        $ppv_details->ppv_amount = $admin_video_details->amount;

        $ppv_details->amount = $request->total;

        $ppv_details->coupon_reason = $request->is_coupon_applied == COUPON_APPLIED ? '' : $request->coupon_reason;

        if($ppv_details->save()) {

            if($ppv_details->amount > 0) { 

                // Do Commission spilit  and redeems for moderator

                Log::info("ppv_commission_spilit started");

                self::ppv_commission_split($admin_video_details->id , $ppv_details->id , $admin_video_details->uploaded_by);

                Log::info("ppv_commission_spilit END"); 
                
            }

            \Log::info("ADD History - add_to_redeem");

            $user_details = User::find($request->id);

            $data = ['id'=>$request->id,'token' => $user_details->token];

            $response_array = ['success' =>true, 'message' => tr('payment_success'), 'data'=> $data];

        } else {

            $response_array = ['success' => false, 'error_messages' => Helper::get_error_message(902), 'error_code' => 902];

        }

        return $response_array;
    }
}
