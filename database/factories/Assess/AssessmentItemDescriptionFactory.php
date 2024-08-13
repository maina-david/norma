<?php

namespace Database\Factories\Assess;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemDescription;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssessmentItemDescription>
 */
class AssessmentItemDescriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'assessment_item_id' => AssessmentItem::factory(),
            'location_id' => Location::factory(),
            'description' => $this->faker->paragraph(),
        ];
    }
}
