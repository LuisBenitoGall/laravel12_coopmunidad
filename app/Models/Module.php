<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

//Models:
use Spatie\Permission\Models\Permission;

class Module extends Model{
    /**
     * 1. Funcionalidades del módulo.
     * 2. Módulo por slug.
     * 3. Empresas del módulo.
     * 4. Guardar módulo.
     * 5. Creado por.
     * 6. Actualizado por.
     * 7. Query scope por contexto activo.
     */
    
    use HasFactory;
    use SoftDeletes;

    protected $table = 'modules';

    protected $dates = ['deleted_at'];

    protected $fillable = ['name', 'slug', 'label', 'color', 'icon', 'scope', 'level', 'translations', 'explanation', 'status'];

    public const SCOPE_COMPANY   = 'company';
    public const SCOPE_COMMUNITY = 'community';
    public const SCOPE_BOTH      = 'both';

    /**
     * 1. Funcionalidades del módulo.
     */
    public function functionalities(){
        return $this->hasMany(Functionality::class);
    }

    /**
     * 2. Módulo por slug.
     */
    public static function moduleBySlug($slug){
        return Module::where('slug', $slug)->first();
    }

    /**
     * 3. Empresas del módulo.
     */
    public function company(){
        return $this->belongsToMany(Company::class, 'company_modules');
    }

    /**
     * 4. Guardar módulo.
     */
    public static function saveModule($request) {
        $slug = Str::slug($request->name);

        //El valor debe ser único:
        if(Module::where('slug', $slug)->exists()){
            $alert = __('valor_repetido');
            return redirect()->route('modules.create')->with(compact('alert'));
        }

        $m = new Module();
        $m->name = strtolower($request->name);
        $m->slug = $slug;
        $m->label = strtolower($request->name);
        $m->color = $request->color;
        $m->icon = $request->icon;
        $m->level = $request->level;
        //$m->explanation = $request->explanation;
        $m->status = $request->status? 1:'0';
        $m->save();

        //Crear permisos del módulo.
        Permission::create(['name' => 'module_'.$m->slug]);

        return $m;
    }

    /**
     * 5. Creado por.
     */
    public function createdBy(){
       return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 6. Actualizado por.
     */
    public function updatedBy(){
       return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 7. Query scope por contexto activo.
     */
    public function scopeForContext($query, string $ctxType){
        return $query->whereIn('scope', [$ctxType, self::SCOPE_BOTH]);
    }
}
