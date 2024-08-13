<?php

namespace Database\Factories\Ontology;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Ontology\UserTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserTagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserTag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(2, true),
            'author_id' => User::factory(),
            'organisation_id' => Organisation::factory(),
        ];
    }
}
