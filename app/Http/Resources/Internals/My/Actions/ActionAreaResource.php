<?php

namespace App\Http\Resources\Internals\My\Actions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Actions\ActionArea */
class ActionAreaResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'related_label' => $this->display_label, // @phpstan-ignore-line
            'subject_label' => $this->subject_label, // @phpstan-ignore-line
            'control_label' => $this->control_label, // @phpstan-ignore-line
            'subject_icon' => $this->subject_icon ?? 'hashtag',
            'control_icon' => $this->control_icon ?? 'hashtag',
            'tasks_count' => $this->whenCounted('tasks'),
            'references_count' => $this->whenCounted('references'),
            'tasks' => TaskAssigneeResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
