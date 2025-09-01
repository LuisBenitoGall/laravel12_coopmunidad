<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model{
    use SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'name', 'slug', 'color', 'description', 'created_by',
    ];

    // Genera slug en creación si no se pasa
    protected static function booted(): void{
        static::creating(function (Category $category) {
            if (blank($category->slug)) {
                $base = Str::slug($category->name);
                $slug = $base;
                $i = 2;
                while (self::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $category->slug = $slug;
            }
        });
    }

    // Accesores sencillos
    public function getDisplayColorAttribute(): string{
        return $this->color ?: '#64748b'; // default slate-500
    }

    /** Relaciones inversas (opcional listar algunas de interés) */
    public function companies(){
        return $this->morphedByMany(\App\Models\Company::class, 'categorizable')->withTimestamps();
    }

    public function communities(){
        return $this->morphedByMany(\App\Models\Community::class, 'categorizable')->withTimestamps();
    }

    public function users(){
        return $this->morphedByMany(\App\Models\User::class, 'categorizable')->withTimestamps();
    }

    public function contents(){
        return $this->morphedByMany(\App\Models\Content::class, 'categorizable')->withTimestamps();
    }

    /** Scopes útiles */
    public function scopeSearch($query, ?string $term){
        if (blank($term)) return $query;
        $term = trim($term);
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('slug', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
