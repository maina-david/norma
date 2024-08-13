<?php

namespace Database\Factories\Requirements;

use App\Models\Requirements\Summary;
use Illuminate\Database\Eloquent\Factories\Factory;

class SummaryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model|TModel>
     */
    protected $model = Summary::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'summary_body' => $this->faker->randomHtml(1, 2),
        ];
    }
}
