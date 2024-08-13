<?php

namespace Tests\Feature\Workflows;

use App\Enums\Workflows\MonitoringTaskFrequency;
use App\Enums\Workflows\TaskPriority;
use App\Jobs\Workflows\GenerateMonitoringTasks;
use App\Models\Arachno\Source;
use App\Models\Collaborators\Group;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Board;
use App\Models\Workflows\MonitoringTaskConfig;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use Tests\TestCase;
use Throwable;

class GenerateMonitoringTasksTest extends TestCase
{
    public function testGenerationOfTasks(): void
    {
        Group::factory()->make()->forceFill(['id' => 7])->save();
        Source::factory()->make()->forceFill(['id' => 8])->save();
        Board::factory()->make()->forceFill([
            'id' => 9,
            'task_type_order' => '35,59,36,34',
        ])->save();

        $users = [26774, 26850, 27057];
        foreach ($users as $user) {
            Location::factory()->make()->forceFill(['id' => $user])->save();
        }

        $locations = [2338, 2337, 2383, 2374, 2358, 2334, 2351, 2367, 2344, 2591, 2603, 2605, 2611];
        foreach ($locations as $location) {
            Location::factory()->make()->forceFill(['id' => $location])->save();
        }

        $domains = [4402, 1, 2];
        foreach ($domains as $domain) {
            LegalDomain::factory()->make()->forceFill(['id' => $domain])->save();
        }

        $taskTypes = [34, 35, 36, 37, 59];
        foreach ($taskTypes as $types) {
            try {
                TaskType::factory()->make()->forceFill(['id' => $types])->save();
            } catch (Throwable) {
            }
        }
        TaskType::whereKey(37)->update(['is_parent' => true]);
        $board = Board::find(9);
        $board->update(['parent_task_type_id' => 37]);
        $board->taskTypes()->sync($taskTypes);

        $this->seedConfig();

        $task = MonitoringTaskConfig::first();
        $this->assertNull($task->last_run);
        $this->assertDatabaseCount(Task::class, 0);

        GenerateMonitoringTasks::dispatch();

        $this->assertDatabaseCount(Task::class, 15);

        $this->assertDatabaseHas(Task::class, ['task_type_id' => 34, 'user_id' => 26774, 'units' => 0.8]);
        $this->assertDatabaseHas(Task::class, ['task_type_id' => 35, 'units' => 2]);
        $this->assertDatabaseHas(Task::class, ['task_type_id' => 36, 'user_id' => 26850]);
        $this->assertDatabaseHas(Task::class, ['task_type_id' => 37, 'manager_id' => 27057]);

        GenerateMonitoringTasks::dispatch();

        $this->assertDatabaseCount(Task::class, 15);
    }

    private function seedConfig()
    {
        MonitoringTaskConfig::create([
            'title' => 'USA, California, Cities and Counties Daily Tracking Task (Waste) - Municode.',
            'board_id' => 9,
            'group_id' => 7,
            'enabled' => true,
            'source_id' => 8,
            'frequency' => MonitoringTaskFrequency::DAY->value,
            'frequency_quantity' => 1,
            'start_day' => now()->dayOfWeek,
            'priority' => TaskPriority::urgent()->value,
            'locations' => [2338],
            'legal_domains' => [4402],
            'tasks' => [
                // handover update
                34 => [
                    'units' => 0.8,
                    'user_id' => 26774,
                ],
                // monitor sources
                35 => [
                    'units' => 2,
                ],
                // approve notifications
                36 => [
                    'user_id' => 26850,
                ],
                // monitor sources - parent
                37 => [
                    'manager_id' => 27057,
                    'description' => 'Please carefully read the instructions before accepting this task [link to be inserted to task instructions that appear on learn.libryo]. Please check all updates uploaded on a daily basis for all the Californian Cities and Counties (waste).',
                ],
            ],
        ]);

        MonitoringTaskConfig::create([
            'title' => 'USA Cities and Counties Daily Tracking Task (EHS) - Municode',
            'board_id' => 9,
            'group_id' => 7,
            'enabled' => true,
            'source_id' => 8,
            'frequency' => MonitoringTaskFrequency::WEEK->value,
            'frequency_quantity' => 1,
            'start_day' => now()->dayOfWeek,
            'priority' => TaskPriority::urgent()->value,
            'locations' => [2337, 2383, 2374, 2358, 2334, 2351, 2367, 2344],
            'legal_domains' => [1, 2],
            'tasks' => [
                // handover update
                34 => [
                    'units' => 0.8,
                    'user_id' => 26774,
                ],
                // monitor sources
                35 => [
                    'units' => 2,
                ],
                // approve notifications
                36 => [
                    'user_id' => 26850,
                ],
                // monitor sources - parent
                37 => [
                    'manager_id' => 27057,
                    'description' => 'Please carefully read the instructions before accepting this task [link to be inserted to task instructions that appear on learn.libryo]. Please check all updates uploaded on a daily basis for all the other USA cities and Counties (EHS) - Municode.',
                ],
            ],
        ]);

        MonitoringTaskConfig::create([
            'title' => 'USA Cities and Counties Daily Tracking Task (EHS) - Municode',
            'board_id' => 9,
            'group_id' => 7,
            'enabled' => true,
            'source_id' => 8,
            'frequency' => MonitoringTaskFrequency::MONTH->value,
            'frequency_quantity' => 1,
            'start_day' => now()->dayOfWeek,
            'priority' => TaskPriority::urgent()->value,
            'locations' => [2591, 2603, 2605, 2611],
            'legal_domains' => [1, 2],
            'tasks' => [
                // handover update
                34 => [
                    'units' => 0.8,
                    'user_id' => 26774,
                ],
                // monitor sources
                35 => [
                    'units' => 2,
                ],
                // approve notifications
                36 => [
                    'user_id' => 26850,
                ],
                // monitor sources - parent
                37 => [
                    'manager_id' => 27057,
                    'description' => 'Please carefully read the instructions before accepting this task [link to be inserted to task instructions that appear on learn.libryo]. Please check all updates uploaded on a daily basis for all the other USA cities and Counties (EHS) - Municode.',
                ],
            ],
        ]);
    }
}
