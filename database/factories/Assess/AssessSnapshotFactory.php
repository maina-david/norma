<?php

namespace Database\Factories\Assess;

use App\Models\Assess\AssessSnapshot;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessSnapshotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssessSnapshot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'place_id' => Norma::factory(),
            'month_date' => now()->endOfMonth(),
        ];
    }
}
