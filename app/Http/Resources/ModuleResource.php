<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Session; 
use Carbon\Carbon;

//Traits:
use App\Traits\LocaleTrait;
use App\Traits\ModulesTrait;

class ModuleResource extends JsonResource{
    /**
     * 1. Array módulos.
     */
    
    use LocaleTrait;
    use ModulesTrait;

    /**
     * 1. Array módulos.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $locale = LocaleTrait::languages(session('locale', app()->getLocale()));

        //Niveles:
        $levels = $this->levels();
        
        return [
            'id'           => $this->id,
            'name'         => $this->name,              // crudo
            'slug'         => $this->slug,
            'label'        => __($this->label),         // texto (sin HTML)
            'icon'         => $this->icon,              // crudo ("cog", "users"...)
            'icon_html'    => '<i class="la la-'.$this->icon.'"></i>', // HTML listo
            'color'        => $this->color,             // crudo "#aabbcc"
            'color_html'   => '<span class="spot" style="background-color:'.$this->color.';"></span>',
            'scope'        => $this->scope,
            'level'        => (int) $this->level,                // crudo 1/2/3
            'level_label'  => $levels[$this->level] ?? '',       // texto (“Básicos”)
            'level_html'   => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100" title="'.$this->color.'">'.$levels[$this->level] ?? ''.'</span>',
            'translations' => $this->translations,
            'explanation'  => $this->explanation,
            'status'       => (int) $this->status,
            'deleted_at'   => $this->deleted_at,
            'created_at'   => \Carbon\Carbon::parse($this->created_at)->format($locale[4]),
            'updated_at'   => \Carbon\Carbon::parse($this->updated_at)->format($locale[4]),
        ];
    }
}
