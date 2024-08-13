<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasTitleLikeScope
{
    /**
     * @param Builder $builder
     * @param string  $title
     *
     * @return Builder
     */
    public function scopeTitleLike(Builder $builder, string $title): Builder
    {
        return $builder->where('title', 'LIKE', "%{$title}%");
    }
}
