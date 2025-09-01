<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use App\Models\Module;

class SecondaryMenuController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Contexto (para no superadmin)
        $currentCommunityId = (int) session('currentCommunity');
        $currentCompanyId   = (int) session('currentCompany');
        $ctxType = $currentCommunityId ? 'community' : ($currentCompanyId ? 'company' : 'company');

        $locale = app()->getLocale();

        // ¿Es superadmin?
        $superRoleName = config('constants.SUPER_ADMIN_'); // p.ej. 'Superadmin'
        $isSuperAdmin = false;
        if ($superRoleName && method_exists($user, 'hasRole')) {
            $isSuperAdmin = $user->hasRole($superRoleName);
        } elseif (method_exists($user, 'isSuperAdmin')) {
            $isSuperAdmin = $user->isSuperAdmin();
        }

        // Versionado de caché
        $schemaVer = Cache::get('secondary_menu_schema_ver', 1);
        $userVer   = Cache::get("secondary_menu_user_ver:{$user->id}", 1);

        $ctxId   = $currentCommunityId ?: $currentCompanyId ?: 0;
        $baseKey = "secondary_menu:v5:s{$schemaVer}:u{$userVer}:super:" . ($isSuperAdmin ? '1' : '0') .
                   ":ctx:{$ctxType}:id:{$ctxId}:user:{$user->id}:loc:{$locale}";
        $supportsTags = Cache::getStore() instanceof TaggableStore;

        // 1) Cacheamos ESTRUCTURA CRUDA (sin traducir): label_key en lugar de label
        $builder = function () use ($user, $ctxType, $isSuperAdmin) {
            $modules = Module::query()
                ->when(!$isSuperAdmin, function ($q) use ($ctxType) {
                    // No superadmin: sólo módulos de su ámbito y level 2|3
                    $q->forContext($ctxType)
                      ->whereIn('level', [2, 3]);
                })
                ->where('status', 1) // módulo activo
                ->with(['functionalities'])
                ->orderBy('id')
                ->get();

            $data = $modules->map(function ($m) use ($user, $isSuperAdmin) {
                // Filtra funcionalidades por permiso (si no es superadmin)
                $funcs = $m->functionalities
                    ->filter(fn ($f) => $isSuperAdmin ? true : $user->can($f->slug . '.index'))
                    ->map(fn ($f) => [
                        'slug'       => $f->slug,
                        'label_key'  => $f->label, // clave de traducción
                    ])
                    ->values()
                    ->all();

                if (empty($funcs)) {
                    return null;
                }

                return [
                    'slug'            => $m->slug,
                    'label_key'       => $m->label, // clave de traducción
                    'icon'            => $m->icon,  // el front lo normaliza a 'la-...'
                    'functionalities' => $funcs,
                ];
            })
            ->filter()
            ->values()
            ->all();

            return $data;
        };

        $raw = $supportsTags
            ? Cache::tags(["menu", "user:{$user->id}", "context:{$ctxType}", "ctxid:{$ctxId}"])->rememberForever($baseKey, $builder)
            : Cache::rememberForever($baseKey, $builder);

        // 2) TRADUCIMOS AHORA (fuera de caché) para que los nuevos strings se reflejen al instante
        $menu = collect($raw)->map(function ($m) {
            return [
                'slug'  => $m['slug'],
                'label' => __($m['label_key']),
                'icon'  => $m['icon'],
                'functionalities' => collect($m['functionalities'] ?? [])
                    ->map(fn ($f) => [
                        'slug'  => $f['slug'],
                        'label' => __($f['label_key']),
                    ])
                    ->values()
                    ->all(),
            ];
        })->values()->all();

        // ETag + 304 (sobre el MENÚ YA TRADUCIDO)
        $etag = '"' . sha1(json_encode($menu)) . '"';
        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'private, max-age=300, must-revalidate');
        }

        return response()->json($menu)
            ->header('ETag', $etag)
            ->header('Cache-Control', 'private, max-age=300, must-revalidate');
    }
}
