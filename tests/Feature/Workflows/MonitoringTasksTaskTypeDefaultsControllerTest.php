<?php

namespace Tests\Feature\Workflows;

use App\Models\Collaborators\Collaborator;
use App\Models\Workflows\Board;
use App\Models\Workflows\MonitoringTaskConfig;
use App\Models\Workflows\TaskType;
use Tests\TestCase;

class MonitoringTasksTaskTypeDefaultsControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItRedirectsToBoard(): void
    {
        $config = MonitoringTaskConfig::factory()->create();
        $taskType = TaskType::factory()->create();

        $this->signIn($this->collaborateSuperUser());

        $this->withExceptionHandling()
            ->get(route('collaborate.monitoring-task-configs.task-types.index', ['config' => $config->id, 'task_type' => $taskType->id]))
            ->assertRedirect(route('collaborate.monitoring-task-configs.show', ['monitoring_task_config' => $config->id]));
    }

    /**
     * @return void
     */
    public function testRendersTheDefaults(): void
    {
        $manager = Collaborator::factory()->create();
        $user = Collaborator::factory()->create();
        $taskType = TaskType::factory()->create();
        $board = Board::factory()->create([
            'task_type_order' => $taskType->id,
            'task_type_defaults' => [
                $taskType->id => [],
            ],
        ]);

        $config = MonitoringTaskConfig::factory()->create([
            'board_id' => $board->id,
            'tasks' => [
                $taskType->id => [
                    'user_id' => $user->id,
                    'manager_id' => $manager->id,
                ],
            ],
        ]);

        $this->signIn($this->collaborateSuperUser());

        $this->withExceptionHandling()
            ->get(route('collaborate.monitoring-task-configs.task-types.defaults', ['config' => $config->id, 'task_type' => $taskType->id]))
            ->assertSuccessful()
            ->assertSee($user->full_name)
            ->assertSee($manager->full_name);
    }

    /**
     * @return void
     */
    public function testUpdatesTheDefaults(): void
    {
        $taskType1 = TaskType::factory()->create();
        $taskType2 = TaskType::factory()->create();
        $board = Board::factory()->create(['task_type_order' => "{$taskType1->id},{$taskType2->id}"]);
        $board->taskTypes()->attach([$taskType1->id, $taskType2->id]);

        $config = MonitoringTaskConfig::factory()->create(['board_id' => $board->id, 'tasks' => []]);

        $this->signIn($this->collaborateSuperUser());

        $manager = Collaborator::factory()->create();
        $user = Collaborator::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'manager_id' => $manager->id,
        ];

        $this->withExceptionHandling()
            ->get(route('collaborate.monitoring-task-configs.task-types.defaults', ['config' => $config->id, 'task_type' => $taskType2->id]))
            ->assertSuccessful()
            ->assertDontSee($user->full_name)
            ->assertDontSee($manager->full_name);

        $this->withoutExceptionHandling()
            ->put(route('collaborate.monitoring-task-configs.task-types.update', ['config' => $config->id, 'task_type' => $taskType2->id]), $payload)
            ->assertRedirect(route('collaborate.monitoring-task-configs.show', ['monitoring_task_config' => $config->id]));

        $this->withExceptionHandling()
            ->get(route('collaborate.monitoring-task-configs.task-types.defaults', ['config' => $config->id, 'task_type' => $taskType2->id]))
            ->assertSuccessful()
            ->assertSee($user->full_name)
            ->assertSee($manager->full_name);
    }
}
