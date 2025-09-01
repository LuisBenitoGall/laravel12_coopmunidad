<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

//Exports:
use App\Exports\UsersExport;

//Models:
use App\Models\Community;
use App\Models\Company;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

//Requests:
use App\Http\Requests\UserFilterRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;

//Resources:
use App\Http\Resources\UserResource;

//Traits:
use App\Traits\ConvertDateTrait;
use App\Traits\HasUserPermissionsTrait;
use App\Traits\LocaleTrait;

class UserController extends Controller{
    /**
     * 1. Listado de usuarios.
     * 1.1. Data para exportación.
     * 1.2. Data Query.
     * 1.3. Data Tabulator.
     * 2. Formulario nuevo usuario.
     * 3. Guardar nuevo usuario.
     * 4. Mostrar usuario.
     * 5. Editar usuario. 
     * 6. Actualizar usuario.
     * 7. Eliminar usuario.
     * 8. Actualizar estado.
     * 9. Opciones de roles para nuevo usuario.
     * 10. Exportar a .xls.
     * 11. Exportar a .pdf.
     * 12. Lógica de exportación.
     */
    
    use ConvertDateTrait;
    use HasUserPermissionsTrait;
    use LocaleTrait;

    private $module = 'users';
    private $option = 'usuarios';
    protected array $permissions = [];

    public function __construct(){
        if(session('currentCompany')){
            $this->permissions = $this->resolvePermissions([
                'users.create',
                'users.destroy',
                'users.edit',
                'users.index',
                'users.search',
                'users.show',
                'users.update'
            ]);   
        } 
    }  
    
    /**
     * 1. Listado de usuarios.
     */
    public function index(UserFilterRequest $request, $company_id = false){
        $perPage = (int) $request->input('per_page', config('constants.RECORDS_PER_PAGE_DEFAULT_'));

        if (!$company_id) {
            $company_id = session('currentCompany', 'all');
        }

        $request->merge(['company_id' => $company_id]);

        $users = $this->dataQuery($request)
            ->paginate($perPage)
            ->onEachSide(1);

        return Inertia::render('Admin/User/Index', [
            "title"            => __($this->option),
            "subtitle"         => __('listado'),
            "module"           => $this->module,
            "slug"             => 'users',
            "users"            => UserResource::collection($users),
            "queryParams"      => request()->query() ?: null,
            "availableLocales" => LocaleTrait::availableLocales(),
            "permissions"      => $this->permissions,
        ]);
    }

