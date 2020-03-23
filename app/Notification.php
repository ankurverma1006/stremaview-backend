<?php

namespace App;

use App\User;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //

    public static function save_notification($video_id, $type = "") {

        \Log::info("Notification Inside");

    	$users = User::where('is_verified', 1)->get();

        \Log::info("Count of users ".count($users));

    	foreach ($users as $key => $value) {
    		
            $notification =  Notification::where('admin_video_id', $video_id)
                ->where('user_id', $value->id)
                ->first();

    		$model = $notification ? $notification : new Notification;

    		$model->user_id = $value->id;

    		$model->admin_video_id = $video_id;

            $model->type = $type;  // Future use

            $model->link_id = $video_id; // Future use

    		$model->status = 0;

    		$model->save();

    	}

    }


    public function adminVideo() {
        return $this->hasOne('App\AdminVideo', 'id', 'admin_video_id');
    }
}
