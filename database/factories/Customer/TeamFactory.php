<?php

namespace Database\Factories\Customer;

use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'organisation_id' => Organisation::factory(),
        ];
    }
}
