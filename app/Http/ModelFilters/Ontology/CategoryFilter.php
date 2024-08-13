<?php

namespace App\Http\ModelFilters\Ontology;

use App\Traits\Bookmarks\UsesBookmarksModelFilter;
use EloquentFilter\ModelFilter;

class CategoryFilter extends ModelFilter
{
    use UsesBookmarksModelFilter;

    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var self */
        return $this->where(function ($builder) use ($search) {
            $builder->where('display_label', 'like', "%{$search}%")
                ->orWhereRelation('descriptions', 'description', 'like', "%{$search}%");
        });
    }
}
