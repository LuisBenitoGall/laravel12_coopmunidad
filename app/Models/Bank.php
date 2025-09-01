<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Bank extends Model{
    /**
     * 1. Importación de datos desde .json.
     */
    
    use HasFactory;
    use SoftDeletes;

    protected $table = 'banks';

    protected $fillable = ['name', 'slug', 'tradename', 'swift', 'lei', 'eu_code', 'supervisor_code', 'status'];

    /**
     * 1. Importación de datos desde .json.
     */
    public static function import(){
        $exist = Bank::select('id')->first();

        if(!$exist){   
            $path = storage_path()."/json/banks.json";
            $data = json_decode(file_get_contents($path), true);

            foreach($data as $r){
                $slug = Str::slug($r['name']);

                Bank::create([
                    'name' => $r['name'],
                    'slug' => $slug,
                    'tradename' => $r['tradename'],
                    'swift' => isset($r['swift'])? $r['swift']:NULL,
                    'lei' => $r['lei'],
                    'eu_code' => $r['eu_code'],
                    'supervisor_code' => $r['supervisor_code'],
                    'status' => $r['status']
                ]);
            }
        } 
    }
}
