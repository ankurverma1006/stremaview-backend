<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use Validator;

use Log;

use App\User;

use App\SubProfile;

use DB;

class NewUserApiValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        $validator = Validator::make($request->all(), [
                        'token' => 'required|min:5',
                        'id' => 'required|integer|exists:users,id',
                    ]);

        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response = ['success' => false, 'error' => $error_messages , 'error_code' => 3000, 'error_messages'=> Helper::get_error_message(3000)];

            return response()->json($response,200);

        } else {

            $sub_profile_details = SubProfile::find($request->sub_profile_id);

            if(!$sub_profile_details) {

                $response_array = ['success' => false , 'error_messages' => Helper::get_error_message(3002) , 'error_code' => 3002];
                
                return response()->json($response_array, 200);

            }

            if (!Helper::is_token_valid('USER', $request->id, $request->token, $error)) {

                $response = response()->json($error, 200);
                
                return $response;
                
            } else {

                $user_details = User::find($request->id);

                if(!$user_details) {
                    
                    $response = ['success' => false , 'error_messages' => Helper::get_error_message(133) , 'error_code' => 133];

                    return response()->json($response, 200);
                }
            }
        }

        return $next($request);
    }
}
