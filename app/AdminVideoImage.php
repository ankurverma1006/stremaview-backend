<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminVideoImage extends Model
{
    public function adminVideo() {
        return $this->belongsTo('App\adminVideo');
    }

    /**
     * Scope a query to only include active users.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBaseResponse($query) {

        return $query->select(
            'admin_video_images.id as admin_video_image_id' ,
            'admin_video_images.admin_video_id',
            'admin_video_images.image'
        );
    }
}
