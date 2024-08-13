<?php

namespace Database\Factories\Collaborators;

use App\Models\Collaborators\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $countryCode = 'UK';
        $currency = $this->faker->currencyCode();

        return [
            'title' => $this->faker->unique()->words(2, true),
            'currency' => $currency,
            'auto_pay' => $this->faker->numberBetween(0, 1),
            'bank_name' => $this->faker->words(2, true),
            'bank_country' => $this->faker->country(),
            'branch_code' => $this->faker->swiftBicNumber(),
            'account_name' => $this->faker->words(2, true),
            'account_number' => $this->faker->bankAccountNumber(),
            'account_currency' => $currency,
            'iban' => $this->faker->iban($countryCode),
            'swift_code' => $this->faker->swiftBicNumber(),
            'other_billing_instructions' => $this->faker->words(3, true),
            'billing_address' => $this->faker->address(),
            'bank_details_plain' => json_encode(['details' => $this->faker->words(3, true)]),
            'transferwise_id' => $this->faker->numberBetween(1000, 9000),
        ];
    }
}
