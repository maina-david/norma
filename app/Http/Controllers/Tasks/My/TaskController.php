<?php

namespace App\Http\Controllers\Tasks\My;

use App\Actions\Tasks\Task\AfterTaskSaved;
use App\Actions\Tasks\Task\CreateRequirementTaskCopies;
use App\Enums\Auth\UserActivityType;
use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskType;
use App\Events\Auth\UserActivity\FilteredResource;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Http\Controllers\Assess\My\Traits\RedirectsTasksNotAvailable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Http\Controllers\Traits\GetsModelForMorph;
use App\Http\Requests\Tasks\TaskRequest;
use App\Jobs\Exports\GenerateTasksExportExcel;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveLibryosManager;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    use DecodesHashids;
    use GetsModelForMorph;
    use RedirectsTasksNotAvailable;

    protected function renderIndexView(Request $request, bool $calendar = false): View|RedirectResponse
    {
        $organisation = null;
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoTasks($libryo);
        /** @var User $user */
        $user = Auth::user();
        // if we've landed here from another page, fetch filter settings from user
        // if the request came from the page itself, then the last filter might have been disabled, in which
        // case we don't want to fetch filter settings from the user
        /** @var UrlGenerator */
        $url = url();

        if (!$calendar) {
            if (
                count((array) $request->query()) === 0
                && Str::before($url->previous(), '?') !== Str::before($url->current(), '?')
            ) {
                $filters = $user->getTaskAppFilters();
                if (!empty($filters)) {
                    return redirect()->route('my.tasks.tasks.index', $filters);
                }
            } else {
                // persist the query params for filters to the user's settings
                $user->updateTaskFilters(Arr::except((array) $request->query(), ['page', 'perPage']));
            }
        }

        if ($libryo) {
            $query = Task::forLibryo($libryo);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $query = Task::forOrganisationUserAccess($organisation, $user);
        }

        if (!$calendar && is_null($request->input('archived'))) {
            $query->active();
        }

        if ($calendar) {
            $type = $request->hasAny(['show-month', 'show-year']) ? UserActivityType::changedCalendarDates() : UserActivityType::viewedCalendar();
            event(new GenericActivity($user, $type, null, $libryo, $organisation));
        }

        if ($request->get('search')) {
            event(new GenericActivity($user, UserActivityType::searchedTasks(), null, $libryo, $organisation));
        }

        $filtered = $request->only(['statuses', 'type', 'priority', 'assignee', 'archived', 'overdue', 'project']);

        if (!empty($filtered)) {
            event(new FilteredResource($user, UserActivityType::filteredTasks(), $filtered, $libryo, $organisation));
        }

        $month = $request->get('show-month', now()->month);
        $month = (int) $month;
        $month = $month > 12 || $month < 1 ? now()->month : $month;

        $year = $request->get('show-year', now()->year);
        $year = (int) $year;
        $year = $year > 3000 || $year < 2000 ? now()->year : $year;

        /** @var View */
        return view('pages.tasks.my.task.index', [
            'query' => $query->with(['assignee', 'project']),
            'canCreate' => $manager->isSingleMode(),
            'taskableType' => TaskType::generic()->value,
            'taskableId' => $libryo?->id,
            'filters' => $request->all(),
            'subTitle' => is_null($libryo) ? ($organisation->title ?? null) : $libryo->title,
            'calendarView' => $calendar,
            'showMonth' => $month,
            'showYear' => $year,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        return $this->renderIndexView($request);
    }

    /**
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function indexForCalendar(Request $request): View|RedirectResponse
    {
        return $this->renderIndexView($request, true);
    }

    /**
     * @param Request $request
     * @param Task    $task
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoTasks($libryo);

        $task->delete();

        if ($request->has('referer')) {
            /** @var RedirectResponse */
            return redirect($request->get('referer'));
        }

        /** @var User $user */
        $user = Auth::user();
        $filters = $user->getTaskAppFilters();
        if (!empty($filters)) {
            return redirect()->route('my.tasks.tasks.index', $filters);
        }

        return redirect()->route('my.tasks.tasks.index');
    }

    /**
     * @param string $task
     *
     * @return View
     */
    public function show(string $task): View
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoTasks($libryo);

        /** @var Task $task */
        $task = $this->decodeHash($task, Task::class);
        $this->authorize('view', $task);

        /** @var User $user */
        $user = Auth::user();

        $task->load(['assignee', 'author', 'project', 'watchers', 'previous', 'next', 'libryo:id,title']);

        // @codeCoverageIgnoreStart
        if ($task->isCloned()) {
            $task->load(['taskable.refTitleText']);
        }

        // @codeCoverageIgnoreEnd
        /** @var View */
        return view('pages.tasks.my.task.show', [
            'task' => $task,
            'user' => $user,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoTasks($libryo);

        if (!$libryo) {
            return redirect()->route('my.tasks.tasks.index');
        }

        /** @var User $user */
        $user = Auth::user();

        /** @var View */
        return view('pages.tasks.my.task.create', [
            'user' => $user,
            'taskableType' => TaskType::generic()->value,
            'taskableId' => $libryo->id,
            'remindWhomValue' => 1,
        ]);
    }

    public function edit(string $task): View|RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoTasks($libryo);

        $task = $this->decodeHash($task, Task::class);
        $this->authorize('update', $task);

        /** @var User $user */
        $user = Auth::user();

        /** @var View */
        return view('pages.tasks.my.task.edit', [
            'user' => $user,
            'task' => $task->load(['assignee', 'author', 'project', 'watchers']),
        ]);
    }

    /**
     * Process the copying of the tasks across.
     *
     * @param \Illuminate\Http\Request          $request
     * @param \App\Models\Tasks\Task            $task
     * @param \App\Models\Customer\Organisation $organisation
     * @param \App\Models\Auth\User             $user
     *
     * @return void
     */
    protected function processTaskCopying(Request $request, Task $task, Organisation $organisation, User $user): void
    {
        if ($request->has('copy') && $task->taskable_type === (new Reference())->getMorphClass()) {
            CreateRequirementTaskCopies::run($request, $task, $organisation, $user);
            $task->update(['source_task_id' => $task->id]);
        }
    }

    public function store(TaskRequest $request): RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();

        $data = $request->validated();
        if (!$libryo && !isset($data['libryo_id'])) {
            return redirect()->route('my.tasks.tasks.index');
        }
        /** @var User $user */
        $user = Auth::user();

        $libryo = null;

        if ($request->get('target_libryo_id')) {
            /** @var Libryo $libryo */
            $libryo = Libryo::whereKey($request->get('target_libryo_id'))->userHasAccess($user)->firstOrFail();
        }

        /** @var Libryo|null */
        $libryo = $libryo ?? $manager->getActive($user);

        // task was created from a taskable... then it can be done from all streams mode e.g. for AssessmentItemResponse
        if (is_null($libryo)) {
            /** @var Libryo */
            $libryo = Libryo::whereKey((int) ($data['libryo_id'] ?? 0))
                ->userHasAccess($user)
                ->firstOrFail();
        }

        $data['author_id'] = $user->id;
        $data['place_id'] = $libryo->id;
        $data['priority'] = TaskPriority::medium()->value;
        unset($data['reminder'], $data['followers']);

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

        $this->processTaskCopying($request, $task, $organisation, $user);

        if ($request->has('save_and_back')) {
            /** @var RedirectResponse */
            return redirect($request->get('save_and_back'));
        }

        // task was created from within a related modal/frame
        //        if ($request->isForTurboFrame()) {
        //            return redirect()->route('my.tasks.tasks.for.related.index', [
        //                'relation' => $this->getSlugFromLegacyName($data['taskable_type'], 'taskable'),
        //                'id' => $data['taskable_id'],
        //            ]);
        //        }

        return redirect()->route('my.tasks.tasks.show', ['task' => $task->hash_id]);
    }

    /**
     * @param TaskRequest $request
     * @param Task        $task
     *
     * @return RedirectResponse
     */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoTasks($libryo);

        $data = $request->validated();

        if (isset($data['reminder'])) {
            // @codeCoverageIgnoreStart
            unset($data['reminder']);
            // @codeCoverageIgnoreEnd
        }

        $task->update($data);

        /** @var User $user */
        $user = Auth::user();
        AfterTaskSaved::run($request, $task, $user);

        return redirect()->route('my.tasks.tasks.show', ['task' => $task->hash_id]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportAsExcel(Request $request): Response
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoTasks($libryo);

        $filename = Str::random(15) . '.xlsx';
        $filters = $request->all();
        $route = route('my.dashboard');

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();

        if ($manager->isSingleMode()) {
            /** @var Libryo $libryo */
            $libryo = $manager->getActive();
            $filename = "{$libryo->title}{$filename}";
            $job = new GenerateTasksExportExcel($filename, $user, $libryo, $organisation, $filters, $route);
        } else {
            $job = new GenerateTasksExportExcel($filename, $user, null, $organisation, $filters, $route);
        }

        $this->dispatch($job);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => 'download-progress',
            'jobId' => $job->getJobStatusId(),
            'percentage' => 0,
            'redirect' => route('my.downloads.download.tasks.excel', ['filename' => $filename], false),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Store the generated tasks.
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function storeForBulk(Request $request): RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();

        if (!$libryo && !$request->has('libryo_id')) {
            return redirect()->route('my.tasks.tasks.index');
        }

        $data = $request->validate([
            'tasks' => ['required', 'array'],
            'taskable_type' => ['string', 'required', 'max:255'],
            'taskable_id' => ['numeric', 'required'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $libryo = $libryo ?? Libryo::whereKey($request->get('libryo_id', 0))->userHasAccess($user)->firstOrFail();

        unset($data['tasks']);

        $data = array_merge($data, [
            'author_id' => $user->id,
            'place_id' => $libryo->id,
        ]);

        /** @var Organisation $organisation */
        $organisation = Organisation::whereKey($libryo->organisation_id)->first();

        $request->collect('tasks')
            ->each(function ($title) use ($organisation, $user, $request, $data) {
                $task = Task::create([...$data, 'title' => html_entity_decode($title)]);

                AfterTaskSaved::run($request, $task, $user);

                $this->processTaskCopying($request, $task, $organisation, $user);
            });

        return redirect()->route('my.tasks.tasks.for.related.index', [
            'relation' => $this->getSlugFromLegacyName($data['taskable_type'], 'taskable'),
            'id' => $data['taskable_id'],
        ]);
    }
}
