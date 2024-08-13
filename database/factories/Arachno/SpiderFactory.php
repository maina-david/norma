<?php

namespace Database\Factories\Arachno;

use App\Models\Arachno\Spider;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpiderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Spider::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'slug' => $this->faker->unique()->word(),
        ];
    }
}
