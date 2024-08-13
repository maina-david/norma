<?php

namespace Database\Factories\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentItemResponseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssessmentItemResponse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'assessment_item_id' => AssessmentItem::factory(),
            'place_id' => Libryo::factory(),
            'answer' => ResponseStatus::yes(),
            'next_due_at' => null,
        ];
    }
}
