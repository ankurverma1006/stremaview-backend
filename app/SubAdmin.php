<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;


class SubAdmin extends Authenticatable
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'timezone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Boot function for using with User Events
     *
     * @return void
     */

    public static function boot()
    {
        //execute the parent's boot method 
        parent::boot();

        static::deleting(function($model){
            
            if($model->picture) {

                Helper::delete_picture($model->picture , '/uploads/sub_admins/');
            }

        });
    }
}
