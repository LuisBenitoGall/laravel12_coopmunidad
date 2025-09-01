<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserCompany extends Model{
    /**
     * 1. Sesión de empresas vinculadas al usuario.
     */
    
    use HasFactory;
    //use SoftDeletes;

    protected $table = 'user_companies';

    //protected $dates = ['deleted_at'];

    protected $fillable = ['user_id', 'company_id']; 

    /**
     * 1. Sesión de empresas vinculadas al usuario.
     */
    public static function userCompanies(){
        $companies = Auth::user()->companies();

        //Limpiamos la session anterior:
        \Session::forget('companies');

        foreach ($companies as $row) {
            if ($row->status) {
                \Session::push('companies', $row);
            }
        }

        return $companies;
    }
}
