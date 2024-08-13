<?php

namespace Database\Factories\Actions;

use App\Models\Ontology\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Actions\ActionArea>
 */
class ActionAreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'control_category_id' => Category::factory(),
            'subject_category_id' => Category::factory(),
        ];
    }
}
