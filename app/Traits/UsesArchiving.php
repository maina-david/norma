<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait UsesArchiving
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $builder): Builder
    {
        /** @var Builder */
        return $builder->whereNull('archived_at');
    }
}
