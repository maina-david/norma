<?php

namespace Database\Factories\Ontology;

use App\Models\Ontology\Category;
use App\Models\Ontology\CategoryType;
use App\Models\Ontology\LegalDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Category $category) {
            $category->display_label = $category->label;
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $label = $this->faker->unique()->words(3, true);

        return [
            'label' => $label,
            'display_label' => $label,
            'category_type_id' => CategoryType::factory(),
            'level' => 1,
            'legal_domain_id' => LegalDomain::factory(),
        ];
    }
}
