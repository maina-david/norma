<?php

namespace Database\Factories\System;

use App\Models\System\JobStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JobStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'job_type' => $this->faker->word(),
        ];
    }
}
