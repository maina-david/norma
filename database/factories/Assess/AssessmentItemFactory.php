<?php

namespace Database\Factories\Assess;

use App\Enums\Assess\AssessmentItemType;
use App\Enums\Assess\ReassessInterval;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssessmentItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'component_1' => $this->faker->unique()->words(3, true),
            'component_2' => $this->faker->unique()->words(3, true),
            'component_3' => $this->faker->unique()->words(3, true),
            'component_4' => $this->faker->unique()->words(3, true),
            'component_5' => $this->faker->unique()->words(3, true),
            'component_6' => $this->faker->unique()->words(3, true),
            'component_7' => $this->faker->unique()->words(3, true),
            'component_8' => $this->faker->unique()->words(3, true),
            'risk_rating' => RiskRating::low()->value,
            'frequency' => 12,
            'frequency_interval' => ReassessInterval::MONTH->value,
            'type' => AssessmentItemType::LEGACY->value,
        ];
    }
}
