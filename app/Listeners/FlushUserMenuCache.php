<?php

namespace App\Listeners;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class FlushUserMenuCache{
    /**
     * Create the event listener.
     */
    public function __construct(){
        //
    }

    public function handle($event): void{
        // $user = property_exists($event, 'user') ? $event->user : null;
        // if ($user) {
        //     Cache::tags(['menu', "user:{$user->id}"])->flush();
        // } else {
        //     // fallback: si no viene el user, vacía todo menú
        //     Cache::tags(['menu'])->flush();
        // }
        
        // Los eventos de Spatie traen el "model" sujeto del attach/detach.
        $model = property_exists($event, 'model') ? $event->model : null;
        if (! $model instanceof Authenticatable) {
            return; // si asignas roles a otros modelos, ignora
        }

        $userId = $model->getAuthIdentifier();
        $store = Cache::getStore();

        // Si el driver soporta tags (Redis/Memcached), vaciamos por tag del usuario.
        if ($store instanceof TaggableStore) {
            Cache::tags(['menu', "user:{$userId}"])->flush();
        }

        // Fallback universal (file/database cache): subimos versión del menú del usuario.
        Cache::increment("secondary_menu_user_ver:{$userId}");
    }
}

