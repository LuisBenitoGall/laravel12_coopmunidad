<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

//Exports:
use App\Exports\ModulesExport;

//Models:
use App\Models\Functionality;
use App\Models\Module;
use Spatie\Permission\Models\Permission;

//Requests:
use App\Http\Requests\ModuleFilterRequest;
use App\Http\Requests\ModuleStoreRequest;
use App\Http\Requests\ModuleUpdateRequest;

//Resources:
use App\Http\Resources\FunctionalityResource;
use App\Http\Resources\ModuleResource;

//Traits:
use App\Traits\HasUserPermissionsTrait;
use App\Traits\LocaleTrait;
use App\Traits\ModulesTrait;

class ModuleController extends Controller{
    /**
     * 1. Listado de módulos.
     * 1.1. Data para exportación.
     * 1.2. Data Query.
     * 1.3. Data Tabulator.
     * 2. Formulario nuevo módulo.
     * 3. Guardar nuevo módulo.
     * 4. Editar módulo.
     * 5. Actualizar módulo.
     * 6. Eliminar módulo.
     * 7. Actualizar estado.
     * 8. Exportar a .xls.
     * 9. Exportar a .pdf.
     * 10. Lógica de exportación.
     */
    
    use HasUserPermissionsTrait;
    use LocaleTrait;
    use ModulesTrait;

    private $module = 'settings';
    private $option = 'configuracion';
    protected array $permissions = [];

    public function __construct(){
        if(session('currentCompany')){
            $this->permissions = $this->resolvePermissions([
                'modules.create',
                'modules.destroy',
                'modules.edit',
                'modules.index',
                'modules.search',
                'modules.show',
                'modules.update'
            ]);   
        } 
    }  

    /**
     * 1. Listado de módulos.
     */
    public function index(ModuleFilterRequest $request){
        $perPage = $request->input('per_page', config('constants.RECORDS_PER_PAGE_DEFAULT_'));
        
        $modules = $this->dataQuery($request)->paginate($perPage)->onEachSide(1);

        //Niveles:
        $levels = $this->levels();

        return Inertia::render('Admin/Module/Index', [
            "title" => __($this->option),
            "subtitle" => __('modulos'),
            "module" => $this->module,
            "slug" => 'modules',
            "modules" => ModuleResource::collection($modules),
            "levels" => $levels,
            "availableLocales" => LocaleTrait::availableLocales(),
            "permissions" => $this->permissions,
        ]);
    }

