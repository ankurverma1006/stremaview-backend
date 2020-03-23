<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Auth, Setting, DB, Log;

use App\Helpers\Helper;

use App\Admin;

use App\User;

use App\Moderator;

use App\AdminVideo;

class SubAdminController extends Controller {

	    /**
     * Function: dashboard()
     * 
     * @uses used to display analytics of the website
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *`
     * @param 
     *
     * @return view page
     */

    public function dashboard() {

            $id = Auth::guard('admin')->user()->id;

            $admin = Admin::find($id);

            $admin->token = Helper::generate_token();

            $admin->token_expiry = Helper::generate_token_expiry();

            $admin->save();
            
            $user_count = User::count();

            $provider_count = Moderator::count();

            $video_count = AdminVideo::count();
            
            $recent_videos = Helper::recently_added();

            $get_registers = get_register_count();

            $recent_users = get_recent_users();

            $total_revenue = total_revenue();

            $view = last_days(10);

            if (Setting::get('track_user_mail')) {

                user_track("StreamHash - New Visitor");
            }

            return view('admin.dashboard.dashboard')
                        ->withPage('dashboard')
                        ->with('sub_page','')
                        ->with('user_count' , $user_count)
                        ->with('video_count' , $video_count)
                        ->with('provider_count' , $provider_count)
                        ->with('get_registers' , $get_registers)
                        ->with('view' , $view)
                        ->with('total_revenue' , $total_revenue)
                        ->with('recent_users' , $recent_users)
                        ->with('recent_videos' , $recent_videos);

    
    }
    
}
