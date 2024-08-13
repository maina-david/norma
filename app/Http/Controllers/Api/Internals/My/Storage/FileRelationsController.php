<?php

namespace App\Http\Controllers\Api\Internals\My\Storage;

use App\Http\Resources\Internals\My\Storage\FileResource;
use App\Models\Comments\Comment;
use App\Models\Tasks\Task;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FileRelationsController
{
    /** @var array<string, class-string> */
    protected array $allowed = [
        'task' => Task::class,
        'comment' => Comment::class,
    ];

    /**
     * Get the listing of the given relation.
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

        return FileResource::collection($model->files()->with(['folder'])->paginate());
    }
}
