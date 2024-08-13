<?php

namespace Database\Factories\Geonames;

use App\Models\Geonames\LocationType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LocationTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LocationType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'adjective_key' => $this->faker->randomElement(['national', 'provincial', 'municipal', 'state']),
        ];
    }
}
