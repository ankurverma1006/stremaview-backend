<?php

namespace App\Http\Controllers;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;

use App\Repositories\PaymentRepository as PaymentRepo;

use Setting;
use Log;
use Session;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\User;
use App\UserPayment;
use Auth;
use App\AdminVideo;
use App\PayPerView;
use App\Subscription;
use App\Moderator;
 
use App\Coupon;

use App\UserCoupon;

class PaypalController extends Controller {
   
    private $_api_context;
    
    protected $UserAPI;

    public function __construct(UserApiController $API) {

        $this->UserAPI = $API;

        // This middleware used check the paypal configuration 

        $this->middleware('PaypalCheck');
       
        // setup PayPal api context

        $paypal_conf = config('paypal');

        $paypal_conf['client_id'] = envfile('PAYPAL_ID') ?  envfile('PAYPAL_ID') : $paypal_conf['client_id'];
        $paypal_conf['secret'] = envfile('PAYPAL_SECRET') ?  envfile('PAYPAL_SECRET') : $paypal_conf['secret'];
        $paypal_conf['settings']['mode'] = envfile('PAYPAL_MODE') ?  envfile('PAYPAL_MODE') : $paypal_conf['settings']['mode'];

        Log::info("PAYPAL CONFIGURATION".print_r($paypal_conf,true));
        
        $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_conf['client_id'], $paypal_conf['secret']));

        $this->_api_context->setConfig($paypal_conf['settings']);
   
    }

    /** 
     *
     *
     *
     *
     */

    public function pay(Request $request) {

        $subscription = Subscription::find($request->id);

        $user = User::find($request->user_id);

        if(count($subscription) == 0 || count($user) == 0) {

            Log::info("Subscription Details Not Found");

            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");

        }

        $total =  $subscription->amount > 0 ?  $subscription->amount : "0.1" ;

        $coupon_amount = 0;

        $coupon_reason = '';

        $is_coupon_applied = COUPON_NOT_APPLIED;

        if ($request->coupon_code) {

            $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

            if ($coupon) {

                $is_coupon_applied = DEFAULT_TRUE;

                if ($coupon->status == COUPON_INACTIVE) {

                    $coupon_reason = tr('coupon_code_declined');

                } else {

                    $check_coupon = $this->UserAPI->check_coupon_applicable_to_user($user, $coupon)->getData();

                    if ($check_coupon->success) {

                        $amount_convertion = $coupon->amount;

                        if ($coupon->amount_type == PERCENTAGE) {

                            $amount_convertion = amount_convertion($coupon->amount, $subscription->amount);

                        }

                        if ($amount_convertion < $subscription->amount) {

                            $total = $subscription->amount - $amount_convertion;

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

                $coupon_reason = tr('coupon_code_not_exists');

            }

        }


        $item = new Item();

        $item->setName(Setting::get('site_name'))
                ->setCurrency('USD')
                ->setQuantity('1')
                ->setPrice($total);
     
        $payer = new Payer();
        
        $payer->setPaymentMethod('paypal');

        // add item to list

        $item_list = new ItemList();
        $item_list->setItems(array($item));
        $total = $total;

        $details = new Details();
        $details->setShipping('0.00')
            ->setTax('0.00')
            ->setSubtotal($total);


        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal($total)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Payment for the Request');

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(url('/user/payment/status'))
                    ->setCancelUrl(Setting::get('ANGULAR_SITE_URL')."payment-failure");

        $payment = new Payment();

        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try {

            Log::info("Pay API TRY METHOD");

            $payment->create($this->_api_context);

        } catch (\PayPal\Exception\PayPalConnectionException $ex) {

            // Log::info("Exception: " . $ex->getMessage() . PHP_EOL);

            $error_data = json_decode($ex->getData(), true);

            $error_message = isset($error_data['error']) ? $error_data['error']: "".".".isset($error_data['error_description']) ? $error_data['error_description'] : "";

            Log::info("Pay API catch METHOD");

            PaymentRepo::subscription_payment_failure_save($request->user_id, $request->id, $error_message);

            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");
        
        }

        foreach($payment->getLinks() as $link) {

            if($link->getRel() == 'approval_url') {

                $redirect_url = $link->getHref();

                break;
            }
       
        }

        // add payment ID to session

        Session::put('paypal_payment_id', $payment->getId());

        if(isset($redirect_url)) {

            Log::info("Pay API - redirect_url method");

            $previous_payment = UserPayment::where('user_id' , $request->user_id)->where('status', DEFAULT_TRUE)->orderBy('id', 'desc')->first();

            $user_payment = new UserPayment();

            if ($previous_payment) {

                if (strtotime($previous_payment->expiry_date) >= strtotime(date('Y-m-d H:i:s'))) {

                    $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime("+{$subscription->plan} months", strtotime($previous_payment->expiry_date)));

                } else {

                    $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));

                }                                                               

            } else {
                $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+{$subscription->plan} months"));
            }

            $user_payment->payment_id  = $payment->getId();
            $user_payment->user_id = $request->user_id;
            $user_payment->subscription_id = $request->id;

    	    $user_payment->payment_mode = PAYPAL;

            // Coupon details

            $user_payment->is_coupon_applied = $is_coupon_applied;

            $user_payment->coupon_code = $request->coupon_code  ? $request->coupon_code  :'';

            $user_payment->coupon_amount = $coupon_amount;

            $user_payment->subscription_amount = $subscription->amount;

            $user_payment->coupon_reason = $is_coupon_applied == COUPON_APPLIED ? '' : $coupon_reason;

            Log::info("User Payment ".print_r($user_payment, true));

            $user_payment->save();

            Log::info("User Payment After saved ".print_r($user_payment, true));

            $response_array = array('success' => true); 

            return redirect()->away($redirect_url);
        
        }
        
        return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");
                 
    }

    /**
     * @uses to store user payment details from the paypal response
     *
     * @param paypal ID
     *
     * @param paypal Token
     *
     * @return redirect to angular pages, depends on the response
     * 
     * @author vidhyar2612
     *
     * @edited : 
     */

    public function getPaymentStatus(Request $request) {

        Log::info("getPaymentStatus method Inside");
        
        // Get the payment ID before session clear

        $payment_id = Session::get('paypal_payment_id');
        
        // clear the session payment ID
     
        if (empty($request->PayerID) || empty($request->token)) {

            Log::info("PayerID or Pay Token empty");
            
            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");

        }

        try { 

            $payment = Payment::get($payment_id, $this->_api_context);

            // PaymentExecution object includes information necessary
            // to execute a PayPal account payment.
            // The payer_id is added to the request query parameters
            // when the user is redirected from paypal back to your site
            
            $execution = new PaymentExecution();

            $execution->setPayerId($request->PayerID);
         
            // Execute the payment

            $result = $payment->execute($execution, $this->_api_context);

        } catch(\PayPal\Exception\PayPalConnectionException $ex){

            $error_data = json_decode($ex->getData(), true);

            $error_message = isset($error_data['error']) ? $error_data['error']: "".".".isset($error_data['error_description']) ? $error_data['error_description'] : "";

            PaymentRepo::subscription_payment_failure_save("", "", $error_message , $payment_id);

            Session::forget('payment_id');

            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");

        }        
          
        if ($result->getState() == 'approved') { // payment made

            $payment = UserPayment::where('payment_id',$payment_id)->first();

            $subscription = Subscription::find($payment->subscription_id);

            $total =  $subscription ? $subscription->amount : "1.00" ;

            $payment->status = PAID_STATUS;

            $payment->amount = $payment->subscription_amount - $payment->coupon_amount;

            $payment->save();


            if ($payment) {

                $user = User::find($payment->user_id);

                $user->amount_paid += $total;

                $user->expiry_date = $payment->expiry_date;

                $user->no_of_days = 0;

                $user->user_type = DEFAULT_TRUE;

                $user->save();

            }

            Session::forget('paypal_payment_id');
            
            $response_array = array('success' => true , 'message' => tr('payment_successful') ); 

            $responses = response()->json($response_array);

            $response = $responses->getData();

            // return back()->with('flash_success' , 'Payment Successful');

            // return redirect()->away("http://localhost/streamview-base/streamview-angular/#/view-profiles");

            // return redirect()->away("https://appswamy.com/#/video-form");

            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."subscription-success");

        } else {

            $error_message = tr('payment_not_approved_contact_admin');

            PaymentRepo::subscription_payment_failure_save("", "", $error_message , $payment_id);

            Session::forget('payment_id');

            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");
       
        }
                   
    }

    /**
     * @uses Get the payment for PPV from user
     *
     * @param id = VIDEO ID
     *
     * @param user_id 
     *
     * @return redirect to success/faiture pages, depends on the payment status
     * 
     * @author shobanacs
     *
     * @edited : vidhyar2612
     */
   
    public function videoSubscriptionPay(Request $request) {

        // Get the PPV total amount based on the selected video

        $video = AdminVideo::where('id', $request->id)->first();

        if(count($video) == 0 ){
            
            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");
        }

        $total = $video->amount == 0 ? 0.1 : $video->amount;

        $coupon_amount = 0;

        $is_coupon_applied = DEFAULT_FALSE;

        $coupon_reason = "";

        if ($request->coupon_code) {

            $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

            if ($coupon) {

                $is_coupon_applied = DEFAULT_TRUE;

                if (!$coupon->status) {

                    $coupon_reason = tr('coupon_code_declined');

                } else {

                    $amount_convertion = $coupon->amount;

                    if ($coupon->amount_type == PERCENTAGE) {

                        $amount_convertion = amount_convertion($coupon->amount, $video->amount);

                    }

                    if ($amount_convertion < $video->amount) {

                        $total = $video->amount - $amount_convertion;

                        $coupon_amount = $amount_convertion;

                    }
                }

            } else {

                $coupon_reason = tr('coupon_code_not_exists');

            }

        }

        $item = new Item();

        $item->setName(Setting::get('site_name')) // item name
                   ->setCurrency('USD')
               ->setQuantity('1')
               ->setPrice($total);
     
        $payer = new Payer();
        
        $payer->setPaymentMethod('paypal');

        // add item to list
        $item_list = new ItemList();
        $item_list->setItems(array($item));
        $total = $total;
        $details = new Details();
        $details->setShipping('0.00')
            ->setTax('0.00')
            ->setSubtotal($total);


        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal($total)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Payment for the Request');

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(url('/user/payment/video-status'))
                    ->setCancelUrl(Setting::get('ANGULAR_SITE_URL')."payment-failure");

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try {

            $payment->create($this->_api_context);

        } catch (\PayPal\Exception\PayPalConnectionException $ex) {

            if (\Config::get('app.debug')) {

                // echo "Exception: " . $ex->getMessage() . PHP_EOL;
                // echo "Payment" . $payment."<br />";

                // $err_data = json_decode($ex->getData(), true);
                // echo "Error" . print_r($err_data);
                // exit;

                 // Log::info("Exception: " . $ex->getMessage() . PHP_EOL);

                $error_data = json_decode($ex->getData(), true);

                $error_message = $ex->getMessage() . PHP_EOL;

                // $error_message = isset($error_data['error']) ? $error_data['error']: "".".".isset($error_data['error_description']) ? $error_data['error_description'] : "";

                Log::info("Pay API catch METHOD");

                PaymentRepo::ppv_payment_failure_save($request->user_id, $request->id, $error_message);

                return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");

            } else {

                $error_data = "Some error occur, sorry for inconvenient";

                PaymentRepo::ppv_payment_failure_save($request->user_id, $request->id, $error_message);

                return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");

            }
        }

        foreach($payment->getLinks() as $link) {

            if($link->getRel() == 'approval_url') {

                $redirect_url = $link->getHref();

                break;
            }
        }

        // Add payment ID to session to use after payment redirection

        Session::put('paypal_payment_id', $payment->getId());

        if(isset($redirect_url)) {

            $user_payment = new PayPerView;

            $user_payment->expiry_date = date('Y-m-d H:i:s');

            $user_payment->payment_id  = $payment->getId();

            $user_payment->user_id = $request->user_id;

            $user_payment->video_id = $request->id;

            // $user_payment->amount = $total;

            $user_payment->coupon_amount = $coupon_amount;

            $user_payment->coupon_code = $request->coupon_code ? $request->coupon_code : "";

            $user_payment->ppv_amount = $video->amount;

            $user_payment->payment_mode = PAYPAL;

            $user_payment->is_coupon_applied = $is_coupon_applied;

            $user_payment->coupon_reason = $is_coupon_applied ? $coupon_reason : '';

            $user_payment->save();

            return redirect()->away($redirect_url);

        }

        return redirect()->away(Setting::get('ANGULAR_SITE_URL'));
                    
    }

    /**
     * @uses to store user payment details from the paypal response
     *
     * @param paypal ID
     *
     * @param paypal Token
     *
     * @return redirect to angular pages, depends on the 
     * 
     * @author shobanacs
     *
     * @edited : vidhyar2612
     */

    public function getVideoPaymentStatus(Request $request) {

        // Get the payment ID before session clear

        $payment_id = Session::get('paypal_payment_id');
        
        // clear the session payment ID
     
        if (empty($request->PayerID) || empty($request->token)) {
            
            Log::info("PPV - PayerID or Pay Token empty");
            
            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");

        } 

        try { 

            $payment = Payment::get($payment_id, $this->_api_context);

            // PaymentExecution object includes information necessary
            // to execute a PayPal account payment.
            // The payer_id is added to the request query parameters
            // when the user is redirected from paypal back to your site
            
            $execution = new PaymentExecution();

            $execution->setPayerId($request->PayerID);
         
            //Execute the payment

            $result = $payment->execute($execution, $this->_api_context);

        } catch(\PayPal\Exception\PayPalConnectionException $ex){

            $error_data = json_decode($ex->getData(), true);

            $error_message = $ex->getMessage() . PHP_EOL;

            // $error_message = isset($error_data['error']) ? $error_data['error']: "".".".isset($error_data['error_description']) ? $error_data['error_description'] : "";

            PaymentRepo::ppv_payment_failure_save("", "", $error_message , $payment_id);

            Session::forget('paypal_payment_id');

            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");

        }
                      
       // echo '<pre>';print_r($result);echo '</pre>';exit; // DEBUG RESULT, remove it later
     
        if ($result->getState() == 'approved') { // payment made

            $payment = PayPerView::where('payment_id',$payment_id)->first();

            if(count($payment) == 0) {

                $error_message = "PPV details not found!!!";

                PaymentRepo::ppv_payment_failure_save("", "", $error_message , $payment_id);

                Session::forget('paypal_payment_id');

                return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");

            }

            $payment->amount = $payment->ppv_amount - $payment->coupon_amount;

            $payment->paid_date = date('Y-m-d');

            $payment->is_watched = DEFAULT_FALSE;

            $payment->status = DEFAULT_TRUE;

            if ($payment->adminVideo->type_of_user == 1) {

                $payment->type_of_user = "Normal User";

            } else if($payment->adminVideo->type_of_user == 2) {

                $payment->type_of_user = "Paid User";

            } else if($payment->adminVideo->type_of_user == 3) {

                $payment->type_of_user = "Both Users";
            }


            if ($payment->adminVideo->type_of_subscription == 1) {

                $payment->type_of_subscription = "One Time Payment";

            } else if($payment->adminVideo->type_of_subscription == 2) {

                $payment->type_of_subscription = "Recurring Payment";

            }

            $payment->save();

            $video = $payment->adminVideo;

            if($video->amount > 0) { 

                // Do Commission spilit  and redeems for moderator

                Log::info("ppv_commission_spilit started");

                PaymentRepo::ppv_commission_split($video->id , $payment->id , $video->uploaded_by);

                Log::info("ppv_commission_spilit END");            
                
            }

            Session::forget('paypal_payment_id');
            
            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."pay-per-view-success/".$payment->adminVideo->id);
       
        } else {

            $error_message = tr('payment_not_approved_contact_admin');

            PaymentRepo::ppv_payment_failure_save("", "", $error_message , $payment_id);

            Session::forget('paypal_payment_id');

            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."payment-failure");
        }
            
           
    }
   
}