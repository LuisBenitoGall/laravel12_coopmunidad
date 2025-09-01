<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanySetting extends Model{
    /**
     * 1. Configuración por empresa.
     */
    
    use HasFactory;
    use SoftDeletes;

    protected $table = 'company_settings';

    protected $fillable = ['company_id', 'validate_nif', 'primary_color', 'secondary_color'];

    /**
     * 1. Configuración por empresa.
     */
    public static function companySettings($company_id){
        return CompanySetting::select('companies.name', 'companies.logo', 'companies.nif', 'company_settings.*')
        ->join('companies', 'company_settings.company_id', '=', 'companies.id')
        ->where('company_settings.company_id', $company_id)
        ->first();
    }
}
