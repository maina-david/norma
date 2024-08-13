<?php

namespace App\Http\Resources\Internals\My\Actions;

use App\Http\Resources\Internals\My\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @codeCoverageIgnore
 *
 * @mixin \App\Models\Tasks\Task
 */
class TaskAssigneeResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_status' => $this->task_status,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
        ];
    }
}
