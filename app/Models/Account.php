<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Account extends Model{
    /**
     * 1. Guardar cuenta.
     */

    use HasFactory;
    use SoftDeletes;

    protected $table = 'accounts';

    protected $dates = ['deleted_at'];

    protected $fillable = ['company_id', 'name', 'slug', 'description', 'rate', 'duration', 'status'];  

    /**
     * 1. Guardar cuenta.
     */
    public static function saveAccount($request){
        $slug = Str::slug($request->name);

        $a = new Account();
        $a->name = $request->name;
        $a->slug = $slug;
        $a->description = $request->description;
        $a->rate = $request->rate;
        $a->duration = $request->duration;
        $a->status = $request->status? 1:'0';
        $a->save();

        return $a;
    }

}
