<?php

namespace Database\Factories\Corpus;

use App\Models\Corpus\Doc;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Doc::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
        ];
    }
}
