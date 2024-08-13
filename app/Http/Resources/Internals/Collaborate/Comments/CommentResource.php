<?php

namespace App\Http\Resources\Internals\Collaborate\Comments;

use App\Http\Resources\Internals\Collaborate\Workflows\TaskTypeResource;
use App\Models\Comments\Collaborate\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Comment */
class CommentResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'work_expression_id' => $this->work_expression_id,
            'created_at' => $this->created_at,
            'comment' => $this->comment,
            'reference_id' => $this->reference_id,
            'id' => $this->id,
            'author_id' => $this->author_id,
            'task_type_id' => $this->task_type_id,

            'task_id' => $this->task_id,
            'taskType' => new TaskTypeResource($this->whenLoaded('taskType')),
        ];
    }
}
