<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Contracts\Http\ResourceAction;
use App\Enums\Collaborators\CollaboratorStatus;
use App\Enums\Collaborators\ContractType;
use App\Enums\Collaborators\LanguageProficiency;
use App\Enums\Collaborators\YearOfStudy;
use App\Enums\Workflows\TaskStatus;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Controllers\Traits\HasNationalityAndResidence;
use App\Http\Requests\Collaborators\CollaboratorRequest;
use App\Http\ResourceActions\Collaborators\ActivateCollaborator;
use App\Http\ResourceActions\Collaborators\DeactivateCollaborator;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Group;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\Team;
use App\Models\Workflows\RatingType;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use App\Support\Languages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\ComponentAttributeBag;
use Throwable;

class CollaboratorController extends CollaborateController
{
    use HasNationalityAndResidence;

    /** @var string */
    protected string $sortBy = 'fname';

    /** @var bool */
    protected bool $withUpdate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Collaborator::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.collaborators';
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
        return CollaboratorRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table. Use the dot notation for relations.
     * e.g. ['profile.id', 'name'].
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'name' => fn ($row) => static::renderPartial($row, 'name-column'),
            'email' => 'email',
            'contract_type' => fn ($row) => static::renderPartial($row, 'contract-column'),
            'year_of_study' => fn ($row) => $row->profile->year_of_study ?? '-',
            'languages' => fn ($row) => static::renderPartial($row, 'languages-column', ['languages' => Languages::all()]),
        ];
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
        $query = parent::baseQuery($request)
            ->with(['profile.languages', 'profile.visa', 'profile.contract'])
            ->withWeekHours()
            ->withScore();

        if ($request->routeIs('collaborate.collaborators.show') || $request->routeIs('collaborate.collaborators.edit')) {
            $query->withCount(['completedTasks'])
                ->with([
                    'ratingAverage' => fn ($builder) => $builder->has('type'),
                    'ratingAverage.type',
                    'roles:id',
                    'profile.inactiveDocuments' => fn ($builder) => $builder->whereNotNull('attachment_id'),
                ]);
        }

        return $query;
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
            'fluid' => true,
            'baseQuery' => $this->baseQuery($request),
        ];
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
        /** @var int $collaborator */
        $collaborator = $request->route('collaborator');

        $tasks = Task::forActivity($collaborator)
            ->when(request('task_type'), fn ($query) => $query->where('task_type_id', request('task_type')))
            ->paginate(30);

        $types = Task::where('user_id', $collaborator)
            ->where('task_status', TaskStatus::done()->value)
            ->select(['task_type_id'])
            ->distinct();

        $types = TaskType::where('is_parent', false)->whereIn('id', $types)->pluck('name', 'id')->toArray();

        return [
            'formWidth' => 'max-w-7xl',
            'ratingTypes' => RatingType::all(),
            'tasks' => $tasks,
            'taskTypes' => $types,
            'canUpload' => $request->user()?->can('collaborate.collaborators.collaborator.update-documents'),
        ];
    }

    /**
     * Get the resource actions.
     *
     * @return array<int, ResourceAction>
     */
    protected static function resourceActions(): array
    {
        return [
            new ActivateCollaborator(),
            new DeactivateCollaborator(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function postStore(Model $model, Request $request): void
    {
        /** @var Collaborator $model */
        $this->updateAccess($request, $model);
    }

    /**
     * {@inheritDoc}
     */
    protected function redirectAfterStore(Model $model): string
    {
        /** @var User $user */
        $user = Auth::user();

        if (request('team_id') == -1 && $user->can('collaborate.collaborators.team.update')) {
            /** @var Collaborator $model */
            $model->load(['profile']);

            /** @var Profile $profile */
            $profile = $model->profile;

            return route('collaborate.teams.edit', $profile->team_id);
        }

        return parent::redirectAfterStore($model);
    }

    /**
     * Update the user roles.
     *
     * @param Request      $request
     * @param Collaborator $collaborator
     *
     * @throws Throwable
     *
     * @return RedirectResponse
     */
    public function updateAccess(Request $request, Collaborator $collaborator): RedirectResponse
    {
        return DB::transaction(function () use ($request, $collaborator) {
            $this->updateTeam($request, $collaborator);

            $this->updateGroups($request, $collaborator);

            $this->updateRoles($request, $collaborator);

            $this->notifySuccessfulUpdate();

            return redirect()->route('collaborate.collaborators.show', [
                'collaborator' => $collaborator->id,
                'tab' => 'access',
            ]);
        });
    }

    /**
     * Update the collaborator's team.
     *
     * @param Request      $request
     * @param Collaborator $collaborator
     *
     * @return void
     */
    protected function updateTeam(Request $request, Collaborator $collaborator): void
    {
        if (!$request->has('team_id')) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        /** @var Profile $profile */
        $profile = $collaborator->profile()->firstOrNew();

        $profile->team_id = $request->get('team_id');
        $profile->is_team_admin = (bool) $request->has('is_team_admin');

        if ($profile->team_id == -1) {
            /** @var Team $team */
            $team = Team::create(['title' => $collaborator->full_name]);
            $profile->team_id = $team->id;
            $profile->is_team_admin = true;
        }

        $profile->save();
    }

    /**
     * Update the collaborators groups.
     *
     * @param Request      $request
     * @param Collaborator $collaborator
     *
     * @return void
     */
    protected function updateGroups(Request $request, Collaborator $collaborator): void
    {
        if ($request->has('groups')) {
            /** @var Collection<Group> $groups */
            $groups = Group::whereKey($request->get('groups', []))->pluck('id');

            $collaborator->groups()->sync($groups->toArray());
        }
    }

    /**
     * Update the collaborator's role.
     *
     * @param Request      $request
     * @param Collaborator $collaborator
     *
     * @return void
     */
    protected function updateRoles(Request $request, Collaborator $collaborator): void
    {
        $roles = $request->collect('roles')->unique()->values();

        $collaborator->updateCollaborateRoles($roles->toArray());
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceFilters(): array
    {
        return [
            ...$this->nationalityAndResidenceFilter(),
            'language' => [
                'label' => __('collaborators.collaborator.languages'),
                'render' => fn () => view('components.ui.language-selector', [
                    'label' => '',
                    'value' => $this->getFilterValue('language'),
                    'name' => 'language',
                    'withDefault' => 'true',
                    'attributes' => new ComponentAttributeBag([]),
                ]),
                'value' => fn ($value) => Languages::forSelector()[$value] ?? '',
            ],
            'proficiency' => [
                'label' => __('collaborators.language_proficiency.proficiency'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('proficiency'),
                        'name' => 'proficiency',
                        'type' => 'select',
                        'options' => LanguageProficiency::forSelector(true),
                    ]),
                ]),
                'value' => fn ($value) => $this->getEnumLabel(LanguageProficiency::class, $value),
            ],
            'year' => [
                'label' => __('collaborators.collaborator.year_of_study'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('year'),
                        'name' => 'year',
                        'type' => 'select',
                        'options' => YearOfStudy::forSelector(true),
                    ]),
                ]),
                'value' => fn ($value) => YearOfStudy::tryFrom($value)?->label() ?? '',
            ],
            'contract' => [
                'label' => __('collaborators.collaborator.contract_type'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('contract'),
                        'name' => 'contract',
                        'type' => 'select',
                        'options' => ContractType::forSelector(true),
                    ]),
                ]),
                'value' => fn ($value) => $this->getEnumLabel(ContractType::class, $value),
            ],
            'status' => [
                'label' => __('auth.user.status'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('status'),
                        'name' => 'status',
                        'type' => 'select',
                        'options' => CollaboratorStatus::forSelector(true),
                    ]),
                ]),
                'value' => fn ($value) => CollaboratorStatus::tryFrom($value)?->label() ?? '',
            ],
            'restricted-visa' => [
                'label' => __('collaborators.restricted_visa'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('restricted-visa'),
                        'name' => 'restricted-visa',
                        'type' => 'select',
                        'options' => ['' => '', 'Yes' => __('interface.yes'), 'No' => __('interface.no')],
                    ]),
                ]),
                'value' => fn ($value) => in_array($value, ['Yes', 'No']) ? $value : '',
            ],
            'expired-visa' => [
                'label' => __('collaborators.expired_visa'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('expired-visa'),
                        'name' => 'expired-visa',
                        'type' => 'select',
                        'options' => ['' => '', 'Yes' => __('interface.yes'), 'No' => __('interface.no')],
                    ]),
                ]),
                'value' => fn ($value) => in_array($value, ['Yes', 'No']) ? $value : '',
            ],
            'role' => [
                'label' => __('nav.roles'),
                'render' => fn () => view('partials.ui.collaborate.role-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'name' => 'role',
                        'placeholder' => __('nav.roles'),
                    ]),
                ]),
                'value' => function ($value) {
                    if (empty($value)) {
                        // @codeCoverageIgnoreStart
                        return '';
                        // @codeCoverageIgnoreEnd
                    }

                    /** @var Role|null $role */
                    $role = Role::collaborate()->whereKey($value)->first();

                    return $role->title ?? '';
                },
            ],
        ];
    }

    /**
     * Import the given user.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        /** @var User|null $user */
        $user = User::where('email', $request->get('email'))->first();

        if (!$user || Profile::where('user_id', $user->id)->exists()) {
            /** @var string $message */
            $message = __('collaborators.user_not_found');
            $this->notifyErrorMessage($message);

            return redirect()->back();
        }

        $data = $user->toArray();
        $data['user_id'] = $user->id;
        unset($data['id']);

        Profile::create($data);

        $this->notifyGeneralSuccess();

        return redirect()->route('collaborate.collaborators.show', ['collaborator' => $user->id, 'tab' => 'access']);
    }
}
