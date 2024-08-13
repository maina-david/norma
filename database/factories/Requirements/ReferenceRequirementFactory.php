<?php

namespace Database\Factories\Requirements;

use App\Models\Corpus\Reference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Requirements\ReferenceRequirement>
 */
class ReferenceRequirementFactory extends Factory
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
        ];
    }
}
