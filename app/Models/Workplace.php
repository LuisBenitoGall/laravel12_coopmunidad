<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workplace extends Model{
    /**
     * 1. Teléfonos del centro de trabajo.
     */
    
    use SoftDeletes;

    protected $table = 'workplaces';

    protected $fillable = ['name', 'slug','company_id', 'featured', 'logo', 'address', 'cp', 'town_id', 'nif', 'website', 'description', 'status'];

    /**
     * 1. Teléfonos del centro de trabajo.
     */
    public function phones(){
        return $this->morphMany(\App\Models\Phone::class, 'phoneable')
            ->orderByDesc('is_primary');
    }
}
