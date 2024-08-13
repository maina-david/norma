<?php

namespace Database\Factories\Corpus;

use App\Models\Corpus\Citation;
use App\Models\Corpus\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

class CitationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Citation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'number' => 'Section ' . $this->faker->unique()->numberBetween(1, 100),
            'heading' => $this->faker->unique()->words(3, true),
            'work_id' => Work::factory(),
            'position' => 1,
        ];
    }
}
