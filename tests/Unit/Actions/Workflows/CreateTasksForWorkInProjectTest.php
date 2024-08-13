<?php

namespace Tests\Unit\Actions\Workflows;

use App\Actions\Workflows\Task\CreateTasksForWorkInProject;
use App\Models\Collaborators\Group;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Workflows\Board;
use App\Models\Workflows\Project;
use App\Services\Workflows\ForBoardTaskCreator;
use App\Services\Workflows\ForProjectDocumentCreator;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class CreateTasksForWorkInProjectTest extends TestCase
{
    public function testHandle(): void
    {
        // just need to test whether services are called, because we have other tests for the other things

        /** @var Work */
        $work = Work::factory()->create();
        /** @var Work */
        $workExpression = WorkExpression::factory()->for($work)->create();
        $work->update(['active_work_expression_id' => $workExpression->id]);
        /** @var Board */
        $board = Board::factory()->create();
        /** @var Group */
        $group = Group::factory()->create();
        /** @var Project */
        $project = Project::factory()->create(['location_id' => Location::factory()]);
        $this->partialMock(ForProjectDocumentCreator::class, function (MockInterface $mock) {
            $mock->shouldReceive('createCollaborateDocument');
        });
        $this->partialMock(ForBoardTaskCreator::class, function (MockInterface $mock) {
            $mock->shouldReceive('createTasks');
        });

        $this->assertThrows(
            fn () => app(CreateTasksForWorkInProject::class)->handle($work->id, $project->id, $project->location_id),
            RuntimeException::class
        );
        $project->update(['board_id' => $board->id]);
        $project->load('board');

        $project->update(['group_id' => null]);
        $this->assertThrows(
            fn () => app(CreateTasksForWorkInProject::class)->handle($work->id, $project->id, $project->location_id),
            RuntimeException::class,
            'Project does not have a group associated'
        );
        $project->update(['group_id' => $group->id]);

        app(CreateTasksForWorkInProject::class)->handle($work->id, $project->id, $project->location_id);
    }
}
