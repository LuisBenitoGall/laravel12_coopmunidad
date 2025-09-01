<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class CompanyAccount extends Model{
    /**
     * 1. Tipo de cuenta.
     */
    
    use HasFactory;
    use SoftDeletes;

    protected $table = 'company_accounts';

    protected $fillable = ['company_id', 'guardian', 'account_id', 'start_date', 'end_date', 'price', 'payment_date', 'status']; 

    /**
     * 1. Tipo de cuenta.
     */
    public function account(){
        return $this->belongsTo(Account::class, 'account_id');    
    }
}


