<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

//Models:
use App\Models\Community;
use App\Models\CommunitySetting;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\UserCommunity;
use App\Models\UserCompany;

//Requests:
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller{
    /**
     * Display the login view.
     */
    public function create(): Response{
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse{
        $request->authenticate();   //Va a app\Http\Requests\Auth\LoginRequest.php

        $request->session()->regenerate();

        //Empresas vinculadas al usuario:
        $companies = UserCompany::userCompanies();

        //Empresa actual:
        if($companies->count() == 1){
            session(['currentCompany' => $companies[0]->id]); 

            //Configuración de la empresa:
            $settings = CompanySetting::companySettings($companies[0]->id);
            session(['companySettings' => $settings]);
        }

        //Comunidades vinculadas al usuario:
        $communities = UserCommunity::userCommunities();

        //Comunidad actual:
        if($communities->count() == 1){
            session(['currentCommunity' => $communities[0]->id]); 

            //Configuración de la comunidad:
            $settings = CommunitySetting::communitySettings($communities[0]->id);
            session(['communitySettings' => $settings]);
        }

        //El usuario debe estar vinculado a alguna comunidad o empresa:
        if($companies->count() == 0 && $communities->count() == 0){
            session()->flash('error', __('usuario_sin_vinculacion'));
            $this->destroy(request());    
            return redirect()->intended(route('home', absolute: false));
        }

        return redirect()->intended(route('dashboard.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse{
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
