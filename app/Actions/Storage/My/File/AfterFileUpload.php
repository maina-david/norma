<?php

namespace App\Actions\Storage\My\File;

use App\Enums\Tasks\TaskActivityType;
use App\Events\Assess\AssessmentActivity\UploadedDocument;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use App\Stores\Assess\AssessmentItemResponseFileStore;
use App\Stores\Ontology\UserTagStore;
use App\Stores\Tasks\FileTaskStore;
use App\Stores\Tasks\TaskActivityStore;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class AfterFileUpload
{
    use AsAction;

    public function __construct(
        protected UserTagStore $userTagStore,
        protected FileTaskStore $fileTaskStore,
        protected TaskActivityStore $taskActivityStore,
        protected AssessmentItemResponseFileStore $assessmentItemResponseFileStore,
    ) {
    }

    public function handle(Request $request, File $file, User $user): void
    {
        $this->handleTags($request, $file, $user);
        $this->handleTask($request, $file, $user);
        $this->handleAssessmentItemResponses($request, $file, $user);
        $this->handleComment($request, $file, $user);
    }

    /**
     * @param Request $request
     * @param File    $file
     * @param User    $user
     *
     * @return void
     */
    protected function handleTags(Request $request, File $file, User $user): void
    {
        if (empty($request->input('user_tags'))) {
            return;
        }
        /** @var array<mixed> */
        $tagsFromInput = $request->input('user_tags');
        /** @var Organisation */
        $organisation = $file->organisation ?? $file->norma?->organisation;
        $tags = $this->userTagStore->createFromForm($tagsFromInput, $organisation, $user);

        $this->userTagStore->attachRelations($file, 'userTags', $tags);
    }

    /**
     * @param Request $request
     * @param File    $file
     * @param User    $user
     *
     * @return void
     */
    protected function handleTask(Request $request, File $file, User $user): void
    {
        if ($request->input('relation') !== 'task') {
            return;
        }

        $taskId = (int) $request->input('related_id');

        /** @var Collection<Task> */
        $tasks = Task::whereKey($taskId)->get();

        $this->fileTaskStore->attachRelations($file, 'tasks', $tasks);

        foreach ($tasks as $task) {
            $details['file_id'] = $file->id;
            $this->taskActivityStore->create(
                TaskActivityType::documentUpload(),
                $task->id,
                $user->id,
                $task->place_id,
                $details
            );
        }
    }

    protected function handleAssessmentItemResponses(Request $request, File $file, User $user): void
    {
        if ($request->input('relation') !== 'assessment-item-response') {
            return;
        }

        $itemResponseId = $request->input('related_id');

        /** @var Collection<AssessmentItemResponse> */
        $responses = AssessmentItemResponse::whereKey($itemResponseId)->get();

        $this->assessmentItemResponseFileStore->attachRelations($file, 'assessmentItemResponses', $responses);

        foreach ($responses as $response) {
            UploadedDocument::dispatch($file, $user, $response, $file->norma, $file->organisation);
        }
    }

    /**
     * @param Request $request
     * @param File    $file
     * @param User    $user
     *
     * @return void
     */
    protected function handleComment(Request $request, File $file, User $user): void
    {
        if ($request->input('relation') !== 'comment') {
            return;
        }

        $commentId = (int) $request->input('related_id');

        /** @var Comment */
        $comment = Comment::find($commentId);
        $comment->files()->attach($file->id);
    }
}
