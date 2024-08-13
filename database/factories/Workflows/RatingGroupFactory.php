<?php

namespace Database\Factories\Workflows;

use App\Models\Workflows\RatingGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RatingGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->words(3, true),
        ];
    }
}
