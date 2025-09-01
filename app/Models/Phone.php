<?php
// app/Models/Phone.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//Traits:
use App\Traits\HasPhoneTypes;

class Phone extends Model{
    use SoftDeletes, HasPhoneTypes;

    protected $table = 'phones';

    protected $fillable = [
        'e164','country_id','ext','type','label', 'is_primary','is_whatsapp','is_verified','verified_at','notes'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_whatsapp' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function phoneable() { return $this->morphTo(); }
    public function country() { return $this->belongsTo(Country::class); }

    /**
     * Defensa extra: guarda e164 sin espacios ni separadores (y 00→+).
     * El Service ya normaliza, pero esto evita sorpresas si alguien escribe directo.
     */
    public function setE164Attribute($value): void{
        if (is_null($value)) { $this->attributes['e164'] = null; return; }

        $v = trim((string)$value);
        if (str_starts_with($v, '00')) {
            $v = '+' . substr($v, 2);
        }
        $v = preg_replace('/[^0-9\+]/', '', $v) ?? '';
        $v = ltrim($v, '+');
        $v = '+' . $v;
        $this->attributes['e164'] = $v;
    }
}
