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
use Auth;
use App\Moderator;
use Validator;
use App\RedeemRequest;
use App\Redeem;

class RedeemPaymentController extends Controller {
   
    private $_api_context;
 
    public function __construct() {

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
     * Function Name : redeem_pay()
     *
     * To pay the amount to moderator which they request from the redeem
     *
     * @created_by - shobana chandrasekar
     *
     * @edited by - -
     *
     * @param object $request - Redeem request details, user details
     *
     * @return response of success/failure message
     */
    public function redeem_pay(Request $request) {

         $validator = Validator::make($request->all() , [
            'redeem_request_id' => 'required|exists:redeem_requests,id',
            'paid_amount' => 'required', 
            ]);

        if($validator->fails()) {

            return back()->with('flash_error' , $validator->messages()->all())->withInput();

        } else {

            $redeem_request_details = RedeemRequest::find($request->redeem_request_id);

            if($redeem_request_details->status == REDEEM_REQUEST_PAID ) {

                return back()->with('flash_error' , tr('redeem_request_status_mismatch'));

            } else {

                $total = $request->paid_amount;

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
                $redirect_urls->setReturnUrl(route('admin.moderator.redeem_pay_status'))
                            ->setCancelUrl(route('admin.moderators.redeems'));

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


                    return redirect(route('admin.moderators.redeems'))->with('flash_error', $error_message);
                
                }

                foreach($payment->getLinks() as $link) {

                    if($link->getRel() == 'approval_url') {

                        $redirect_url = $link->getHref();

                        break;
                    }
               
                }

                // add payment ID to session

                Session::put('redeem_payment_id', $payment->getId());

                if(isset($redirect_url)) {

                    Log::info("Pay API - redirect_url method");

                    $redeem_request_details->payment_id = $payment->getId();

                    $redeem_request_details->admin_paid_amount = $request->paid_amount;

                    $redeem_request_details->status = REDEEM_REQUEST_PROCESSING;

                    $redeem_request_details->save();

                    return redirect()->away($redirect_url);
                
                }
                
            }
        }
                 
    }

    /**
     * @uses to store user payment details from the paypal response
     *
     * @param paypal ID
     *
     * @param paypal Token
     *
     * @return redirect to admin payment page
     * 
     * @created_by shobana chandrasekar
     *
     * @edited : 
     */

    public function redeem_pay_status(Request $request) {

        Log::info("getPaymentStatus method Inside");
        
        // Get the payment ID before session clear

        $payment_id = Session::get('redeem_payment_id');
        
        // clear the session payment ID
     
        if (empty($request->PayerID) || empty($request->token)) {

            Log::info("PayerID or Pay Token empty");
            
            return back()->with('flash_error', tr('payment_configuration'));

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


            dd($error_data);

            $error_message = isset($error_data['error']) ? $error_data['error']: "".".".isset($error_data['error_description']) ? $error_data['error_description'] : "";

            Session::forget('payment_id');

            return redirect(route('admin.moderator.redeem_pay'))->with('flash_error', $error_message);

        }        
          
        if ($result->getState() == 'approved') { // payment made

            $redeem_request_details = RedeemRequest::where('payment_id', $payment_id)->first();

            $redeem_request_details->paid_amount = $redeem_request_details->paid_amount + $redeem_request_details->admin_paid_amount;

            $redeem_request_details->status = REDEEM_REQUEST_PAID;

            $redeem_request_details->save();

            
            $redeem = Redeem::where('moderator_id', $redeem_request_details->moderator_id)->first();

            $redeem->paid += $redeem_request_details->admin_paid_amount;

            $redeem->remaining = $redeem->total_moderator_amount - $redeem->paid;

            $redeem->save();

            if ($redeem_request_details->moderator) {

                $redeem_request_details->moderator->paid_amount += $redeem_request_details->admin_paid_amount;

                $redeem_request_details->moderator->remaining_amount = $redeem->total_moderator_amount - $redeem->paid;

                // $redeem_request_details->moderator->total = $redeem->paid;

                $redeem_request_details->moderator->save();
            }

            Session::forget('paypal_payment_id');
            
            $response_array = array('success' => true , 'message' => tr('payment_successful') ); 

            $responses = response()->json($response_array);

            $response = $responses->getData();

            return redirect(route('admin.moderators.redeems'))->with('flash_success',tr('payment_successful'));

        } else {

            $error_message = tr('payment_not_approved_contact_admin');

            Session::forget('payment_id');

            return redirect(route('admin.moderator.redeem_pay'))->with('flash_error', $error_message);
       
        }
                   
    }

   
}