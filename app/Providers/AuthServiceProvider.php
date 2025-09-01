<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

//Models
use App\Models\Category;
use App\Models\Phone;

//Policies
use App\Policies\CategoryPolicy;
use App\Policies\PhonePolicy;

class AuthServiceProvider extends ServiceProvider{
    /**
     * Mapear Modelos → Policies
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Phone::class    => PhonePolicy::class,
        // Añade aquí más modelos => policies según vayas creando
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void{
        // Super Admin (Spatie): permite todo
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Si necesitas Gates sueltos, defínelos aquí.
        // Gate::define('algo', fn ($user) => true);
    }
}
