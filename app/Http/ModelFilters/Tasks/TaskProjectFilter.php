<?php

namespace App\Http\ModelFilters\Tasks;

use EloquentFilter\ModelFilter;

/**
 * @method static TaskProjectFilter search(string $search)
 * @method static TaskProjectFilter archived(string $archived)
 */
class TaskProjectFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return TaskProjectFilter
     */
    public function search(string $search): TaskProjectFilter
    {
        // @phpstan-ignore-next-line
        return $this->where(function ($q) use ($search) {
            $q->titleLike($search);
            $q->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    /**
     * @param string $archived
     *
     * @return TaskProjectFilter
     */
    public function archived(string $archived): TaskProjectFilter
    {
        $archived = $archived === 'true' ? true : false;

        // @phpstan-ignore-next-line
        return $this->where('archived', $archived);
    }
}
