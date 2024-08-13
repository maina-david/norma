<?php

namespace App\Http\ModelFilters\Compilation;

use EloquentFilter\ModelFilter;

/**
 * @method static LibraryFilter titleLike(string $title)
 */
class LibraryFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return LibraryFilter
     */
    public function search(string $search): self
    {
        return $this->titleLike($search);
    }
}
