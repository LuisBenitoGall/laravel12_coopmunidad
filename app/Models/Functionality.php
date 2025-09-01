<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Functionality extends Model{
    /**
     * 1. Funcionalidades por módulo.
     * 2. Filtrado de funcionalidades por módulo.
     * 3. Funcionalidades join módulos.
     */
    
    use HasFactory;
    use SoftDeletes;

    protected $table = 'functionalities';

    protected $dates = ['deleted_at'];

    protected $fillable = ['name', 'slug', 'label', 'module_id'];

    /**
     * 1. Funcionalidades por módulo.
     */
    public static function getByModule($moduleId){
        return self::where('module_id', $moduleId)->orderBy('label', 'ASC')->get();
    }

    /**
     * 2. Filtrado de funcionalidades por módulo.
     */
    public function filteredData(Request $request){
        $query = Functionality::query();

        if ($request->filled('module_id')) {
            $query->where('module_id', $request->input('module_id'));
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('label')) {
            $query->where('label', 'like', '%' . $request->input('label') . '%');
        }

        $data = $query->get();

        return response()->json([
            'functionalities' => $data 
        ]);
    }

    /**
     * 3. Funcionalidades join módulos.
     * 
     * Se descartan los permisos que pertenezcan exclusivamente a Super Admin:
     */
    public static function permissionsJoinModule(){
        $exceptions = config('constants.PERMISSIONS_OFF_');

        $data = Functionality::select('functionalities.*', 'modules.level')
        ->join('modules', 'functionalities.module_id', '=', 'modules.id')
        ->whereNotIn('functionalities.slug', $exceptions)
        ->orderBy('functionalities.label', 'ASC')
        ->get();

        return $data;
    }

}
