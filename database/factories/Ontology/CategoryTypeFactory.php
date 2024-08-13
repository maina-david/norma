<?php

namespace Database\Factories\Ontology;

use App\Models\Ontology\CategoryType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CategoryType::class;

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (CategoryType $type) {
            $taxonomy = explode(' ', $type->title);
            array_unshift($taxonomy);
            $type->taxonomy_type = implode('_', $taxonomy);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
        ];
    }
}
