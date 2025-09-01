<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Constants;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

//Exports:
use App\Exports\RolesExport;

//Models:
use App\Models\Company;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

//Requests:
use App\Http\Requests\RoleFilterRequest;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;

//Resources:
use App\Http\Resources\RoleResource;

//Traits:
use App\Traits\HasUserPermissionsTrait;
//use App\Traits\InvalidatesSecondaryMenuCache;
use App\Traits\LocaleTrait;

class RoleController extends Controller{
    /**
     * 1. Listado de roles.
     * 1.1. Data para exportación.
     * 1.2. Data Query.
     * 1.3. Data Tabulator.
     * 2. Formulario nuevo role.
     * 3. Guardar nuevo role.
     * 4. Editar role.
     * 5. Actualizar role.
     * 6. Eliminar role.
     * 7. Sincronizar permisos sobre los módulos.
     * 8. Setear permiso para role.
     * 9. Setear múltiples permisos.
     * 10. Exportar a .xls.
     * 11. Exportar a .pdf.
     * 12. Lógica de exportación.
     */
    
    use HasUserPermissionsTrait;
    //use InvalidatesSecondaryMenuCache;
    use LocaleTrait;
    
    private $module = 'settings';
    private $option = 'configuracion';
    protected array $permissions = [];

    public function __construct(){
        if(session('currentCompany')){
            $this->permissions = $this->resolvePermissions([
                'roles.create',
                'roles.destroy',
                'roles.edit',
                'roles.index',
                'roles.search',
                'roles.show',
                'roles.update'
            ]);   
        } 
    }   

    /**
     * 1. Listado de roles.
     */
    public function index(RoleFilterRequest $request){
        $perPage = $request->input('per_page', config('constants.RECORDS_PER_PAGE_DEFAULT_'));

        $roles = $this->dataQuery($request)->paginate($perPage)->onEachSide(1);

        //SuperAdmin:
        $is_superadmin = Auth::user()->hasRole(config('constants.SUPER_ADMIN_'))? true:false;

        return Inertia::render('Admin/Role/Index', [
            "title" => __($this->option),
            "subtitle" => __('roles'),
            "module" => $this->module,
            "slug" => 'roles',
            "roles" => RoleResource::collection($roles),
            "is_superadmin" => $is_superadmin,
            "queryParams" => request()->query() ?: null,
            "availableLocales" => LocaleTrait::availableLocales(),
            "permissions" => $this->permissions
        ]);    
    }

