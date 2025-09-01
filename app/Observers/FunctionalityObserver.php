<?php

namespace App\Observers;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use App\Models\Functionality;

class FunctionalityObserver{
    public function saved(Functionality $f): void   { $this->invalidateMenuCache(); }
    public function deleted(Functionality $f): void { $this->invalidateMenuCache(); }

    private function invalidateMenuCache(): void{
        $store = Cache::getStore();
        if ($store instanceof TaggableStore) {
            Cache::tags(['menu'])->flush(); // solo si el driver lo soporta
        }
        // Versionado universal (vale con cualquier driver)
        Cache::increment('secondary_menu_schema_ver');
    }
}
