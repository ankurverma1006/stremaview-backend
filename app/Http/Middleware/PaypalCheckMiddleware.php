<?php

namespace App\Http\Middleware;

use Closure;

class PaypalCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {        

        if (!envfile('PAYPAL_ID') || !envfile('PAYPAL_SECRET') || !envfile('PAYPAL_MODE')) {

            // If the payment occuring from admin panel,  to pay the amount to moderator

            if ($request->redeem_request_id) {

                return back()->with('flash_error', tr('payment_cause').tr('payment_configuration'));

            }
            // If the payment occuring from user panel
            
            return redirect()->route('payment.failure');
        }

        return $next($request);
    }
}
