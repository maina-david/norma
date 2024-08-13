<?php

namespace App\Http\Resources\Internals\Collaborate\Workflows;

use App\Models\Workflows\TaskType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TaskType */
class TaskTypeResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'colour' => $this->colour,
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
