<?php

namespace Database\Factories\Notify;

use App\Models\Corpus\Doc;
use App\Models\Notify\UpdateCandidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UpdateCandidate>
 */
class UpdateCandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'doc_id' => Doc::factory(),
            'manual' => true,
            'justification_status' => true,
            'justification' => '',
            'affected_legislation_type' => 1,
            'affected_available' => false,
            'affected_legislation' => [],
            'checked_comment' => '',
            'notification_status' => '',
        ];
    }
}
