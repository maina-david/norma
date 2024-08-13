<?php

namespace Database\Factories\Corpus;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Corpus\DocMeta>
 */
class DocMetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'source_url' => $this->faker->url(),
        ];
    }
}
