<?php

namespace App\Http\ModelFilters\Geonames;

use EloquentFilter\ModelFilter;

/**
 * @method static LocationFilter titleLike(string $title)
 */
class LocationFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        return $this->titleLike($search);
    }

    /**
     * @param string $selected
     *
     * @return self
     */
    public function countries(string $selected): self
    {
        /** @var static */
        return $this->whereNull('parent_id');
    }
}
