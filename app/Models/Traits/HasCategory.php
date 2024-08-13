<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCategory
{
    /**
     * Scope to filter the many to many categories relation.
     *
     * @param Builder               $builder
     * @param array<array-key, int> $categoryIds
     *
     * @return Builder
     */
    public function scopeHasCategories(Builder $builder, array $categoryIds): Builder
    {
        return $builder->whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereKey($categoryIds)->orWhereHas('ancestors', fn ($builder) => $builder->whereKey($categoryIds));
        });
    }
}
