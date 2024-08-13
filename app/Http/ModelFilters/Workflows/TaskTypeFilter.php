<?php

namespace App\Http\ModelFilters\Workflows;

use EloquentFilter\ModelFilter;

class TaskTypeFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var static */
        return $this->where('name', 'like', "%{$search}%");
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function parents(string $value): self
    {
        /** @var static */
        return $this->where('is_parent', true);
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function children(string $value): self
    {
        /** @var static */
        return $this->where('is_parent', false);
    }
}
