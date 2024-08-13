<?php

namespace Database\Factories\Lookups;

use App\Enums\Lookups\CannedResponseField;
use App\Models\Lookups\CannedResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

class CannedResponseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CannedResponse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'response' => $this->faker->paragraph(),
            'for_field' => $this->faker->randomElement(array_values(CannedResponseField::options())),
        ];
    }
}
