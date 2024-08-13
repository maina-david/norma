<?php

namespace Database\Factories\Collaborators;

use App\Enums\Collaborators\ApplicationStage;
use App\Enums\Collaborators\ApplicationStatus;
use App\Models\Collaborators\CollaboratorApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollaboratorApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CollaboratorApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'fname' => $this->faker->unique()->firstName(),
            'lname' => $this->faker->unique()->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'text_quiz' => $this->faker->lastName(),
            'application_state' => ApplicationStatus::pending(),
            'stage' => ApplicationStage::one(),
        ];
    }
}
