<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

//Concerns:
use App\Models\Concerns\HasCategories;

class Community extends Model{
    /**
     * 1. Población.
     * 2. Usuarios.
     * 3. Creada por.
     * 4. Actualizada por.
     * 5. Teléfonos de la comunidad.
     */

    use HasCategories;
    use HasFactory; 
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'cp',
        'town_id',
        'nif',
        'created_by',
        'updated_by',
        'status',
    ];

    /**
     * 1. Población.
     */
    public function town(){
        return $this->belongsTo(Town::class);
    }

    /**
     * 2. Usuarios.
     */
    public function users(){
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * 3. Creada por.
     */
    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 4. Actualizada por.
     */
    public function updater(){
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 5. Teléfonos de la comunidad.
     */
    public function phones(){
        return $this->morphMany(\App\Models\Phone::class, 'phoneable')
            ->orderByDesc('is_primary');
    }

}

