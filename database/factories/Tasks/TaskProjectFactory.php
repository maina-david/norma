<?php

namespace Database\Factories\Tasks;

use App\Models\Customer\Organisation;
use App\Models\Tasks\TaskProject;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TaskProject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->unique()->paragraphs(2, true),
            'organisation_id' => Organisation::factory(),
            'archived' => false,
        ];
    }
}
