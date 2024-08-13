<?php

namespace App\Http\ModelFilters\Collaborators;

use EloquentFilter\ModelFilter;

class GroupFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var static */
        return $this->where(function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%');
            $query->orWhere('description', 'like', '%' . $search . '%');
        });
    }
}
