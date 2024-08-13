<?php

namespace App\Http\ModelFilters\Ontology;

use EloquentFilter\ModelFilter;

/**
 * @method static UserTagFilter titleLike(string $title)
 */
class UserTagFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return UserTagFilter
     */
    public function search(string $search): UserTagFilter
    {
        /** @var UserTagFilter */
        return $this->titleLike($search);
    }
}
