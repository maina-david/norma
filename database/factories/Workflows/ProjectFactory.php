<?php

namespace Database\Factories\Workflows;

use App\Enums\Workflows\ProjectRagStatus;
use App\Models\Collaborators\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workflows\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'group_id' => Group::factory(),
            'rag_status' => ProjectRagStatus::GREEN->value,
        ];
    }
}
