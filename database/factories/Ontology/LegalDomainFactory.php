<?php

namespace Database\Factories\Ontology;

use App\Models\Ontology\LegalDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

class LegalDomainFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LegalDomain::class;

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
