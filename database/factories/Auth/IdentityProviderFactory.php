<?php

namespace Database\Factories\Auth;

use App\Models\Auth\IdentityProvider;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdentityProviderFactory extends Factory
{
    protected $model = IdentityProvider::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_id' => $this->faker->word(),
            'sso_url' => $this->faker->url(),
            'slo_url' => $this->faker->url(),
            'certificate' => $this->faker->word(),
            'enabled' => true,
            'organisation_id' => Organisation::factory(),
            'team_id' => Team::factory(),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (IdentityProvider $provider) {
            Team::where('id', $provider->team_id)->update(['organisation_id' => $provider->organisation_id]);
        });
    }
}
