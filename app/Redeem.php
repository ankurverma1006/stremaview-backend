<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Redeem extends Model
{
    public function moderator() {
    	return $this->belongsTo('App\Moderator');
    }
}
