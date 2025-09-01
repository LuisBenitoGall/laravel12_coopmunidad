<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

//Models:
use App\Models\Module;
use App\Models\Functionality;

//Observers:
use App\Observers\ModuleObserver;
use App\Observers\FunctionalityObserver;

class AppServiceProvider extends ServiceProvider{
    /**
     * Register any application services.
     */
    public function register(): void{
        // Bindings de contenedor, macros, singletons, etc.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void{
        // Locale desde sesión (fallback a config/app.php)
        $locale = session('locale', config('app.locale'));
        App::setLocale($locale);

        // Props globales para Inertia
        Inertia::share([
            'msg'               => fn () => session('msg'),
            'alert'             => fn () => session('alert'),
            'new_functionality' => fn () => session()->get('new_functionality'),
            // Puedes añadir aquí otras props globales si las necesitas
        ]);

        Vite::prefetch(concurrency: 3);
        
        Functionality::observe(FunctionalityObserver::class);
        Module::observe(ModuleObserver::class);
    }
}

