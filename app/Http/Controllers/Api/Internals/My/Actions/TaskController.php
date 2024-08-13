<?php

namespace App\Http\Controllers\Api\Internals\My\Actions;

use App\Actions\Tasks\Task\AfterTaskSaved;
use App\Actions\Tasks\Task\CreateRequirementTaskCopies;
use App\Enums\Auth\UserActivityType;
use App\Enums\System\LibryoModule;
use App\Enums\Tasks\Frequency;
use App\Enums\Tasks\TaskPriority;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Http\Controllers\Api\Internals\My\MyApiController;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Http\Requests\Actions\RecurringTaskRequest;
use App\Http\Requests\Actions\TaskRequest;
use App\Http\ResourceActions\Tasks\ChangeAssignee;
use App\Http\ResourceActions\Tasks\ChangeImpact;
use App\Http\ResourceActions\Tasks\ChangePriority;
use App\Http\ResourceActions\Tasks\ChangeStatus;
use App\Http\Resources\Internals\My\Actions\TaskResource;
use App\Jobs\Exports\GenerateTasksExportExcel;
use App\Models\Actions\ActionArea;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveLibryosManager;
use DateTime;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RRule\RRule;

class TaskController extends MyApiController
{
    use DecodesHashids;

    /**
     * Get the allowed sorts.
     *
     * @var array<int, string>
     */
    protected array $allowedSorts = [
        'id', 'title', 'impact', 'priority', 'task_status', 'created_at', 'taskable_type', 'due_on',
    ];

    /**
     * {@inheritDoc}
     */
    protected function actionsDefinitions(): array
    {
        return [
            new ChangeAssignee(),
            new ChangeImpact(),
            new ChangePriority(),
            new ChangeStatus(),
        ];
    }

