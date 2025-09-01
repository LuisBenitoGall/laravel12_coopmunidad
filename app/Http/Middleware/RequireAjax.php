<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAjax{
    public function handle(Request $request, Closure $next){
        // 1) Debe ser petición AJAX/JSON
        $isAjax      = $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
        $acceptsJson = $request->expectsJson() || str_contains($request->header('Accept', ''), 'application/json');

        if (!($isAjax && $acceptsJson)) {
            abort(404); // o 403 si prefieres
        }

        // 2) (Opcional) mismo host que APP_URL — más robusto que referer
        $appHost = parse_url(config('app.url', ''), PHP_URL_HOST);
        if ($appHost && $request->getHost() !== $appHost) {
            abort(404);
        }

        return $next($request);
    }
}
