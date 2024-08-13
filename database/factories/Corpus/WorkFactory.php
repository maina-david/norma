<?php

namespace Database\Factories\Corpus;

use App\Enums\Corpus\WorkStatus;
use App\Enums\Corpus\WorkType;
use App\Models\Arachno\Source;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Work::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'status' => WorkStatus::active()->value,
            'language_code' => 'eng',
            'work_date' => Carbon::now()->toDateString(),
            'work_number' => $this->faker->randomNumber(),
            'work_type' => WorkType::act()->value,
            'primary_location_id' => Location::factory(),
            'source_id' => Source::factory(),
        ];
    }
}
