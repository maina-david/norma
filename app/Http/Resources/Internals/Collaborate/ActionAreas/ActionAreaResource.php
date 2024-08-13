<?php

namespace App\Http\Resources\Internals\Collaborate\ActionAreas;

use App\Models\Actions\Pivots\ActionAreaReferenceDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Actions\ActionArea */
class ActionAreaResource extends JsonResource
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
            'title' => $this->title,
            'change_status' => $this->whenPivotLoaded(ActionAreaReferenceDraft::class, fn () => $this->pivot->change_status), // @phpstan-ignore-line
        ];
    }
}
