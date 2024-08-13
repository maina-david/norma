<?php

namespace App\Http\Resources\Requirements\Consequence\V1;

use App\Http\Resources\AbstractResource;
use App\Models\Requirements\Consequence;

/**
 * @mixin Consequence
 */
class ConsequenceResource extends AbstractResource
{
    /**
     * Copied from the original transformer in the old code base - so lots of legacy here.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'consequence_type' => (int) $this->consequence_type,
            'consequence_other_detail' => $this->consequence_other_detail,
            'penalty' => false, // legacy, doesn't exist anymore
            'penalty_type' => 0, // legacy, doesn't exist anymore
            'penalty_other_details' => null, // legacy, doesn't exist anymore
            'personal_liability' => false, // legacy, doesn't exist anymore
            'amount' => $this->amount ? (int) $this->amount : null,
            'currency' => $this->currency,
            'sentence_period' => $this->sentence_period ? (int) $this->sentence_period : null,
            'sentence_period_type' => $this->sentence_period_type ? (int) $this->sentence_period_type : null,
            'per_day' => (bool) $this->per_day,
            'activated' => false, // legacy, doesn't exist anymore
            'description' => $this->toReadableString(),
            'severity' => $this->severity ? (int) $this->severity : null,
        ];
    }
}
