<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Enums\Workflows\TaskStatus;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskApplication;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\Traits\Workflows\UsesTaskTypeFilter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use Throwable;

class TaskApplicationController extends CollaborateController
{
    use UsesJurisdictionFilter;
    use UsesTaskTypeFilter;

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withShow = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /** @var string */
    protected string $searchResultKey = 'task_id';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return TaskApplication::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.task-applications';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        // @codeCoverageIgnoreStart
        return '';
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the base search query builder instance.
     *
     * @param Request $request
     *
     * @return \Laravel\Scout\Builder
     */
    protected static function baseSearchQuery(Request $request): \Laravel\Scout\Builder
    {
        return Task::search($request->get('search'))
            ->where('user_id', null)
            ->whereIn('task_status', [TaskStatus::todo(), TaskStatus::pending()])
            ->orderBy('id', 'desc');
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
        return parent::baseQuery($request)
            ->whereHas('applicant')
            ->with([
                'task.taskType',
                'applicant' => fn ($builder) => $builder->withScore()->withWeekHours(),
                'applicant.profile' => fn ($builder) => $builder->withPendingDocuments(),
                'applicant.profile.contract',
                'applicant.profile.identification',
                'applicant.profile.visa',
            ]);
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'applicant' => fn ($row) => static::renderPartial($row, 'applicant-column'),
            'task' => fn ($row) => static::renderPartial($row, 'task-column'),
            'actions' => fn ($row) => static::renderPartial($row, 'action-column'),
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $resourceId
     *
     * @throws ReflectionException
     * @throws Throwable
     *
     * @return RedirectResponse
     */
    public function update(int $resourceId): RedirectResponse
    {
        /** @var TaskApplication $application */
        $application = TaskApplication::with('task')->findOrFail($resourceId);

        $this->authoriseAction('edit', $application);

        DB::transaction(function () use ($application) {
            $application->task->update(['user_id' => $application->user_id]);
            $application->task->save();
            TaskApplication::where('task_id', $application->task_id)->delete();
        });

        /** @var Request $request */
        $request = request();

        return redirect($request->header('referer', route('collaborate.task-applications.index')));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $resourceId
     *
     * @throws Throwable
     * @throws AuthorizationException
     * @throws ReflectionException
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request, int $resourceId): RedirectResponse
    {
        $application = TaskApplication::with('task')->findOrFail($resourceId);

        $this->authoriseAction('edit', $application);

        $application->delete();

        return redirect()->route('collaborate.task-applications.index');
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        return [
            'jurisdiction' => $this->getJurisdictionFilter(),
            'types' => $this->getTaskTypeFilter(),
        ];
    }
}
