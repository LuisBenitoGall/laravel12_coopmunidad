<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//Concerns:
use App\Models\Concerns\HasCategories;

class Content extends Model{
    /**
     * 
     */
    
    use HasCategories;
    use SoftDeletes;

    protected $table = 'contents';

    protected $dates = ['deleted_at'];
}
