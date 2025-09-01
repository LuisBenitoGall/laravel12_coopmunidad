<?php

namespace App\Policies;

//Models:
use App\Models\User;

class CategoryPolicy{
    /**
     * Create a new policy instance.
     */
    public function __construct(){
        /**
         * 
         */
    }

    public function viewAny(User $user): bool{
        return $user->hasRole('admin'); // solo admins pueden ver todas las categorías
    }

    public function view(User $user, Category $category): bool{
        return true; // cualquiera puede ver una categoría
    }

    public function create(User $user): bool{
        return $user->hasPermission('category.create');
    }

    public function update(User $user, Category $category): bool{
        return $user->id === $category->created_by || $user->hasRole('admin');
    }

    public function delete(User $user, Category $category): bool{
        return $user->hasRole('admin');
    }
}
