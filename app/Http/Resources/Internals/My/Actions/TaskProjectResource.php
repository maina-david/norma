<?php

namespace App\Http\Resources\Internals\My\Actions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @codeCoverageIgnore
 *
 * @mixin \App\Models\Tasks\TaskProject
 */
class TaskProjectResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'id' => $this->id,
        ];
    }
}