    /**
     * Get the base query.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        $this->parseSort($request);
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        return Task::forLibryoOrOrganisation($libryo, $organisation);
    }

    /**
     * List the available tasks.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();

        $items = $this->baseQuery($request)
            ->with([
                'taskable:id',
                'assignee:id,fname,sname,avatar_attachment_id',
                'watchers:id,fname,sname,avatar_attachment_id',
                'taskable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Reference::class => ['refPlainText'],
                ]),
            ])
            ->when(!$libryo, fn ($query) => $query->with(['libryo:id,title']))
            ->filter($request->all())
            ->apiQueryFilter($request)
            ->reorder($this->sortBy, $this->sortDirection)
            ->paginate(min($request->get('perPage', 50), 50));

        return TaskResource::collection($items);
    }

    /**
     * Get the resource.
     *
     * @param string $task
     *
     * @return \App\Http\Resources\Internals\My\Actions\TaskResource
     */
    public function show(string $task): TaskResource
    {
        $task = $this->decodeHash($task, Task::class);
        $this->authorize('view', $task);

        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();

        $task->loadMissing([
            'project:id,title',
            'taskable:id',
            'watchers:id,fname,sname,avatar_attachment_id',
            'assignee:id,fname,sname,avatar_attachment_id',
            'taskable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                Reference::class => ['refPlainText'],
            ]),
            ...($libryo ? [] : ['libryo:id,title']),
        ]);

        return new TaskResource($task);
    }

    /**
     * Create a new resource.
     *
     * @param \App\Http\Requests\Actions\TaskRequest $request
     *
     * @return \App\Http\Resources\Internals\My\Actions\TaskResource
     */
    public function store(TaskRequest $request): TaskResource
    {
        $data = $request->validated();
        $manager = app(ActiveLibryosManager::class);

        /** @var User $user */
        $user = Auth::user();

        // task was created from a taskable... then it can be done from all streams mode e.g. for AssessmentItemResponse
        if (is_null($libryo = $manager->getActive($user))) {
            /** @var Libryo $libryo */
            $libryo = Libryo::whereKey((int) ($data['libryo_id'] ?? 0))
                ->userHasAccess($user)
                ->firstOrFail();
        }

        $data['author_id'] = $user->id;
        $data['place_id'] = $libryo->id;
        $data['priority'] = $data['priority'] ?? TaskPriority::medium()->value;

        /** @var Task $task */
        $task = Task::create($data);
        // @codeCoverageIgnoreStart
        if ($task->taskable_type === (new ContextQuestion())->getMorphClass()) {
            event(new GenericActivity($user, UserActivityType::createdApplicabilityTask(), null, $libryo));
        }
        // @codeCoverageIgnoreEnd
        AfterTaskSaved::run($request, $task, $user);

        /** @var Organisation $organisation */
        $organisation = Organisation::whereKey($libryo->organisation_id)->first();

        if ($request->get('copy') === true && $task->taskable_type === (new Reference())->getMorphClass()) {
            CreateRequirementTaskCopies::run($request, $task, $organisation, $user);
            $task->update(['source_task_id' => $task->id]);
        }

        return $this->show($task->hash_id);
    }

    /**
     * Create a copy of the task.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Tasks\Task   $task
     *
     * @return \App\Http\Resources\Internals\My\Actions\TaskResource
     */
    public function copy(Request $request, Task $task): TaskResource
    {
        $this->authorize('view', $task);

        /** @var User $user */
        $user = Auth::user();

        if (!$task->source_task_id) {
            $task->load([
                'libryo.organisation',
                'watchers:id',
            ]);

            $request->merge([
                'followers' => $task->watchers->pluck('id')->all(),
            ]);

            CreateRequirementTaskCopies::run($request, $task, $task->libryo?->organisation, $user);
            $task->update(['source_task_id' => $task->id]);
        }

        return $this->show($task->hash_id);
    }

    /**
     * Update the relevant task fields.
     *
     * @param \App\Http\Requests\Actions\TaskRequest $request
     * @param string                                 $task
     *
     * @return \App\Http\Resources\Internals\My\Actions\TaskResource
     */
    public function update(TaskRequest $request, string $task): TaskResource
    {
        /** @var Task $task */
        $task = $this->decodeHash($task, Task::class);
        $this->authorize('view', $task);

        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        unset($data['followers']);

        $task->update($data);

        AfterTaskSaved::run($request, $task, $user);

        return $this->show($task->hash_id);
    }

    /**
     * Get the available groupings.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $field
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroups(Request $request, string $field): JsonResponse
    {
        $selectMap = [
            'assignee' => sprintf('%s, concat(fname," ", sname) as assignee', qualify_column(User::class, 'id')),
            'control' => sprintf('%s, display_label as control', qualify_column(Category::class, 'id')),
            'topic' => sprintf('%s, display_label as topic', qualify_column(Category::class, 'id')),
        ];

        $excludeOther = ['type', 'status'];

        abort_unless(isset($selectMap[$field]), 404);

        $items = $this->baseQuery($request)
            ->when(
                $field === 'assignee',
                fn ($query) => $query->whereNotNull('assigned_to_id')
                    ->join(get_table(User::class), qualify_column(User::class, 'id'), qualify_column(Task::class, 'assigned_to_id'))
            )
            ->when(
                $field === 'control',
                fn ($query) => $query->whereNotNull('action_area_id')
                    ->join(get_table(ActionArea::class), qualify_column(ActionArea::class, 'id'), qualify_column(Task::class, 'action_area_id'))
                    ->join(get_table(Category::class), qualify_column(ActionArea::class, 'control_category_id'), qualify_column(Category::class, 'id'))
            )
            ->when(
                $field === 'topic',
                fn ($query) => $query->whereNotNull('action_area_id')
                    ->join(get_table(ActionArea::class), qualify_column(ActionArea::class, 'id'), qualify_column(Task::class, 'action_area_id'))
                    ->join(get_table(Category::class), qualify_column(ActionArea::class, 'subject_category_id'), qualify_column(Category::class, 'id'))
            )
            ->selectRaw("distinct {$selectMap[$field]}")
            ->orderBy($field)
            ->get()
            ->map(function ($item) use ($field) {
                /** @var Task $item */
                return [
                    'label' => $item->{$field},
                    'value' => $item->id,
                ];
            });

        if (!in_array($field, $excludeOther)) {
            // @phpstan-ignore-next-line
            $items->push([
                'label' => __('interface.other'),
                'value' => null,
            ]);
        }

        return response()->json(['data' => $items]);
    }

    /**
     * {@inheritDoc}
     */
    protected function generateExcel(Request $request): array
    {
        $filename = Str::random(15) . '.xlsx';
        $filters = $request->all();
        $route = route('my.dashboard');

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation $organisation */
        $organisation = $manager->getActiveOrganisation();

        if ($manager->isSingleMode()) {
            /** @var Libryo $libryo */
            $libryo = $manager->getActive();
            $filename = "{$libryo->title}{$filename}";
            $job = new GenerateTasksExportExcel($filename, $user, $libryo, $organisation, $filters, $route, LibryoModule::actions()->value);
        } else {
            $job = new GenerateTasksExportExcel($filename, $user, null, $organisation, $filters, $route, LibryoModule::actions()->value);
        }
        dispatch($job);

        return [$job->getJobStatusId(), route('my.downloads.download.tasks.excel', ['filename' => $filename], false)];
    }

    /**
     * @param RecurringTaskRequest $request
     * @param string               $task
     *
     * @throws Exception
     *
     * @return TaskResource
     */
    public function updateRecurrence(RecurringTaskRequest $request, string $task): TaskResource
    {
        /** @var Task $task */
        $task = $this->decodeHash($task, Task::class);
        $this->authorize('view', $task);

        $data = $request->validated();

        $startDate = new DateTime($data['startDate']);
        $frequency = Frequency::tryFrom($data['frequencyUnit'])?->name ?? Frequency::DAILY->name;

        $rules = [
            'FREQ' => strtoupper($frequency),
            'INTERVAL' => $data['frequencyNumber'],
            'DTSTART' => $startDate->format('Ymd\THis\Z'),
        ];

        if ($data['endCondition'] === 'onDate') {
            $endDate = new DateTime($data['endDate']);
            $rules['UNTIL'] = $endDate->format('Ymd\THis\Z');
        } elseif ($data['endCondition'] === 'afterOccurrences' && isset($data['occurrences'])) {
            $rules['COUNT'] = $data['occurrences'];
        }

        if (!empty($data['selectedDays'])) {
            $rules['BYDAY'] = implode(',', $data['selectedDays']);
        }

        if (isset($data['monthSelection'])) {
            if ($data['monthSelection'] === 'dayOfMonth' && isset($data['monthDay'])) {
                $rules['BYMONTHDAY'] = $data['monthDay'];
            } elseif ($data['monthSelection'] === 'weekOfMonth' && isset($data['weekDayName'])) {
                $rules['BYDAY'] = $data['weekDayName'];
            }
        }

        $rule = new RRule($rules);
        $task->update([
            'recurrence_start_date' => $startDate->format('Y-m-d'),
            'rrule' => $rule->rfcString(),
        ]);

        return $this->show($task->hash_id);
    }

    /**
     * @param string $task
     *
     * @throws AuthorizationException
     *
     * @return TaskResource
     */
    public function clearRecurrence(string $task): TaskResource
    {
        /** @var Task $task */
        $task = $this->decodeHash($task, Task::class);
        $this->authorize('view', $task);

        $task->update([
            'rrule' => null,
        ]);

        return $this->show($task->hash_id);
    }
}
