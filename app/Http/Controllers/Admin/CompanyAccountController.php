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
use App\Models\CompanyAccount;

//Requests:

//Traits:
use App\Traits\ConvertDateTrait;
use App\Traits\HasUserPermissionsTrait;
use App\Traits\LocaleTrait;

class CompanyAccountController extends Controller{
    /**
     * 1. Cuentas de la empresa.
     */
    
    use ConvertDateTrait;
    use LocaleTrait;

    /**
     * 1. Cuentas de la empresa.
     */
    public function index(){

    }
}
