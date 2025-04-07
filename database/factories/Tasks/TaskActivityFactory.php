<?php

namespace Database\Factories\Tasks;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TaskActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'place_id' => Norma::factory(),
        ];
    }
}
