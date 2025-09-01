<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunitySetting extends Model{
    /**
     * 1. Configuración por comunidad.
     */
    
    use HasFactory;
    use SoftDeletes;

    protected $table = 'community_settings';

    protected $fillable = ['community_id', 'primary_color', 'secondary_color'];

    /**
     * 1. Configuración por empresa.
     */
    public static function communitySettings($community_id){
        return CommunitySetting::select('communities.name', 'communities.slug', 'communities.nif', 'community_settings.*')
        ->join('communities', 'community_settings.community_id', '=', 'communities.id')
        ->where('community_settings.community_id', $community_id)
        ->first();
    }
}