    /**
     * 1.1. Data para exportación.
     */
    public function filteredData(RoleFilterRequest $request){
        $cacheKey = 'filtered_roles_' . md5(json_encode($request->all()));

        $roles = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            return $this->dataQuery($request)->get();
        });

        return response()->json([
            'roles' => RoleResource::collection($roles)
        ]);    
    }

    /**
     * 1.2. Data Query.
     */
    private function dataQuery(Request $request){
        $currentCompany = session('currentCompany');
        $user = Auth::user();
        $query = Role::query();

        // Ordenación por defecto:
        $sortField = $request->input('sort_field', 'name');
        $sortDirection = $request->input('sort_direction', 'ASC');
        $allowedSortFields = ['name', 'description'];

        if(!in_array($sortField, $allowedSortFields)){
            $sortField = 'name';
        }

        // Si el usuario es SuperAdmin:
        if($user->hasRole(config('constants.SUPER_ADMIN_'))){
            $query->where(function ($q){
                $q->where('name', config('constants.SUPER_ADMIN_'));
            });
        }else{
            // Si NO es SuperAdmin, solo ve roles de su empresa
            $query->where('name', '!=', config('constants.SUPER_ADMIN_'));
        }

        // Aplicar filtros
        if($request->filled('name')){
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if($request->filled('description')){
            $query->where('description', 'like', '%' . $request->input('description') . '%');
        }

        return $query->orderBy($sortField, $sortDirection);
    }

    /**
     * 1.3. Data Tabulator.
     */
    public function table(Request $request){
        $page    = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);

        $sorters = json_decode($request->input('sorters', '[]'), true) ?: [];
        $filters = json_decode($request->input('filters', '[]'), true) ?: [];

        // sort por el primero
        $sortField = 'name';
        $sortDir   = 'ASC';
        if (!empty($sorters)) {
            $sf = $sorters[0]['field'] ?? 'name';
            $sd = strtoupper($sorters[0]['dir'] ?? 'asc');
            $allowed = ['id','name','description','created_at'];
            if (in_array($sf, $allowed, true)) $sortField = $sf;
            $sortDir = $sd === 'DESC' ? 'DESC' : 'ASC';
        }

        // Mapea filtros remotos {field,value}
        $mapped = [];
        foreach ($filters as $f) {
            $field = $f['field'] ?? null;
            $value = trim((string)($f['value'] ?? ''));
            if ($field && $value !== '') $mapped[$field] = $value;
        }

        $request->merge([
            'sort_field'     => $sortField,
            'sort_direction' => $sortDir,
        ]);

        $query = $this->dataQuery($request);

        // Aplica filtros remotos
        if (isset($mapped['name'])) {
            $v = $mapped['name'];
            $query->where(function ($sub) use ($v) {
                $sub->where('name', 'like', "%{$v}%")
                ->orWhere('description', 'like', "%{$v}%");
            });
        }
        // añade aquí otros si activas headerFilter en más columnas

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)->onEachSide(1);

        // 👉 DEVUELVE DATOS PLANOS (sin Resource) PARA TABULATOR
        return response()->json([
            'data'       => $paginator->getCollection()->values(), // <- aquí el array "limpio"
            'last_page'  => $paginator->lastPage(),
            'page'       => $paginator->currentPage(),
            'per_page'   => $paginator->perPage(),
            'total'      => $paginator->total(),
        ]);
    }

    /**
     * 2. Formulario nuevo role.
     */
    public function create(){
        return Inertia::render('Admin/Role/Create', [
            "title" => __($this->option),
            "subtitle" => __('role_nuevo'),
            "module" => $this->module,
            "slug" => 'roles',
            "availableLocales" => LocaleTrait::availableLocales(),
            "permissions" => $this->permissions
        ]);     
    }

    /**
     * 3. Guardar nuevo role.
     */
    public function store(RoleStoreRequest $request){
        //Guardamos role:
        $role = new Role();
        $role->name = $request->name;
        $role->guard_name = $request->guard_name? $request->guard_name:'web';
        $role->description = $request->description;
        $role->save();

        //Permisos sobre los módulos:
        //$this->syncModulePermissions($role);

        return redirect()->route('roles.edit', $role->id)
            ->with('msg', __('role_creado_msg'));
    }

    /**
     * 4. Editar role.
     */
    public function edit(Role $role){
        $locale = LocaleTrait::languages(session('locale', app()->getLocale()));

        //Formateo de datos:
        $role->formatted_created_at = Carbon::parse($role->created_at)->format($locale[4].' H:i:s');
        $role->formatted_updated_at = Carbon::parse($role->updated_at)->format($locale[4].' H:i:s');

        $functionalities = Functionality::permissionsJoinModule();
        $permissions_all = Permission::get();
        $modules = Module::where('status', 1)
        ->orderBy('label', 'ASC')
        ->get();

        //Obtenemos permisos del rol:
        $data = $role->permissions;

        $module_permissions = [];       //Permisos de módulo.
        $role_permissions = [];         //Permisos de funcionalidad.
        foreach($data as $row){
            //Separamos los permisos según sean de módulo o de funcionalidad:
            if(substr($row->name, 0, 7) === "module_"){
                array_push($module_permissions, $row->id);
            }else{
                array_push($role_permissions, $row->id);   
            }
        }   

        return Inertia::render('Admin/Role/Edit', [
            "title" => __($this->option),
            "subtitle" => __('role_editar'),
            "module" => $this->module,
            "slug" => 'roles',
            "role" => $role,
            "functionalities" => $functionalities,
            "modules" => $modules,
            "permissions_all" => $permissions_all,
            "module_permissions" => $module_permissions,
            "role_permissions" => $role_permissions,
            "availableLocales" => LocaleTrait::availableLocales(),
            "permissions" => $this->permissions
        ]);   
    }

    /**
     * 5. Actualizar role.
     */
    public function update(RoleUpdateRequest $request, Role $role){
        $role->description = $request->description;
        $role->save();

        return redirect()->route('roles.edit', $role->id)
            ->with('msg', __('role_actualizado_msg'));
    }

    /**
     * 6. Eliminar role.
     */
    public function destroy(Request $request, Role $role){
        $role->delete();  
        
        return redirect()->route('roles.index')
            ->with('msg', __('role_eliminado_msg'));  
    }

    /**
     * 7. Sincronizar permisos sobre los módulos.
     */
    private function syncModulePermissions($role){
        $role_permissions = $role->permissions;

        if($role_permissions->count()){
            foreach($role_permissions as $rp){
                $p = explode('.', $rp->name);

                //Omitimos los permisos sobre módulos:
                if(strpos($p[0], 'module_') === false){
                    $functionality = Functionality::select('modules.name')
                    ->join('modules', 'functionalities.module_id', '=', 'modules.id')
                    ->where('functionalities.slug', $p[0])
                    ->first();

                    if($functionality) $role->givePermissionTo('module_'.$functionality->name);
                }
            }
        }
    }

    /**
     * 8. Setear permiso para role.
     */
    public function setPermission(Request $request, Role $role){
        $validated = $request->validate([
            'permission_id' => 'required|integer|exists:permissions,id',
            'assigned' => 'required|boolean',
        ]);

        $permissionId = $validated['permission_id'];
        $assigned = $validated['assigned'];

        if($assigned){
            $role->givePermissionTo($permissionId);
        }else{
            $role->revokePermissionTo($permissionId);
        }

        return redirect()->back();
    }    

    /**
     * 9. Setear múltiples permisos.
     */
    public function setMultiplePermissions(Request $request, Role $role){
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
            'assigned' => 'required|boolean',
        ]);

        if ($validated['assigned']) {
            // Añadir permisos
            $role->syncPermissions(array_unique(array_merge(
                $role->permissions->pluck('id')->toArray(),
                $validated['permissions']
            )));
        } else {
            // Quitar permisos
            $remaining = $role->permissions
                ->pluck('id')
                ->reject(fn($id) => in_array($id, $validated['permissions']))
                ->toArray();

            $role->syncPermissions($remaining);
        }

        return response()->json(['success' => true]);
    }


    /**
     * 10. Exportar a .xls.
     */
    public function exportXls(Request $request){
        $query = $this->getExportQuery($request);
        return Excel::download(new RolesExport($query), 'roles.xlsx');
    }

    /**
     * 11. Exportar a .pdf.
     */
    public function exportPdf(Request $request){
        $query = $this->getExportQuery($request);
        $rows = $query->get(['id','name','description','created_at']);
        $pdf = Pdf::loadView('exports.roles_pdf', ['rows' => $rows])->setPaper('a4', 'portrait');
        return $pdf->download('roles.pdf');
    }

    /**
     * 12. Lógica de exportación.
     */
    private function getExportQuery(Request $request){
        $sorters = json_decode($request->input('sorters', '[]'), true) ?: [];
        $filters = json_decode($request->input('filters', '[]'), true) ?: [];

        // sort
        $sortField = 'name';
        $sortDir   = 'ASC';
        if (!empty($sorters)) {
            $sf = $sorters[0]['field'] ?? 'name';
            $sd = strtoupper($sorters[0]['dir'] ?? 'asc');
            $allowed = ['id','name','description','created_at'];
            if (in_array($sf, $allowed, true)) $sortField = $sf;
            $sortDir = $sd === 'DESC' ? 'DESC' : 'ASC';
        }

        $request->merge([
            'sort_field'     => $sortField,
            'sort_direction' => $sortDir,
        ]);

        $query = $this->dataQuery($request);

        // aplicar filtros remotos
        $mapped = [];
        foreach ($filters as $f) {
            $field = $f['field'] ?? null;
            $value = trim((string)($f['value'] ?? ''));
            if ($field && $value !== '') $mapped[$field] = $value;
        }
        if (isset($mapped['name'])) {
            $v = $mapped['name'];
            $query->where(function ($sub) use ($v) {
                $sub->where('name', 'like', "%{$v}%")->orWhere('description', 'like', "%{$v}%");
            });
        }
        if (isset($mapped['description'])) {
            $query->where('description', 'like', "%{$mapped['description']}%");
        }

        return $query;
    }
}

