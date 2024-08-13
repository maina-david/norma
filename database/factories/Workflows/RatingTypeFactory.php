<?php

namespace Database\Factories\Workflows;

use App\Models\Workflows\RatingType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RatingType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->words(3, true),
            'course_link' => $this->faker->url(),
            'icon' => $this->faker->randomElement(['align-center', 'times', 'cog']),
        ];
    }
}
