<?php

namespace App\Http\ModelFilters\Customer;

use EloquentFilter\ModelFilter;

/**
 * @method static TeamFilter titleLike(string $title)
 */
class TeamFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return TeamFilter
     */
    public function search(string $search): TeamFilter
    {
        return $this->titleLike($search);
    }
}
