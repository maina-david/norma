<?php

namespace App\Http\ModelFilters\Workflows;

use App\Models\Compilation\RequirementsCollection;
use EloquentFilter\ModelFilter;

/**
 * @method static TaskApplicationFilter forRequirementsCollection(RequirementsCollection $id)
 */
class TaskApplicationFilter extends ModelFilter
{
    public function jurisdiction(int $id): TaskApplicationFilter
    {
        /** @var RequirementsCollection */
        $reqCollection = RequirementsCollection::find($id);

        /** @var TaskApplicationFilter */
        return $this->forRequirementsCollection($reqCollection);
    }

    /**
     * @param array<int> $ids
     *
     * @return TaskApplicationFilter
     */
    public function types(array $ids): TaskApplicationFilter
    {
        /** @var TaskApplicationFilter */
        return $this->whereRelation('task', fn ($q) => $q->whereIn('task_type_id', $ids));
    }
}
