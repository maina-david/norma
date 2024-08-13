<?php

namespace Database\Factories\Corpus;

use App\Models\Arachno\Source;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Corpus\CatalogueDoc>
 */
class CatalogueDocFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'source_unique_id' => $this->faker->word(),
            'source_id' => Source::factory(),
            'language_code' => 'eng',
            'summary' => $this->faker->paragraph(),
            'primary_location_id' => Location::factory(),
        ];
    }
}
