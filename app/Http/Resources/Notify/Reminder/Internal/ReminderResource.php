<?php

namespace App\Http\Resources\Notify\Reminder\Internal;

use App\Http\Resources\Internals\My\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Notify\Reminder */
class ReminderResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reminded' => $this->reminded,
            'description' => $this->description,
            'completed_by' => $this->completed_by,
            'created_at' => $this->created_at,
            'remind_on' => $this->remind_on,
            'title' => $this->title,
            'organisation_id' => $this->organisation_id,
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'completed_at' => $this->completed_at,

            'author' => new UserResource($this->whenLoaded('author')),
        ];
    }
}
