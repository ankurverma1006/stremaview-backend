<?php

namespace App\Repositories;

use App\Repositories\UserRepository as UserRepo;
use App\Repositories\ProviderRepository as ProviderRepo;
use App\Repositories\ModeratorRepository as ModeratorRepo;
use App\Repositories\CommonRepository as CommonRepo;

use App\Helpers\Helper;

use Validator;

use App\Language;

use App\User;

use Hash;

use Log;

use Exception;

use DB;

class AdminRepository
{

    public static function languages_save($request) {

        try {
            
            DB::beginTransaction();

            $validator = Validator::make($request->all(),[
                    // 'folder_name' => $request->language_id ? 'required|max:4|unique:languages,folder_name,'.$request->language_id : 'required|max:4|unique:languages,folder_name',
                    // 'language'=> $request->language_id ? 'required|max:4|unique:languages,language,'.$request->language_id : 'required|max:4|unique:languages,language',
                    'folder_name' => 'required|max:4',
                    'language'=>'required|max:64',
                    'auth_file'=> !($request->language_id) ? 'required' : '',
                    'messages_file'=>!($request->language_id) ? 'required' : '',
                    'pagination_file'=>!($request->language_id) ? 'required' : '',
                    'passwords_file'=>!($request->language_id) ? 'required' : '',
                    'validation_file'=>!($request->language_id) ? 'required' : '',
            ]);
            
            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);
            } 

            $language_details = ($request->language_id != '') ? Language::find($request->language_id) : new Language;

            $lang = ($request->language_id != '') ? $language_details->folder_name : '';

            $language_details->folder_name = $request->folder_name;

            $language_details->language = $request->language;

            $language_details->status = APPROVED;

            if ($request->hasFile('auth_file')) {

                 // Read File Length

                $originallength = readFileLength(base_path().'/resources/lang/en/auth.php');

                $length = readFileLength($_FILES['auth_file']['tmp_name']);

                if ($originallength != $length) {

                    throw new Exception(Helper::get_error_message(162), 162);
                }

                if ($language_details->id != '') {

                    $boolean = ($lang != $request->folder_name) ? DEFAULT_TRUE : DEFAULT_FALSE;

                    Helper::delete_language_files($lang, $boolean, 'auth.php');
                }

                Helper::upload_language_file($language_details->folder_name, $request->auth_file, 'auth.php');

            }

            if ($request->hasFile('messages_file')) {

                 // Read File Length

                $originallength = readFileLength(base_path().'/resources/lang/en/messages.php');

                $length = readFileLength($_FILES['messages_file']['tmp_name']);

                if ($originallength != $length) {

                    throw new Exception(Helper::get_error_message(162), 162);
                }

                if ($language_details->id != '') {

                    $boolean = ($lang != $request->folder_name) ? DEFAULT_TRUE : DEFAULT_FALSE;

                    Helper::delete_language_files($lang, $boolean, 'messages.php');
                }

                Helper::upload_language_file($language_details->folder_name, $request->messages_file, 'messages.php');

            }

            if ($request->hasFile('pagination_file')) {

                 // Read File Length

                $originallength = readFileLength(base_path().'/resources/lang/en/pagination.php');

                $length = readFileLength($_FILES['pagination_file']['tmp_name']);

                if ($originallength != $length) {

                    throw new Exception(Helper::get_error_message(162), 162);
                }

                if ($language_details->id != '') {

                    $boolean = ($lang != $request->folder_name) ? DEFAULT_TRUE : DEFAULT_FALSE;

                    Helper::delete_language_files($lang, $boolean, 'pagination.php');
                }

                Helper::upload_language_file($language_details->folder_name, $request->pagination_file, 'pagination.php');

            }


            if ($request->hasFile('passwords_file')) {

                 // Read File Length

                $originallength = readFileLength(base_path().'/resources/lang/en/passwords.php');

                $length = readFileLength($_FILES['passwords_file']['tmp_name']);

                if ($originallength != $length) {
                    
                    throw new Exception(Helper::get_error_message(162), 162);

                }

                if ($language_details->id != '') {

                    $boolean = ($lang != $request->folder_name) ? DEFAULT_TRUE : DEFAULT_FALSE;

                    Helper::delete_language_files($lang, $boolean , 'passwords.php');
                }

                Helper::upload_language_file($language_details->folder_name, $request->passwords_file, 'passwords.php');

            }

            if($request->hasFile('validation_file')) {

                // Read File Length

                $originallength = readFileLength(base_path().'/resources/lang/en/validation.php');

                $length = readFileLength($_FILES['validation_file']['tmp_name']);

                if ($originallength != $length) {
                    
                    throw new Exception(Helper::get_error_message(162), 162);

                }

                if ($language_details->id != '') {
                    $boolean = ($lang != $request->folder_name) ? DEFAULT_TRUE : DEFAULT_FALSE;

                    Helper::delete_language_files($lang, $boolean, 'validation.php');
                }

                Helper::upload_language_file($language_details->folder_name, $request->validation_file, 'validation.php');

            } 

            if ($request->id) {
                if($lang != $request->folder_name)  {
                    $current_path=base_path('resources/lang/'.$lang);
                    $new_path=base_path('resources/lang/'.$request->folder_name);
                    rename($current_path,$new_path);
                }
            }

            $language_details->save();

            if($language_details) {
                
                DB::commit();

                $response_array = ['success' => true, 'message'=> $request->id != '' ? tr('language_update_success') : tr('language_create_success')];
           
            } else {
                
                throw new Exception(tr('something_error'), 101);
            }
            
        } catch (Exception $e) {
            
            DB::rollback();

            $error = $e->getMessage();
            
            $code = $e->getCode();
            
            $response_array = ['success' => false , 'error' => $error, 'code' => $code];

        }

        return $response_array;
    }
}
