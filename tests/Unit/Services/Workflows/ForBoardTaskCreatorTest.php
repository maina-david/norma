<?php

namespace Tests\Unit\Services\Workflows;

use App\Models\Auth\User;
use App\Models\Collaborators\Group;
use App\Models\Workflows\Board;
use App\Models\Workflows\Document;
use App\Models\Workflows\Project;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use App\Services\Workflows\ForBoardTaskCreator;
use Tests\TestCase;

class ForBoardTaskCreatorTest extends TestCase
{
    public function testCreateTasks(): void
    {
        /** @var Document */
        $document = Document::factory()->create();
        /** @var TaskType */
        $parentTaskType = TaskType::factory()->create();
        /** @var TaskType */
        $childTaskType = TaskType::factory()->create();
        /** @var Project */
        $project = Project::factory()->create();
        /** @var User */
        $manager = User::factory()->create();
        /** @var Board */
        $board = Board::factory()->create([
            'parent_task_type_id' => $parentTaskType->id,
            'task_type_order' => (string) $childTaskType->id,
            'task_type_defaults' => [
                $childTaskType->id => [
                    'manager_id' => $manager->id,
                ],
            ],
        ]);
        /** @var Group */
        $group = Group::factory()->create();

        app(ForBoardTaskCreator::class)->createTasks($board, $document, $group->id, $project->id);

        /** @var Task|null */
        $task = Task::where('group_id', $group->id)
            ->where('title', 'like', "%{$document->title}%")
            ->where('project_id', $project->id)
            ->with(['childTasks'])
            ->first();

        $this->assertNotNull($task);
        $firstChild = $task->childTasks->first();
        $this->assertNotNull($firstChild);
        $this->assertSame($manager->id, $firstChild->manager_id);
        $this->assertStringContainsString($document->title, $task->title ?? '');
        $this->assertStringContainsString($document->title, $firstChild->title ?? '');
    }
}
