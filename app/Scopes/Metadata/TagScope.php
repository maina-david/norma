<?php

namespace App\Scopes\Metadata;

use App\Enums\Metadata\TagType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TagScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where(function ($query) {
            $query->where('tag_type_id', TagType::general()->value)
                ->orWhereNull('tag_type_id');
        });
    }
}
