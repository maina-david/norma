<?php

namespace Database\Factories\Assess;

use App\Enums\Assess\AssessActivityType;
use App\Enums\Assess\ResponseStatus;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssessmentActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'assessment_item_response_id' => AssessmentItemResponse::factory(),
            'assessment_item_id' => AssessmentItem::factory(),
            'place_id' => Norma::factory(),
            'activity_type' => AssessActivityType::answerChange()->value,
            'from_status' => ResponseStatus::notAssessed()->value,
            'to_status' => ResponseStatus::no()->value,
        ];
    }
}
