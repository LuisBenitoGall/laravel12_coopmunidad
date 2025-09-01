<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserCommunity extends Model{
    /**
     * 1. Sesión de comunidades vinculadas al usuario.
     */
    
    use HasFactory;
    //use SoftDeletes;

    protected $table = 'user_communities';

    //protected $dates = ['deleted_at'];

    protected $fillable = ['user_id', 'community_id']; 

    /**
     * 1. Sesión de comunidades vinculadas al usuario.
     */
    public static function userCommunities(){
        $communities = Auth::user()->communities();

        //Limpiamos la session anterior:
        \Session::forget('communities');

        foreach ($communities as $row) {
            if ($row->status) {
                \Session::push('communities', $row);
            }
        }

        return $communities;
    }
}
