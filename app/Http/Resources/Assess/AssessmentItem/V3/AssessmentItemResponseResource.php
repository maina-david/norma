<?php

namespace App\Http\Resources\Assess\AssessmentItem\V3;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Assess\AssessmentItemResponse */
class AssessmentItemResponseResource extends JsonResource
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
            'stream_id' => $this->place_id,
            'answer' => $this->answer,
            'last_answered_at' => $this->answered_at?->toDateString(),
            'next_due_at' => $this->next_due_at?->toDateString(),
            'frequency' => $this->frequency,
            'frequency_interval' => $this->frequency_interval,
            'link' => route('my.normas.activate.redirect', [
                'norma' => $this->place_id,
                'redirect' => route('my.assess.assessment-item-responses.show', ['aiResponse' => $this->hash_id], false),
            ], false),
        ];
    }
}
