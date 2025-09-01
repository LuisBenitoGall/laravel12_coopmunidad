<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

//Models:
use App\Models\User;
use App\Models\UserError;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\SetLanguage::class,
            \App\Http\Middleware\ShareSessionData::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'only.ajax' => \App\Http\Middleware\RequireAjax::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Validación CSRF y rutas exceptuadas:
        $middleware->validateCsrfTokens(
            except: [
                'admin/communities/select',
                'admin/companies/select'
            ]
        );
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: __DIR__.'/../routes/health.php',
    )
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\App\Exceptions\CustomAuthorizationException $e, \Illuminate\Http\Request $request) {
            $user = auth()->user();

            if ($user) {
                \App\Models\UserError::create([
                    'user_id' => $user->id,
                    'error'   => 403,
                ]);

                $max = config('constants.ERROR_MAX_403_');
                $count = \App\Models\UserError::where('user_id', $user->id)
                    ->where('error', 403)
                    ->whereNull('deleted_at')
                    ->count();

                if ($count >= $max) {
                    $user->update(['status' => 0]);
                    auth()->logout();
                    return redirect()
                        ->route('login')
                        ->with('alert', __('usuario_desactivado_aviso'));
                }
            }

            if ($request->header('X-Inertia')) {
                session()->flash('alert', $e->getMessage());
                return \Inertia\Inertia::location(route('error.403'));
            }

            return redirect()->route('error.403')->with('alert', $e->getMessage());
        });
    })
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ])
    ->create();
