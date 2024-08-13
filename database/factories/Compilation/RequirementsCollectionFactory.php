<?php

namespace Database\Factories\Compilation;

use App\Models\Compilation\RequirementsCollection;
use App\Models\Geonames\LocationType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compilation\RequirementsCollection>
 */
class RequirementsCollectionFactory extends Factory
{
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (RequirementsCollection $collection) {
            $collection->slug = Str::slug($collection->title);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(4, true),
            'location_type_id' => LocationType::factory(),
            'level' => 1,
            'flag' => 'za',
            'location_country_id' => null,
            'parent_id' => null,
        ];
    }
}
