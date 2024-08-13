<?php

namespace App\Http\Services\Workflows\Tasks;

use App\Enums\Workflows\PaymentStatus;
use App\Enums\Workflows\TaskPriority;
use App\Enums\Workflows\TaskStatus;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Board;
use App\Models\Workflows\Project;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\Traits\InteractsWithFilters;
use App\Traits\Workflows\UsesTaskTypeFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\ComponentAttributeBag;

class UserTaskFilters
{
    use InteractsWithFilters;
    use UsesJurisdictionFilter;
    use UsesTaskTypeFilter;

    public const USER_SETTING_KEY = 'manage_tasks';

    /**
     * Get the task filters for data table.
     *
     * @return array<string, array<string, mixed>>
     */
    public function resourceFilters(): array
    {
        return [
            'workflow' => [
                'label' => __('workflows.task.workflow'),
                'render' => fn () => view('components.workflows.board.board-selector', [
                    'label' => '',
                    'value' => $this->getFilterValue('workflow'),
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => Board::cached($value)?->title ?? '',
            ],
            'types' => $this->getTaskTypeFilter(),
            'status' => [
                'label' => __('workflows.task.status'),
                'render' => fn () => view('components.workflows.tasks.task-status-selector', [
                    'label' => '',
                    'multiple' => true,
                    'value' => $this->getFilterValue('status'),
                    'attributes' => new ComponentAttributeBag(),
                ]),
                'multiple' => true,
                'value' => fn ($value) => self::getEnumLabel(TaskStatus::class, $value),
            ],
            'jurisdiction' => $this->getJurisdictionFilter(),
            'domains' => [
                'label' => __('ontology.legal_domain.index_title'),
                'render' => fn () => view('components.ontology.legal-domain.selector', [
                    'value' => '',
                    'name' => 'domain',
                    'multiple' => false,
                    'label' => '',
                    'attributes' => new ComponentAttributeBag([
                        'annotations' => true,
                    ]),
                ]),
                'value' => fn ($value) => $value === 'all'
                    // @codeCoverageIgnoreStart
                    ? __('ontology.legal_domain.all')
                    // @codeCoverageIgnoreEnd
                    : (LegalDomain::cached($value)?->title ?? ''),
            ],
            'priority' => [
                'label' => __('workflows.task.priority'),
                'render' => fn () => view('components.workflows.tasks.task-priority-selector', [
                    'multiple' => true,
                    'label' => '',
                    'value' => $this->getFilterValue('priority'),
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'multiple' => true,
                'value' => fn ($value) => self::getEnumLabel(TaskPriority::class, $value),
            ],
            'assignee' => [
                'label' => __('workflows.task.assigned_to'),
                'render' => fn () => view('components.workflows.tasks.task-assignment-selector', [
                    'label' => '',
                    'name' => 'assignee',
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => $value === 'none' ? __('tasks.unassigned') : (Collaborator::active()->find($value)?->full_name ?? ''),
            ],
            'payment_status' => [
                'label' => __('workflows.task.payment_status.label'),
                'render' => fn () => view('partials.workflows.collaborate.task.payment-status-selector'),
                'value' => fn ($value) => PaymentStatus::tryFrom($value)?->label() ?? '',
            ],
            'manager' => [
                'label' => __('workflows.task.manager'),
                'render' => fn () => view('components.workflows.tasks.task-assignment-selector', [
                    'label' => '',
                    'name' => 'manager',
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => Collaborator::active()->find($value)?->full_name ?? '',
            ],
            'project' => [
                'label' => __('workflows.project.index_title'),
                'render' => fn () => view('components.workflows.project.project-selector', [
                    'label' => '',
                    'name' => 'project',
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => Project::find($value)?->title ?? '',
            ],
        ];
    }

    /**
     * Extract the values of the filters from the request.
     *
     * @return array<string, mixed>
     */
    public function requestValues(): array
    {
        return collect($this->resourceFilters())
            ->map(fn ($value, $key) => $this->getFilterValue($key))
            ->toArray();
    }

    /**
     * Get the default  database values for the task settings.
     *
     * @return array<string, mixed>
     */
    protected function defaultDatabaseValues(): array
    {
        /** @var Board $board */
        $board = Board::first();

        return [
            'status' => [],
            'workflow' => $board->id,
            'jurisdiction' => null,
            'priority' => [],
            'types' => [],
            'domains' => null,
            'work_types' => null,
        ];
    }

    /**
     * Migrate the task filters to the new mapping.
     *
     * @param User                 $user
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>
     */
    protected function migrateSettings(User $user, array $settings = []): array
    {
        /** @var Board $board */
        $board = Board::first();

        $settings = [
            'status' => $settings['status'] ?? [],
            'workflow' => $settings['boardFilter'] ?? $board->id,
            'jurisdiction' => null,
            'priority' => $settings['priorityFilter'] ?? [],
            'types' => $settings['taskTypeFilter'] ?? [],
            'domains' => $settings['legalDomainFilter'] ?? null,
            'work_types' => $settings['legislationTypeFilter'] ?? null,
        ];

        $user->updateSetting(self::USER_SETTING_KEY, $settings);

        return $settings;
    }

    /**
     * Get the settings from the database.
     *
     * @return array<string, mixed>
     */
    protected function fromDatabase(): array
    {
        $settings = $this->defaultDatabaseValues();
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            // @codeCoverageIgnoreStart
            return $settings;
            // @codeCoverageIgnoreEnd
        }

        if (!Arr::get($user->settings, self::USER_SETTING_KEY)) {
            $user->updateSetting(self::USER_SETTING_KEY, $settings);

            return $settings;
        }

        $settings = Arr::get($user->settings, self::USER_SETTING_KEY);

        // old settings thus, migrate to new
        if (!($settings['workflow'] ?? null)) {
            $settings = $this->migrateSettings($user, $settings);
        }

        return array_merge($this->defaultDatabaseValues(), $settings);
    }

    /**
     * Get the payload to be used if a redirect is required.
     *
     * @return array<string, mixed>
     */
    public function redirectPayload(Request $request): array
    {
        $fromRequest = $this->requestValues();

        // we require a workflow or task types to be selected
        if (!empty($fromRequest['workflow']) || !empty($fromRequest['types'])) {
            return [];
        }

        $fromDatabase = collect($this->fromDatabase())->filter()->toArray();

        if (!empty($fromDatabase['types'])) {
            unset($fromDatabase['workflow']);
        }

        return collect(array_merge($fromRequest, $fromDatabase))->filter()->toArray();
    }

    public function updateFromRequest(Request $request): void
    {
        $current = $this->requestValues();

        if (!$previous = $request->session()->pull('workflow_filters')) {
            $request->session()->put('workflow_filters', $current);

            return;
        }

        $toUpdate = [];

        // check if the previous filter in the session matches with the current value. If not, update that filter.
        foreach ($current as $key => $value) {
            if (($previous[$key] ?? false) !== $value) {
                $toUpdate[$key] = $value;
            }
        }

        if (!empty($toUpdate)) {
            /** @var User $user */
            $user = $request->user();
            $user->updateSetting(self::USER_SETTING_KEY, array_merge($this->fromDatabase(), $toUpdate));
        }

        $request->session()->put('workflow_filters', $current);
    }
}