    /**
     * 1.1. Data para exportación.
     */
    public function filteredData(ModuleFilterRequest $request){
        $cacheKey = 'filtered_modules_' . md5(json_encode($request->all()));

        $modules = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            return $this->dataQuery($request)->get();
        });

        return response()->json([
            'modules' => ModuleResource::collection($modules)
        ]);
    }

    /**
     * 1.2. Data Query.
     */
    private function dataQuery(Request $request){
        $user = auth()->user();

        //Niveles:
        $levels = $this->levels();

        $query = Module::query();

        // Filtros dinámicos
        $filters = [
            'label' => fn($q, $v) => $q->whereRaw('LOWER(label) LIKE ?', ['%' . strtolower($v) . '%']),
            'level' => fn($q, $v) => $q->where('level', $v)
        ];

        foreach ($filters as $key => $callback) {
            if ($request->filled($key)) {
                $callback($query, $request->input($key));
            }
        }

        // Filtros por rangos de fechas dinámicos
        $dateFilters = [
            'created_at' => ['date_from', 'date_to']
        ];

        foreach ($dateFilters as $column => [$fromKey, $toKey]) {
            $from = $request->input($fromKey);
            $to = $request->input($toKey);

            if ($from && $to) {
                $query->whereBetween($column, ["$from 00:00:00", "$to 23:59:59"]);
            } elseif ($from) {
                $query->where($column, '>=', "$from 00:00:00");
            } elseif ($to) {
                $query->where($column, '<=', "$to 23:59:59");
            }
        }

        // Ordenación
        $sortField = $request->input('sort_field', 'name');
        $sortDirection = $request->input('sort_direction', 'ASC');
        $allowedSortFields = ['label', 'level'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'label';
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
            // 👉 admite también 'level'
            $allowed = ['id','name','slug','label','level','scope','explanation','status','created_at'];
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

        // Filtros adicionales que Tabulator envía
        if (isset($mapped['name'])) {
            $v = $mapped['name'];
            $query->where(function ($sub) use ($v) {
                $sub->where('name', 'like', "%{$v}%")
                    ->orWhere('slug', 'like', "%{$v}%")
                    ->orWhere('label', 'like', "%{$v}%");
            });
        }
        if (isset($mapped['level'])) {
            $query->where('level', $mapped['level']); // exacto; si quieres like, cámbialo
        }
        if (isset($mapped['scope'])) {
            $query->where('scope', 'like', "%{$mapped['scope']}%");
        }
        if (isset($mapped['explanation'])) {
            $query->where('explanation', 'like', "%{$mapped['explanation']}%");
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)->onEachSide(1);

        // ✅ TRANSFORMA LAS FILAS CON EL MISMO RESOURCE
        $rows = ModuleResource::collection($paginator->getCollection())->resolve();

        // 👇 shape que Tabulator espera con pagination:"remote"
        return response()->json([
            'data'      => $rows,
            'last_page' => $paginator->lastPage(),
            'page'      => $paginator->currentPage(),
            'per_page'  => $paginator->perPage(),
            'total'     => $paginator->total(),
        ]);
    }

    /**
     * 2. Formulario nuevo módulo.
     */
    public function create(){
        //Niveles:
        $levels = $this->levels();

        //Ambitos:
        $scopes = $this->scopes();

        return Inertia::render('Admin/Module/Create', [
            "title" => __($this->option),
            "subtitle" => __('modulo_nuevo'),
            "module" => $this->module,
            "slug" => 'modules',
            "levels" => $levels,
            "scopes" => $scopes,
            "availableLocales" => LocaleTrait::availableLocales(),
            "permissions" => $this->permissions
        ]);        
    }

    /**
     * 3. Guardar nuevo módulo.
     */
    public function store(ModuleStoreRequest $request){
        //Guardando módulo:
        $module = Module::saveModule($request);      

        return redirect()->route('modules.edit', $module->id)
            ->with('msg', __('modulo_creado_msg'));
    }

    /**
     * 4. Editar módulo.
     */
    public function edit(Module $module, $tab = false){
        $locale = LocaleTrait::languages(session('locale', app()->getLocale()));

        //Formateo de datos:
        $module->formatted_created_at = Carbon::parse($module->created_at)->format($locale[4].' H:i:s');
        $module->formatted_updated_at = Carbon::parse($module->updated_at)->format($locale[4].' H:i:s');

        $module->created_by_name = optional($module->createdBy)->full_name ?? false;
        $module->updated_by_name = optional($module->updatedBy)->full_name ?? false;

        //Niveles:
        $levels = $this->levels();

        //Funcionalidades:
        $functionalities = FunctionalityResource::collection(
            Functionality::getByModule($module->id)
        );

        return Inertia::render('Admin/Module/Edit', [
            "title" => __($this->option),
            "subtitle" => __('modulo_editar'),
            "module" => $this->module,
            "slug" => 'modules',
            "module_data" => $module,
            "tab" => $tab,
            "levels" => $levels,
            "functionalities" => $functionalities,
            "availableLocales" => LocaleTrait::availableLocales(),
            "permissions" => $this->permissions,
            "columnPreferences" => UserColumnPreference::forUserAndTables(
                auth()->user()->id,
                ['tblFunctionalities'] 
            )
        ]);       
    }

    /**
     * 5. Actualizar módulo.
     */
    public function update(ModuleUpdateRequest $request, Module $module){
        try{
            $validated = $request->validated();

            $slug = Str::slug($request->name);

            $module->name = strtolower($request->name);
            $module->slug = $slug;
            $module->label = strtolower($request->label);
            $module->color = $request->color;
            $module->icon = $request->icon;
            $module->scope = $request->scope;
            $module->explanation = $request->explanation;
            $module->status = filter_var($request->status, FILTER_VALIDATE_BOOLEAN)? 1:0;
            $module->save();

            //Guardar .json de módulos opcionales:
            //$this->jsonOptionalModules();

            return redirect()->route('modules.edit', $module)
            ->with('msg', __('modulo_actualizado_msg'));

        }catch(\Throwable $e){
            Log::error('Error en update(): ' . $e->getMessage());
            abort(500, 'Error interno del servidor');
        }
    }

    /**
     * 6. Eliminar módulo.
     */
    public function destroy(Request $request, Module $module){
        $module->delete();

        //Mantenimiento de filtros:
        $queryParams = $request->only([
            'label', 'level', 'date_from', 'date_to', 'sort_field', 'sort_direction', 'per_page', 'page'
        ]);

        return redirect()->route('modules.index', $queryParams)->with('msg', __('modulo_eliminado'));
    }

    /**
     * 7. Actualizar estado.
     */
    public function status(Request $request){
        $validated = $request->validate([
            'id' => 'required|exists:modules,id',
            'status' => 'required|in:0,1'
        ]);

        $module = Module::find($validated['id']);
        $module->status = $validated['status'];
        $module->save();
        $module->refresh();

        return response()->json([
            'success' => true,
            'message' => __('estado_actualizado_ok'),
            'new_status' => $module->status
        ]);
    }

    /**
     * 8. Exportar a .xls.
     */
    public function exportXls(Request $request){
        $query = $this->getExportQuery($request);
        $levels = $this->levels();
        $scopes = $this->scopes();
        return Excel::download(new ModulesExport($query, $levels, $scopes), 'modulos.xlsx');
    }

    /**
     * 9. Exportar a .pdf.
     */
    public function exportPdf(Request $request){
        $query = $this->getExportQuery($request);
        $rows = $query->get(['id','label','color','icon','scope','level','explanation','created_at']);
        $pdf = Pdf::loadView('exports.modules_pdf', ['rows' => $rows, 'levels' => $this->levels(), 'scopes' => $this->scopes()])->setPaper('a4', 'portrait');
        return $pdf->download('modulos.pdf');
    }

    /**
     * 10. Lógica de exportación.
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
            $allowed = ['id','name','label','color','created_at'];
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
                $sub->where('name', 'like', "%{$v}%")
                    ->orWhere('slug', 'like', "%{$v}%")
                    ->orWhere('label', 'like', "%{$v}%");
            });
        }
        if (isset($mapped['level'])) {
            $query->where('level', $mapped['level']); // exacto; si quieres like, cámbialo
        }
        if (isset($mapped['scope'])) {
            $query->where('scope', 'like', "%{$mapped['scope']}%");
        }
        if (isset($mapped['explanation'])) {
            $query->where('explanation', 'like', "%{$mapped['explanation']}%");
        }

        return $query;
    }
}
