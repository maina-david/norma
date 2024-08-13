<?php

namespace Database\Factories\Workflows;

use App\Models\Workflows\TaskRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskRouteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TaskRoute::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'route_name' => 'collaborate.annotations.index',
            'parameters' => [
                'task' => 'id', 'expression' => 'document.work_expression_id',
            ],
        ];
    }
}
