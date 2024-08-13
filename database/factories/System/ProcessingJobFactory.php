<?php

namespace Database\Factories\System;

use App\Enums\System\ProcessingJobStatus;
use App\Enums\System\ProcessingJobType;
use App\Models\Arachno\Spider;
use App\Models\Corpus\Work;
use App\Models\System\ProcessingJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcessingJobFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProcessingJob::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'job_type' => $this->faker->randomElement(ProcessingJobType::options()),
            'status' => $this->faker->randomElement(ProcessingJobStatus::options()),
            'payload' => [],
            'available_at' => $this->faker->dateTime('now', 'UTC'),
            'spider_slug' => Spider::factory(),
            'work_id' => Work::factory(),
            'created_at' => $this->faker->dateTime('now', 'UTC'),
        ];
    }
}
