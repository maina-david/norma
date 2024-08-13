<?php

namespace App\Http\Controllers\Tasks\My;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetsModelForMorph;
use App\Http\Requests\Tasks\TaskRequest;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentExtract;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Tasks\AITaskGenerator;
use App\Stores\Tasks\TaskWatcherStore;
use App\Traits\UpdatesReferenceContentExtract;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\ComponentAttributeBag;

class TaskStreamController extends Controller
{
    use GetsModelForMorph;
    use UpdatesReferenceContentExtract;

    public function __construct(protected TaskWatcherStore $taskWatcherStore)
    {
    }

    public function show(Task $task): Response
    {
        /** @var User */
        $user = Auth::user();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.tasks.task.show-details',
            'target' => 'tasks-details-' . $task->id,
            'task' => $task->load(['assignee', 'author', 'project', 'watchers', 'libryo:id,title']),
            'user' => $user,
            'isActionsModule' => false,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param string $relation
     * @param string $id
     *
     * @return Response
     */
    public function indexForRelated(string $relation, string $id): Response
    {
        $model = $this->getModel($relation, (int) $id, 'taskable');
        [$libryo, $organisation, $query] = $this->getLibryoOrgQuery($model);

        $query = $query->with(['author', 'assignee']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.tasks.task.tasks-for',
            'target' => 'tasks-for-' . $relation . '-' . $id,
            'query' => $query,
            'relation' => $relation,
            'related' => $model,
            'taskableType' => $this->getLegacyName($relation, 'taskable'),
            'taskableId' => $model->getAttribute('id'),
            'libryo' => $model instanceof AssessmentItemResponse ? $model->libryo : $libryo,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param string $relation
     * @param string $id
     * @param string $libryo
     *
     * @return Response
     */
    public function createForRelated(string $relation, string $id, string $libryo): Response
    {
        /** @var User */
        $user = Auth::user();

        $libryo = Libryo::whereKey((int) $libryo)
            ->userHasAccess($user)
            ->firstOrFail();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.tasks.task.create',
            'target' => 'create-task-for-related-' . $relation . '-' . $id,
            'taskableType' => $this->getLegacyName($relation, 'taskable'),
            'taskableId' => $id,
            'libryo' => $libryo,
            'user' => $user,
            'remindWhomValue' => 1,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Task $task
     *
     * @return Response
     */
    public function follow(Task $task): Response
    {
        /** @var User */
        $user = Auth::user();

        $this->taskWatcherStore->watch($task, $user);

        return $this->show($task);
    }

    /**
     * @param Task $task
     *
     * @return Response
     */
    public function unfollow(Task $task): Response
    {
        /** @var User */
        $user = Auth::user();

        $this->taskWatcherStore->unwatch($task, $user);

        return $this->show($task);
    }

    /**
     * @param TaskRequest $request
     * @param Task        $task
     *
     * @return Response
     */
    public function updateStatus(TaskRequest $request, Task $task): Response
    {
        $task->update(['task_status' => $request->validated()['task_status']]);
        /** @var View $view */
        $view = view('streams.single-component-partial', [
            'component' => 'tasks.task-status',
            'target' => 'tasks-task-status-' . $task->id,
            'attributes' => new ComponentAttributeBag([
                'task' => $task,
                'status' => $task->task_status,
                'changeable' => true,
            ]),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @return array<mixed>
     */
    private function getLibryoOrgQuery(Model $model): array
    {
        $taskRelation = $model instanceof File ? 'taskableTasks' : 'tasks';
        $organisation = $libryo = null;
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $query = $model->{$taskRelation}()->forLibryo($libryo);
        } else {
            /** @var User */
            $user = Auth::user();
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $query = $model->{$taskRelation}()->forOrganisationUserAccess($organisation, $user);
        }

        return [$libryo, $organisation, $query];
    }

    /**
     * Query the AI Generator and suggest tasks that can be used.
     *
     * @param string $relation
     * @param int    $id
     * @param string $libryo
     *
     * @return Response
     */
    public function suggestForRelated(string $relation, int $id, string $libryo): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $libryo = Libryo::whereKey((int) $libryo)->userHasAccess($user)->firstOrFail();

        $response = match ($relation) {
            'reference' => $this->getReferenceExtracts($id),
            default => [],
        };

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.tasks.task.suggested',
            'target' => 'suggested-task-for-related-' . $relation . '-' . $id,
            'taskableType' => $this->getLegacyName($relation, 'taskable'),
            'taskableId' => $id,
            'libryo' => $libryo,
            'user' => $user,
            'suggestions' => $response,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param int $referenceId
     *
     * @return array<int, string>
     */
    protected function getReferenceExtracts(int $referenceId): array
    {
        if (!ReferenceContentExtract::where('reference_id', $referenceId)->exists()) {
            $reference = new Reference();
            $reference->id = $referenceId;
            $this->updateFromGPT($reference, app(AITaskGenerator::class));
        }

        return ReferenceContentExtract::where('reference_id', $referenceId)->pluck('content')->all();
    }
}
