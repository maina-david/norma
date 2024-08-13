<?php

namespace Database\Factories\Collaborators;

use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Profile;
use Database\Factories\Auth\UserFactory;

class CollaboratorFactory extends UserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Collaborator::class;

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->has(Profile::factory()->state(function (array $attributes, $user) {
            return [
                'user_id' => $user->id,
                'timezone' => $user->timezone,
            ];
        }), 'profile');
    }
}
