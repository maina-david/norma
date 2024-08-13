<?php

namespace Database\Factories\Assess;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\GuidanceNote;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuidanceNoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GuidanceNote::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'assessment_item_id' => AssessmentItem::factory(),
            'description' => $this->faker->text,
            'location_id' => $this->faker->randomElement([Location::factory(), null]),
        ];
    }
}
