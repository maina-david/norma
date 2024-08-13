<?php

namespace App\Http\Controllers\Api\Internals\My\Activities;

use App\Http\Resources\Internals\My\Tasks\TaskActivityResource;
use App\Models\Tasks\Task;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController
{
    /** @var array<string, class-string> */
    protected array $allowed = [
        'task' => Task::class,
    ];

    /** @var array<string, class-string> */
    protected array $resources = [
        'task' => TaskActivityResource::class,
    ];

    /**
     * Get the activities for the given relation.
     *
     * @param string $relation
     * @param int    $related
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(string $relation, int $related): AnonymousResourceCollection
    {
        abort_unless(in_array($relation, array_keys($this->allowed)), 404);

        /** @var Task $model */
        $model = $this->allowed[$relation]::findOrFail($related);

        $items = $model->activities()
            ->with(['user'])
            ->orderByDesc('created_at')
            ->simplePaginate(25);

        return $this->resources[$relation]::collection($items);
    }
}
