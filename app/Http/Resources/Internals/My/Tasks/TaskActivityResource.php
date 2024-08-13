<?php

namespace App\Http\Resources\Internals\My\Tasks;

use App\Http\Resources\Internals\My\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Tasks\TaskActivity */
class TaskActivityResource extends JsonResource
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
            'created_at' => $this->created_at,
            'description' => $this->toText(),
            'activity_type' => $this->activity_type,
            'user_id' => $this->user_id,
            'place_id' => $this->place_id,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
