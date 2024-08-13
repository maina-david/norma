<?php

namespace App\Http\Resources\Internals\Collaborate\Assess;

use App\Models\Assess\Pivots\AssessmentItemReferenceDraft;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AssessmentItemReferenceDraft $pivot
 *
 * @mixin \App\Models\Assess\AssessmentItem
 */
class AssessmentItemResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->description,
            'change_status' => $this->whenPivotLoaded(AssessmentItemReferenceDraft::class, fn () => $this->pivot->change_status),
        ];
    }
}
