<?php

namespace App\Observers;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

//Models:
use App\Models\Module;

class ModuleObserver{
    public function saved(Module $m): void{
        $this->invalidateMenuCache();
    }

    public function deleted(Module $m): void{
        $this->invalidateMenuCache();
    }

    private function invalidateMenuCache(): void{
        // Si el driver soporta tags, puedes vaciar por tag (opcional)
        $store = Cache::getStore();
        if ($store instanceof TaggableStore) {
            Cache::tags(['menu'])->flush();
        }

        // Siempre sube versión: funciona con cualquier driver
        Cache::increment('secondary_menu_schema_ver');
    }
}