    /**
     * 1.1. Data para exportación.
     */
    public function filteredData(UserFilterRequest $request){
        $company_id = $request->input('company_id', session('currentCompany', 'all'));
        $cacheKey   = 'filtered_users_' . md5(json_encode($request->all()));

        $request->merge(['company_id' => $company_id]);

        $users = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            return $this->dataQuery($request)->get();
        });

        return response()->json([
            'users' => UserResource::collection($users),
        ]);
    }

    /**
     * 1.2. Data Query.
     */
    private function dataQuery(Request $request): Builder{
        // mientras pruebas todo: fuerza 'all' si quieres
        $company_id = $request->input('company_id', session('currentCompany', 'all'));
        $company_id = 'all'; // <- quita este forzado cuando ya funcione todo

        $query = User::query()->with(['avatar', 'phones']);

        if ($company_id !== 'all') {
            $query->whereHas('companiesRelation', function ($q) use ($company_id) {
                $q->where('companies.id', $company_id);
            });
        }

        // Filtros si llegan como query normal (no los remotos de Tabulator)
        $filters = [
            'name'   => fn($q, $v) => $q->where(function ($sub) use ($v) {
                $sub->where('name', 'like', "%{$v}%")
                    ->orWhere('surname', 'like', "%{$v}%");
            }),
            'email'  => fn($q, $v) => $q->where('email', 'like', "%{$v}%"),
            'phones' => fn($q, $v) => $q->whereHas('phones', fn($sub) =>
                $sub->where('phone_number', 'like', "%{$v}%")
            ),
        ];
        foreach ($filters as $key => $cb) {
            if ($request->filled($key)) $cb($query, $request->input($key));
        }

        // Rangos de fechas
        $from = $request->input('date_from');
        $to   = $request->input('date_to');
        if ($from && $to) {
            $query->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"]);
        } elseif ($from) {
            $query->where('created_at', '>=', "{$from} 00:00:00");
        } elseif ($to) {
            $query->where('created_at', '<=', "{$to} 23:59:59");
        }

        // Orden por defecto (table() lo sobrescribe)
        $sortField     = $request->input('sort_field', 'name');
        $sortDirection = strtoupper($request->input('sort_direction', 'ASC'));
        $allowedSort   = ['id','name','surname','email','created_at'];

        if (!in_array($sortField, $allowedSort, true)) $sortField = 'name';
        if (!in_array($sortDirection, ['ASC','DESC'], true)) $sortDirection = 'ASC';

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
            $allowed = ['id','name','surname','email','created_at'];
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

        // Company id (mientras pruebas, puedes forzar 'all')
        $request->merge([
            'company_id'     => $request->input('company_id', session('currentCompany', 'all')),
            'sort_field'     => $sortField,
            'sort_direction' => $sortDir,
        ]);

        $query = $this->dataQuery($request);

        // Aplica filtros remotos
        if (isset($mapped['name'])) {
            $v = $mapped['name'];
            $query->where(function ($sub) use ($v) {
                $sub->where('name', 'like', "%{$v}%")
                    ->orWhere('surname', 'like', "%{$v}%");
            });
        }
        if (isset($mapped['email'])) {
            $query->where('email', 'like', "%{$mapped['email']}%");
        }
        if (isset($mapped['phones'])) {
            $v = $mapped['phones'];
            $query->whereHas('phones', fn($q) => $q->where('phone_number', 'like', "%{$v}%"));
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
     * 2. Formulario nuevo usuario.
     */
    public function create(){
        $roles = $this->roleOptions(true);

        return Inertia::render('Admin/User/Create', [
            "title" => __($this->option),
            "subtitle" => __('usuario_nuevo'),
            "module" => $this->module,
            "slug" => 'users',
            "roles" => $roles,
            "availableLocales" => LocaleTrait::availableLocales(),
            "permissions" => $this->permissions
        ]);
    }

    /**
     * 3. Guardar nuevo usuario.
     */
    public function store(UserStoreRequest $request){
        //dd($request->all());

        //Comprobamos si el rol dispone de permisos para declarar $isAdmin:
        $role = Role::find($request->input('role'));

        if(!$role){
            $alert = __('role_no_existe');
            return redirect()->route('users.create')->with(compact('alert'));
            exit;
        }

        $permissions = $role->permissions;

        $isAdmin = false;
        if($role->name == config('constants.SUPER_ADMIN_') || $permissions->count()){
            $isAdmin = true;
        }

        $status = $request->input('status')? 1:0;
        $random_password = Str::random(8);

        $user = new User();
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->email = $request->email;
        $user->password = bcrypt($random_password);
        $user->isAdmin = $isAdmin;
        $user->status = $status;
        $user->remember_token = $request->input('_token');
        $user->save();

        //Guardamos rol:
        $user->assignRole($role->name);

        //Vinculamos usuario a empresa:
        if($request->link_company){
            $uc = new UserCompany();
            $uc->user_id = $user->id;
            $uc->company_id = session('currentCompany');
            $uc->save();
        }

        //Envío de password:
        if($request->input('send_pwd')){
            $emailFrom = config('constants.EMAIL_');
            $emailTo = $user->email;
            $data['usuario'] = $user->name.' '.$user->surname;
            $data['password'] = $random_password;
            $company = Company::find(session('currentCompany'));
            $data['company'] = $company;

            Mail::send('emails.send-user-password', $data, function($message) use($emailFrom, $company, $emailTo){
                $message->from($emailFrom, $company->name);
                $message->to($emailTo);
                $message->subject(__('contrasena_envio'));
            });
        }

        return redirect()->route('users.edit', $user)->with('msg', __('usuario_creado_msg'));
    }

    /**
     * 4. Mostrar usuario.
     */
    public function show(User $user){

    }

    /**
     * 5. Editar usuario.
     */
    public function edit(User $user){
        $roles = $this->roleOptions(true);

        return Inertia::render('Admin/User/Edit', [
            "title" => __($this->option),
            "subtitle" => __('usuario_edit'),
            "module" => $this->module,
            "slug" => 'users',
            "user" => $user,
            "roles" => $roles,
            "availableLocales" => LocaleTrait::availableLocales(),
            "permissions" => $this->permissions
        ]);    
    }

    /**
     * 6. Actualizar usuario.
     */
    public function update(UserUpdateRequest $request){

    }

    /**
     * 7. Eliminar usuario.
     */    
    public function destroy(User $user){
        $user->delete();

        //REVISAR TODAS LAS VINCULACIONES DEL USUARIO QUE DEBAN ELIMINARSE. POR EJEMPLO: FOTOS DEL USUARIO.

        return redirect()->route('users.index')->with('msg', __('usuario_eliminado'));
    }

    /**
     * 8. Actualizar estado.
     */
    public function status(Request $request, $id, $status){
        $user = User::find($id);
        
        if($user){
            $user->status = $status;
            $user->save(); 

            return response()->json([
                'success' => true,
                'message' => __('estado_actualizado_ok'),
                'new_status' => $status
            ]);

        // Responder con error si el usuario no se encuentra
        }else{
            return response()->json(['error' => __('usuario_no_encontrado')], 404);
        }
    }

    /**
     * 9. Opciones de roles para nuevo usuario.
     */
    public function roleOptions($is_array = false){
        if(auth()->user()->hasRole(config('constants.SUPER_ADMIN_'))){
            $roles = Role::orderBy('id', 'ASC')
            ->get();

        //Revisar los casos para obtener sólo los roles pertinentes para cada usuario.
        }else{
            $roles = Role::where('name', '!=', config('constants.SUPER_ADMIN_'))
            ->orderBy('id', 'ASC')
            ->get();
        }

        //Conversión a array:
        if($is_array){
            //Mapeamos para obtener sólo el string, sin el company_id que lo precede
            $roles = $roles->mapWithKeys(function ($role) {
                $nameParts = explode('/', $role->name);
                return [$role->id => $nameParts[1] ?? $role->name];
            })->toArray();
        }

        return $roles;
    }

    /**
     * 10. Exportar a .xls.
     */
    public function exportXls(Request $request){
        $query = $this->getExportQuery($request);
        return Excel::download(new UsersExport($query), 'usuarios.xlsx');
    }

    /**
     * 11. Exportar a .pdf.
     */
    public function exportPdf(Request $request){
        $query = $this->getExportQuery($request);
        $rows = $query->get(['id','name','surname','email','created_at']);
        $pdf = Pdf::loadView('exports.users_pdf', ['rows' => $rows])->setPaper('a4', 'portrait');
        return $pdf->download('usuarios.pdf');
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
            $allowed = ['id','name','surname','email','created_at'];
            if (in_array($sf, $allowed, true)) $sortField = $sf;
            $sortDir = $sd === 'DESC' ? 'DESC' : 'ASC';
        }

        $request->merge([
            'company_id'     => $request->input('company_id', session('currentCompany', 'all')),
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
                    ->orWhere('surname', 'like', "%{$v}%");
            });
        }
        if (isset($mapped['email'])) {
            $query->where('email', 'like', "%{$mapped['email']}%");
        }
        if (isset($mapped['phones'])) {
            $v = $mapped['phones'];
            $query->whereHas('phones', fn($q) => $q->where('phone_number', 'like', "%{$v}%"));
        }

        return $query;
    }

}
