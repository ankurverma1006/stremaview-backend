<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubProfile extends Model
{
    //

    public function user() {
        return $this->belongsTo('App\User');
    }


 	public function userHistory()
    {
        return $this->hasMany('App\UserHistory', 'user_id', 'id');
    }

    public function userWishlist()
    {
        return $this->hasMany('App\Wishlist', 'user_id', 'id');
    }

    /**
     * Get the flag record associated with the user.
     */
    public function userFlag()
    {
        return $this->hasMany('App\Flag', 'sub_profile_id', 'id');
    }


    /**
     * Get the continueWatchingVideo record associated with the sub profile.
     */
    public function continueWatchingVideo()
    {
        return $this->hasMany('App\ContinueWatchingVideo', 'sub_profile_id', 'id');
    }

    public static function boot()
    {
        //execute the parent's boot method 
        parent::boot();

        //delete your related models here, for example
        static::deleting(function($user)
        {
            if (count($user->userHistory) > 0) {

                foreach($user->userHistory as $history)
                {
                    $history->delete();
                } 

            }

            if (count($user->userWishlist) > 0) {

                foreach($user->userWishlist as $wishlist)
                {
                    $wishlist->delete();
                } 

            }

            if (count($user->userFlag) > 0) {

                foreach($user->userFlag as $flag)
                {
                    $flag->delete();
                }

            }

            if (count($user->continueWatchingVideo) > 0) {

                foreach($user->continueWatchingVideo as $continue_watching_video)
                {
                    $continue_watching_video->delete();
                }

            }


        }); 

	}
}
