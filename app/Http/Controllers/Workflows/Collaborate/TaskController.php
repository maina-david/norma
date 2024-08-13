<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Contracts\Http\ResourceAction;
use App\Enums\Workflows\TaskStatus;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Workflows\TaskRequest;
use App\Http\Requests\Workflows\TaskStatusRequest;
use App\Http\ResourceActions\Workflows\ArchiveTasks;
use App\Http\ResourceActions\Workflows\ChangeAssignee;
use App\Http\ResourceActions\Workflows\ChangeGroup;
use App\Http\ResourceActions\Workflows\ChangeManager;
use App\Http\ResourceActions\Workflows\ChangePriority;
use App\Http\ResourceActions\Workflows\ChangeStatus;
use App\Http\ResourceActions\Workflows\RequestPayment;
use App\Http\Services\Workflows\Tasks\UserTaskFilters;
use App\Models\Auth\User;
use App\Models\Workflows\Board;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskCheck;
use App\Models\Workflows\TaskType;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends CollaborateController
{
    /** @var bool */
    protected bool $withCreate = false;

    /** @var string */
    protected string $sortDirection = 'desc';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Task::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.tasks';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return TaskRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * Use the dot notation for relations and for custom results use functions that
     * will receive the current row as an arg. The field name will be used as the
     * translation key but when you use functions, make sure the array is keyed with the
     * translation name.
     *
     * e.g. ['profile' => 'profile.id', 'name'].
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function authoriseAction(string $action, mixed $arguments = []): void
    {
        if ($action === 'show') {
            $this->authorize('view', $arguments);

            return;
        }

        parent::authoriseAction($action, $arguments);
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var User $user */
        $user = $request->user();

        return parent::baseQuery($request)
            ->when(
                !$user->canAny(['collaborate.workflows.task.set-pending', 'collaborate.workflows.task.view-pending']),
                fn ($builder) => $builder->where('task_status', '!=', TaskStatus::pending()->value)
            )
            ->when(
                $request->routeIs('collaborate.task-type.tasks.index'),
                fn ($builder) => $builder->withCount(['outstandingPaymentRequests', 'paidPaymentRequests'])
            )
            ->when(
                $request->routeIs('collaborate.tasks.show'),
                fn ($builder) => $builder->with([
                    'document', 'assignee', 'manager', 'group', 'ratings.type', 'transitions.user',
                    'paymentRequests.team', 'taskType.taskRoute', 'taskType.ratingGroup.ratingTypes',
                    'checks.collaborator',
                ])
            )
            ->when(
                $request->routeIs('collaborate.tasks.edit'),
                fn ($builder) => $builder->with([
                    'document', 'assignee', 'manager', 'group', 'ratings.type', 'transitions.user',
                    'paymentRequests.team',
                ])
            );
    }

    /**
     * Get the view to be used for the given action.
     *
     * @param string $action
     *
     * @return View
     */
    protected function getView(string $action): View
    {
        if ($action === 'index') {
            /** @var View */
            return view('pages.workflows.collaborate.task.index');
        }

        return parent::getView($action);
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        return app(UserTaskFilters::class)->resourceFilters();
    }

    /**
     * Get the resource actions.
     *
     * @return array<int, ResourceAction>
     */
    protected static function resourceActions(): array
    {
        return [
            new ArchiveTasks(),
            new ChangeAssignee(),
            new ChangeGroup(),
            new ChangeManager(),
            new ChangePriority(),
            new ChangeStatus(),
            new RequestPayment(),
        ];
    }

    /**
     * Get the task types in the order they are set up in the board.
     *
     * @param Request $request
     *
     * @return array<array-key, array<string, string|int>>
     */
    protected function getTaskTypes(Request $request): array
    {
        $types = $request->get('types', []);

        $board = Board::cached($request->get('workflow'));

        if (!$board && empty($types)) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $tasksTypes = TaskType::when(!empty($types), fn ($builder) => $builder->whereKey($types))
            ->when($board, function ($builder) use ($board) {
                /** @var Board $board */
                $builder->whereHas('boards', fn ($builder) => $builder->where('id', $board->id));
            })
            ->get(['id', 'name', 'colour'])
            ->keyBy('id');

        return collect(isset($board->task_type_order) ? explode(',', $board->task_type_order) : $types)
            ->map(fn ($type) => $tasksTypes->get($type))
            ->filter(fn ($type) => (bool) $type)
            ->toArray();
    }

    /**
     * Get extra data to be sent back to the index view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'taskFilters' => $request->query(),
            'taskTypes' => $this->getTaskTypes($request),
            'boards' => Board::orderBy('title')->whereNull('archived_at')->get(['id', 'title']),
            'noData' => true,
            'fluid' => true,
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @throws AuthorizationException
     *
     * @return View|Response
     */
    public function index(Request $request): View|Response
    {
        $filter = app(UserTaskFilters::class);

        $payload = $filter->redirectPayload($request);

        if (!empty($payload)) {
            return redirect()->route('collaborate.tasks.index', $payload);
        }

        $filter->updateFromRequest($request);

        return parent::index($request);
    }

    /**
     * Get the tasks for the given task type.
     *
     * @param Request $request
     * @param int     $type
     *
     * @return MultiplePendingTurboStreamResponse
     */
    public function forTaskType(Request $request, int $type): MultiplePendingTurboStreamResponse
    {
        abort_unless($request->isForTurboFrame(), 404);

        $this->updateSorts($request);

        $tasks = $this->baseQuery($request)
            ->with(['assignee'])
            ->where('task_type_id', $type)
            ->filter($request->all())
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(5);

        return multipleTurboStreamResponse([
            singleTurboStreamResponse("task-type-tasks-{$type}", 'append')
                ->view('partials.workflows.collaborate.task.task-per-type-collection', [
                    'tasks' => $tasks,
                    'type' => $type,
                ]),
            singleTurboStreamResponse("task-type-tasks-{$type}-pagination")
                ->view('partials.workflows.collaborate.task.task-per-type-pagination', [
                    'tasks' => $tasks,
                    'type' => $type,
                ]),
        ]);
    }

    /**
     * Get extra data to be sent back to the show view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
        ];
    }

    /**
     * Get extra data to be sent back to the edit view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function editViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-2xl',
        ];
    }

    /**
     * Change the status of the task.
     *
     * @param TaskStatusRequest $request
     * @param int               $resourceId
     * @param int               $status
     *
     * @return RedirectResponse
     */
    public function setStatus(TaskStatusRequest $request, int $resourceId, int $status): RedirectResponse
    {
        try {
            /** @var TaskStatus $status */
            $status = TaskStatus::fromValue($status);
        } catch (InvalidArgumentException $e) {
            abort(404);
        }

        /** @var Task $resource */
        $resource = $this->baseQuery($request)->findOrFail($resourceId);

        abort_unless($resource->canChangeStatusTo($status), 403);

        // @codeCoverageIgnoreStart
        if (TaskStatus::done()->is($status) && !$resource->isRated()) {
            Session::flash('flash.type', 'error');
            Session::flash('flash.message', __('workflows.task.rating_required'));

            return redirect()->route('collaborate.tasks.show', $resourceId);
        }
        // @codeCoverageIgnoreEnd

        $resource->task_status = $status->value;
        $resource->save();

        return redirect()->route('collaborate.tasks.show', $resourceId);
    }

    /**
     * Mark the task as checked for the day.
     *
     * @param Request $request
     * @param Task    $task
     *
     * @return RedirectResponse
     */
    public function markAsChecked(Request $request, Task $task): RedirectResponse
    {
        $checked = TaskCheck::where('task_id', $task->id)->whereDate('created_at', now())->exists();

        if (!$checked) {
            $task->checks()->create([
                'user_id' => Auth::id(),
                'no_entries' => $request->has('no_entries'),
            ]);
        }

        $this->notifySuccessfulUpdate();

        return redirect()->back();
    }

    /**
     * {@inheritDoc}
     */
    protected static function availableSorts(): array
    {
        return [
            '-id' => __('interface.newest_first'),
            'id' => __('interface.oldest_first'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, int $resourceId): View|Response
    {
        if (!$request->isForTurboFrame()) {
            return parent::show($request, $resourceId);
        }

        /** @var User $user */
        $user = $request->user();

        $task = parent::baseQuery($request)
            ->with([
                'document', 'assignee', 'manager', 'group', 'ratings.type', 'transitions.user',
                'paymentRequests.team', 'taskType.taskRoute',
            ])
            ->when(
                !$user->canAny(['collaborate.workflows.task.set-pending', 'collaborate.workflows.task.view-pending']),
                fn ($builder) => $builder->where('task_status', '!=', TaskStatus::pending()->value)
            )
            ->findOrFail($resourceId);

        return singleTurboStreamResponse($request->header('turbo-frame') ?? '')
            ->view('pages.workflows.collaborate.task.show', ['resource' => $task, 'withEdit' => true])
            ->toResponse($request);
    }
}
