<?php

namespace Database\Factories\Auth;

use App\Enums\Application\ApplicationType;
use App\Models\Auth\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->unique()->words(2, true),
            'permissions' => [],
        ];
    }

    /**
     * The collaborate app state.
     *
     * @return RoleFactory
     */
    public function collaborate(): RoleFactory
    {
        return $this->state(function () {
            return [
                'app' => ApplicationType::collaborate()->value,
                'permissions' => ['workflows.task.view'],
            ];
        });
    }

    /**
     * The my app state.
     *
     * @return RoleFactory
     */
    public function my(): RoleFactory
    {
        return $this->state(function () {
            return [
                'app' => ApplicationType::my()->value,
                'permissions' => ['access'],
            ];
        });
    }

    /**
     * The admin app state.
     *
     * @return RoleFactory
     */
    public function admin(): RoleFactory
    {
        return $this->state(function () {
            return [
                'app' => ApplicationType::admin()->value,
            ];
        });
    }
}
