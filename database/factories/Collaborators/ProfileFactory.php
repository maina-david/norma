<?php

namespace Database\Factories\Collaborators;

use App\Models\Auth\User;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'turk_application_id' => CollaboratorApplication::factory(),
            'timezone' => $this->faker->timezone(),
            'gender' => ['M', 'F'][$this->faker->numberBetween(0, 1)],
            'mobile_country_code' => $this->faker->countryCode(),
            'phone_mobile' => $this->faker->phoneNumber(),
            'contract_signed' => $this->faker->boolean(),
            'available' => $this->faker->boolean(),
            'nationality' => $this->faker->countryCode(),
            'current_residency' => $this->faker->countryCode(),
            'skills_develop' => ['Legal writing', 'Legal analysis', 'Legal research', 'Legal editing'],
            'university' => 'Other',
            'year_of_study' => '1st',
            'linkedin_url' => $this->faker->url(),
            'restricted_visa' => false,
            'visa_hours' => $this->faker->boolean(),
            'visa_available_hours' => $this->faker->boolean(),
            'training_complete' => $this->faker->boolean(),
            'start_date' => $this->faker->date(),
            'allows_communication' => true,
            'process_personal_data' => true,
            'team_id' => Team::factory()->create(),
        ];
    }
}
