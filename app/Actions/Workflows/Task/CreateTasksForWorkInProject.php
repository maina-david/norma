<?php

namespace App\Actions\Workflows\Task;

use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Project;
use App\Services\Workflows\ForBoardTaskCreator;
use App\Services\Workflows\ForProjectDocumentCreator;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

class CreateTasksForWorkInProject
{
    use AsAction;

    public string $jobQueue = 'default';
    public int $jobTries = 2;

    public function __construct(
        protected ForProjectDocumentCreator $forProjectDocumentCreator,
        protected ForBoardTaskCreator $forBoardTaskCreator,
    ) {
    }

    /**
     * Job to create a document for the given work and then parent and child tasks associated with that document,
     * using the default project workflow to determine which task types need to be created.
     *
     * @param int $workId
     * @param int $projectId
     * @param int $locationId
     *
     * @return void
     */
    public function handle(int $workId, int $projectId, int $locationId): void
    {
        $project = Project::findOrFail($projectId);
        if (!$project->board) {
            throw new RuntimeException('Project does not have a board associated');
        }
        if (!$project->group_id) {
            throw new RuntimeException('Project does not have a group associated');
        }
        $work = Work::has('activeExpression')->with('activeExpression')->findOrFail($workId);
        /** @var WorkExpression $expression */
        $expression = $work->activeExpression;
        $document = $this->forProjectDocumentCreator->createCollaborateDocument($expression, $project, $locationId);
        $this->forBoardTaskCreator->createTasks($project->board, $document, $project->group_id, $projectId);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param int $workId
     * @param int $projectId
     * @param int $locationId
     *
     * @return void
     */
    public function asJob(int $workId, int $projectId, int $locationId): void
    {
        $this->handle(
            $workId,
            $projectId,
            $locationId
        );
    }
}
