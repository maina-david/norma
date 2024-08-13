<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Actions\Workflows\Task\Wizard\CreateParentTask;
use App\Actions\Workflows\Task\Wizard\CreateSubTask;
use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Corpus\UpdateCandidateActionStatus;
use App\Enums\Workflows\WizardStep;
use App\Http\Controllers\Abstracts\StepperController;
use App\Http\Requests\Workflows\Wizard\AttachGroupRequest;
use App\Http\Requests\Workflows\Wizard\TaskDetailsRequest;
use App\Models\Auth\User;
use App\Models\Corpus\WorkExpression;
use App\Models\Notify\Pivots\NotificationHandoverUpdateCandidateLegislation;
use App\Models\Workflows\Board;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Throwable;

class TaskCreationController extends StepperController
{
    protected bool $grouped = true;
    protected string $groupByField = 'key';
    protected Board $board;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        /** @var int $board */
        $board = $request->route('board');
        /** @var Board|null $board */
        $board = Board::cached($board);
        if ($board) {
            $this->board = $board;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getStages(): array
    {
        $steps = collect($this->board->wizard_steps ?? [])
            ->map(function ($step) {
                $step = WizardStep::tryFrom($step);

                return [
                    'key' => $step?->value,
                    'title' => $step?->label(),
                    'request' => $step?->formRequest(),
                    'partial' => $step?->view(),
                    'optional' => in_array($step->value ?? null, $this->board->optional_wizard_steps ?? []),
                ];
            })
            ->toArray();

        $steps[] = [
            'key' => 'group_assignment',
            'title' => __('workflows.task.wizard_steps.group_assignment'),
            'request' => AttachGroupRequest::class,
            'partial' => 'partials.workflows.collaborate.task.wizard.attach-group',
            'optional' => false,
        ];

        $steps[] = [
            'key' => 'task_details',
            'title' => __('workflows.task.wizard_steps.task_details'),
            'request' => TaskDetailsRequest::class,
            'partial' => 'partials.workflows.collaborate.task.wizard.task-details',
            'optional' => false,
        ];

        return $steps;
    }

    /**
     * {@inheritDoc}
     */
    protected function routeName(): string
    {
        return 'collaborate.tasks.wizard';
    }

    /**
     * {@inheritDoc}
     */
    protected function routeParams(Request $request): array
    {
        return [
            'board' => $request->route('board'),
            'project' => $request->query('project'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getView(): View
    {
        /** @var string $stage */
        $stage = app(Request::class)->route('stage', '1');
        $stage = (int) $stage;

        $stages = $this->getStages();

        if ($stages[$stage - 1]['key'] !== 'task_details') {
            /** @var View */
            return view('pages.workflows.collaborate.task.create', [
                'board' => $this->board,
                'forTurbo' => $this->fromTurbo(),
            ]);
        }

        $stages = collect($this->getStages());

        /** @var View */
        return view('pages.workflows.collaborate.task.create', [
            'board' => $this->board,
            'forTurbo' => $this->fromTurbo(),
            'users' => $this->getDefaultUsers(),
            'taskTitle' => $this->getCommonTaskTitle($stages),
        ]);
    }

    /**
     * Get the users that are defined int the task type defaults.
     *
     * @return Collection
     */
    protected function getDefaultUsers(): Collection
    {
        /** @var Collection $defaults */
        $defaults = collect($this->board->task_type_defaults)
            ->reduce(function ($pre, $cur) {
                $users = collect(['user_id', 'manager_id'])->map(fn ($field) => $cur[$field] ?? null)->filter();

                return $pre->merge($users);
            }, collect());

        $defaults = $defaults->unique();

        $users = collect();

        if ($defaults->isNotEmpty()) {
            /** @var Collection<User> $users */
            $users = User::find($defaults->values()->toArray(), ['id', 'fname', 'sname']);
            $users = $users->keyBy('id');
        }

        return $users;
    }

    /**
     * Get the task title to be used across all tasks.
     *
     * @param Collection $stages
     *
     * @return string
     */
    protected function getCommonTaskTitle(Collection $stages): string
    {
        /** @var int|false $index */
        $index = $stages->search(function ($stage) {
            return in_array($stage['key'], WizardStep::createsDocument());
        });

        if ($index === false) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        $stored = $this->getStoredStage($index + 1);

        $title = $stored['title'] ?? '';

        if ($stored['work_expression_id'] ?? false) {
            // @codeCoverageIgnoreStart
            $title = WorkExpression::whereKey($stored['work_expression_id'])->with(['work'])->first();
            $title = $title->work->title ?? '';
            // @codeCoverageIgnoreEnd
        }

        return $title;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Throwable
     */
    protected function onFinish(Collection $data, callable $flush): RedirectResponse
    {
        $parent = DB::transaction(function () use ($data) {
            $executed = [];
            $steps = $this->board->wizard_steps ?? [];

            foreach ($steps as $step) {
                /** @var string $step */
                $wizard = WizardStep::tryFrom($step);

                if (!$wizard) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }

                $action = $wizard->action();

                /** @var WizardAction $action */
                $action = new $action();

                $response = $action->handle(
                    $data->get($wizard->value)->toArray(),
                    $executed
                );

                $executed[$wizard->value] = $response;
            }

            return $this->createTasks($executed, $data);
        });

        $flush();

        $this->notifySuccessfulUpdate();

        $target = $this->redirectTo() ?? route('collaborate.tasks.index');

        $targetRequest = Request::create($target);
        $targetRoute = Route::getRoutes()->match($targetRequest);

        // @codeCoverageIgnoreStart
        if ($targetRoute->getName() === 'collaborate.docs-for-update.handover-updates.create' && $targetRequest->get('target')) {
            NotificationHandoverUpdateCandidateLegislation::where('id', $targetRequest->get('target'))
                ->whereRelation('legislation', 'doc_id', $targetRoute->parameter('doc'))
                ->whereNull('task_id')
                ->update(['task_id' => $parent->id ?? null, 'status' => UpdateCandidateActionStatus::DONE->value]);
        }

        // @codeCoverageIgnoreEnd
        /** @var RedirectResponse */
        return redirect($target);
    }

    /**
     * Handle task creation.
     *
     * @param array<string, mixed> $executed
     * @param Collection           $requestData
     *
     * @return \App\Models\Workflows\Task|null
     */
    protected function createTasks(array $executed, Collection $requestData): ?Task
    {
        $usable = WizardStep::createsDocument();

        /** @var Document|null $document */
        $document = collect($executed)->filter(fn ($val, $key) => in_array($key, $usable))->first();

        if (!$document) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $group = $requestData->get('group_assignment')['group_id'];
        $meta = [
            'board' => $this->board,
            'groupId' => $group,
            'document' => $document,
            'project_type' => $executed[WizardStep::SELECT_PROJECT_TYPE->value] ?? null,
        ];

        /** @var \App\Models\Workflows\Task $parent */
        $parent = (new CreateParentTask())->handle(['project' => request()->query('project')], $executed, $meta);

        /** @var Document|null $update */
        $update = collect($executed)
            ->filter(fn ($val, $key) => $key === WizardStep::SELECT_LEGAL_UPDATE->value)
            ->first();

        // @codeCoverageIgnoreStart
        if ($update) {
            $update->load(['legalUpdate.createdFromDoc.updateCandidate']);
            $update->legalUpdate?->createdFromDoc?->updateCandidate?->update([
                'task_id' => $parent->id,
            ]);
        }
        // @codeCoverageIgnoreEnd

        $meta['parent'] = $parent;

        (new CreateSubTask())->handle(
            $requestData->get('task_details')->toArray(),
            $executed,
            $meta
        );

        return $parent;
    }
}
