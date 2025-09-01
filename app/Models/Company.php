<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use File;

//Concerns:
use App\Models\Concerns\HasCategories;

//Models:
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Company extends Model{
    /**
     * 1. Creada por.
     * 2. Actualizada por.
     * 3. Guardar empresa.
     * 4. Guardar logo de empresa.
     * 5. Teléfonos de la empresa.
     */
    
    use HasCategories;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'companies';

    protected $dates = ['deleted_at'];

    protected $fillable = ['id', 'name', 'slug', 'tradename', 'logo', 'nif', 'created_by', 'updated_by', 'status'];

    /**
     * 1. Creada por.
     */
    public function createdBy(){
       return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 2. Actualizada por.
     */
    public function updatedBy(){
       return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 3. Guardar empresa.
     */
    public static function saveCompany($request){
        $slug = Str::slug($request->name);

        $co = new Company();
        $co->name = $request->name;
        $co->slug = $slug;
        $co->tradename = $request->tradename;
        $co->nif = $request->nif;
        $co->created_by = Auth::user()->id;
        $co->updated_by = Auth::user()->id;   
        
        //Guardando logo:
        $filename = self::saveCompanyLogo($request, $slug);

        $co->logo = $filename; 
        $co->save();

        //Cuenta de la empresa:
        

        //Roles de la empresa:
        

        //Creando centro de trabajo por defecto:
        $wp = new Workplace();
        $wp->company_id = $co->id;
        $wp->name = ucfirst(trans('textos.sede')).' '.$co->name;
        $wp->slug = Str::slug(trans('textos.sede'));
        $wp->featured = 1;
        $wp->save();

        //Vinculación a empresa:
        if($request->auto_link){
            UserCompany::firstOrCreate(
                ['user_id' => Auth::user()->id, 'company_id' => $co->id,],
                ['user_id' => Auth::user()->id, 'company_id' => $co->id]
            );
        } 





        return $co;
    }

    /**
     * 4. Guardar logo de empresa.
     */
    public static function saveCompanyLogo($request, $slug){
        if ($request->hasFile('logo')){
            $file = $request->file('logo');
            $filename = $slug . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/companies', $filename);
            return $filename;

        }else{
            return false;
        }
    }

    /**
     * 5. Teléfonos de la empresa.
     */
    public function phones(){
        return $this->morphMany(\App\Models\Phone::class, 'phoneable')
            ->orderByDesc('is_primary');
    }
}
