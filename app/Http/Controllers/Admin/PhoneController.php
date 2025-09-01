<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

//Models:
use App\Models\Phone;

//Policies
use App\Policies\PhonePolicy;

//Requests:
use App\Http\Requests\Admin\Phone\PhoneStoreRequest;

//Services:
use App\Services\PhoneService;

//Traits:
use App\Traits\HasPhoneTypes;
use App\Traits\HasUserPermissionsTrait;
use App\Traits\LocaleTrait;

class PhoneController extends Controller{
    /**
     * 1. Guardar teléfono.
     */
    
    use ConvertDateTrait;
    use HasPhoneTypes;
    use LocaleTrait;

    /**
     * 1. Guardar teléfono.
     */
    public function store(PhoneStoreRequest $request, PhoneService $service){
        $phone = $service->create($request->validated());
        return back()->with('msg', __('telf_guardado'));
    }
}
