<?php

namespace Database\Factories\Ontology;

use App\Models\Ontology\Category;
use App\Models\Ontology\CategoryDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryDescription>
 */
class CategoryDescriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'category_id' => Category::factory(),
            'description' => $this->faker->paragraph(),
        ];
    }
}
