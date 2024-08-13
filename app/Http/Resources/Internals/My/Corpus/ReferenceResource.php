<?php

namespace App\Http\Resources\Internals\My\Corpus;

use App\Http\Resources\Internals\My\Actions\TaskAssigneeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Corpus\Reference */
class ReferenceResource extends JsonResource
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
            'work_id' => $this->work_id,
            'title' => $this->title, // @phpstan-ignore-line
            'work_title' => $this->work_title, // @phpstan-ignore-line
            'flag' => $this->flag, // @phpstan-ignore-line
            'tasks_count' => $this->whenCounted('tasks'),
            'tasks' => TaskAssigneeResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
