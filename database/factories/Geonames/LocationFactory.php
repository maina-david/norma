<?php

namespace Database\Factories\Geonames;

use App\Models\Geonames\Location;
use App\Models\Geonames\LocationType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Location $location) {
            $location->slug = Str::slug($location->title);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array
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
