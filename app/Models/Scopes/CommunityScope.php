<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CommunityScope implements Scope{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void{
        if(session()->has('currentCommunity')){
            $builder->where('community_id', session('currentCommunity'));
        }
    }
}
