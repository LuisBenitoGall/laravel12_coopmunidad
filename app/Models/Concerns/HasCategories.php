<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

//Models:
use App\Models\Category;

trait HasCategories{
    public function categories(): MorphToMany{
        return $this->morphToMany(Category::class, 'categorizable')->withTimestamps();
    }

    /** Helpers */
    public function syncCategoriesByIds(array $categoryIds): void{
        $this->categories()->sync(array_filter($categoryIds));
    }

    public function attachCategoriesByIds(array $categoryIds): void{
        $this->categories()->syncWithoutDetaching(array_filter($categoryIds));
    }

    public function detachCategoriesByIds(array $categoryIds): void{
        $this->categories()->detach(array_filter($categoryIds));
    }

    public function syncCategoriesBySlugs(array $slugs): void{
        $ids = Category::whereIn('slug', array_filter($slugs))->pluck('id')->all();
        $this->syncCategoriesByIds($ids);
    }
}
