<?php

namespace App\Http\Resources\Assess\AssessmentItem\V3;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Assess\AssessmentItem */
class AssessmentItemResource extends JsonResource
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
            'id' => $this->id,
            'description' => $this->toDescription(),
            'risk_rating' => $this->risk_rating,
            'responses' => AssessmentItemResponseResource::collection($this->assessmentResponses),
        ];
    }
}
