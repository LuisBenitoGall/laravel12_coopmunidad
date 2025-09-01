<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImage extends Model{
    /**
     * 1. Relación con user.
     */
    
    protected $table = 'user_images';

    /**
     * 1. Relación con user.
     */
    public function user(){
        return $this->belongsTo(User::class);
    }   
}
