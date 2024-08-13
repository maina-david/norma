<?php

namespace App\Http\Resources\Tasks\Task\V3;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Tasks\Task */
class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'due_at' => $this->due_on?->toDateString(),
            'priority' => $this->priority,
            'task_status' => $this->task_status,
            'frequency' => $this->frequency,
            'frequency_interval' => $this->frequency_interval->value,
            'taskable_type' => $this->taskable_type,
            'taskable_id' => $this->taskable_id,
            'link' => route('my.tasks.tasks.show', ['task' => $this->hash_id], false),
        ];
    }
}
