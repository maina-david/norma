<?php

namespace Database\Factories\Requirements;

use App\Enums\Requirements\ConsequenceAmountPrefix;
use App\Enums\Requirements\ConsequenceAmountType;
use App\Enums\Requirements\ConsequencePeriod;
use App\Enums\Requirements\ConsequenceType;
use App\Models\Requirements\Consequence;
use App\Support\Currencies;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsequenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Consequence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'consequence_type' => $this->faker->randomElement(ConsequenceType::options()),
            'consequence_other_detail' => $this->faker->sentence(),
            'amount_prefix' => $this->faker->randomElement(ConsequenceAmountPrefix::options()),
            'amount' => $this->faker->unique()->randomNumber(),
            'amount_type' => $this->faker->randomElement(ConsequenceAmountType::options()),
            'currency' => $this->faker->randomElement(Currencies::codes()),
            'sentence_period' => $this->faker->unique()->randomNumber(),
            'sentence_period_type' => $this->faker->randomElement(ConsequencePeriod::options()),
            'per_day' => $this->faker->boolean,
        ];
    }
}
