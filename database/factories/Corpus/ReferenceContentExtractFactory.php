<?php

namespace Database\Factories\Corpus;

use App\Models\Corpus\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Corpus\ReferenceContentExtract>
 */
class ReferenceContentExtractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'reference_id' => Reference::factory(),
            'content' => $this->faker->text(),
        ];
    }
}
