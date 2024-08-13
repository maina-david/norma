<?php

namespace Database\Factories\Geonames;

use App\Models\Geonames\CountryInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryInfoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CountryInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'iso_alpha3' => $this->faker->countryISOAlpha3,
        ];
    }
}
