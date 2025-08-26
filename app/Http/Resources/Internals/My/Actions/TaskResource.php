<?php

namespace App\Http\Resources\Internals\My\Actions;

use App\Http\Resources\Internals\My\Auth\UserResource;
use App\Models\Corpus\Reference;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @codeCoverageIgnore
 *
 * @mixin \App\Models\Tasks\Task
 */
class TaskResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @throws Exception
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hash_id' => $this->hash_id,
            'title' => $this->title,
            'sub_title' => $this->whenLoaded('taskable', function () {
                return match ($this->taskable_type) {
                    (new Reference())->getMorphClass() => $this->taskable->refPlainText->plain_text ?? '',
                    default => null,
                };
            }),
            'norma_title' => $this->whenLoaded('norma', fn () => $this->norma->title ?? ''),
            'project' => new TaskProjectResource($this->whenLoaded('project')),
            'impact' => $this->impact,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'due_on' => $this->due_on,
            'priority' => $this->priority,
            'task_status' => $this->task_status,
            'taskable_type' => $this->taskable_type,
            'taskable_id' => $this->taskable_id,
            'task_project_id' => $this->task_project_id,
            'assigned_to_id' => $this->assigned_to_id,
            'place_id' => $this->place_id,

            'frequency' => $this->frequency,
            'frequency_interval' => $this->frequency_interval,
            'source_task_id' => $this->source_task_id,

            'archived' => $this->archived,
            'updated_at' => $this->updated_at,
            'completed_at' => $this->completed_at,

            'previous_task_id' => $this->previous_task_id,
            'action_area_id' => $this->action_area_id,
            'author_id' => $this->author_id,

            'author' => new UserResource($this->whenLoaded('author')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'followers' => UserResource::collection($this->whenLoaded('watchers')),

            'recurrence_rule' => $this->getHumanReadableRRule(),
        ];
    }
}
