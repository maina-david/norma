<?php

namespace Database\Factories\Arachno;

use App\Enums\Arachno\ConsolidationFrequency;
use App\Models\Arachno\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class SourceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Source::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'source_url' => $this->faker->url(),
            'source_content' => $this->faker->text(),
            'primary_language' => 'eng',
            'consolidation_frequency' => ConsolidationFrequency::QUARTERLY->value,
        ];
    }
}
