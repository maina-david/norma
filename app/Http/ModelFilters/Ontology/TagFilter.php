<?php

namespace App\Http\ModelFilters\Ontology;

use EloquentFilter\ModelFilter;

/**
 * @method static TagFilter search(string $search)
 * @method static TagFilter titleLike(string $search)
 */
class TagFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return TagFilter
     */
    public function search(string $search): TagFilter
    {
        /** @var TagFilter */
        return $this->titleLike($search);
    }
}
