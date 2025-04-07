<?php

namespace Database\Factories\Customer;

use App\Models\Customer\NormaType;
use Illuminate\Database\Eloquent\Factories\Factory;

class NormaTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NormaType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(7, true),
        ];
    }
}
