<?php

namespace App\Http\Controllers\Collaborators\Collaborate\Applications;

use App\Enums\Collaborators\ApplicationStage;
use App\Enums\Collaborators\ApplicationStatus;
use App\Enums\Collaborators\LanguageProficiency;
use App\Enums\Collaborators\YearOfStudy;
use App\Events\Collaborators\CollaboratorApplicationApproved;
use App\Events\Collaborators\CollaboratorApplicationDeclined;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Controllers\Traits\HasNationalityAndResidence;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Profile;
use App\Support\Languages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class ApplicationController extends CollaborateController
{
    use HasNationalityAndResidence;

    protected bool $withCreate = false;

    protected bool $withUpdate = false;

    protected string $sortDirection = 'desc';

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Builder */
        return parent::baseQuery($request)
            ->whereHas('profile', fn ($query) => $query->whereNull('user_id'))
            ->when($request->routeIs('collaborate.collaborator-applications.show'), function ($query) {
                $query->with(['profile']);
            });
    }

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return CollaboratorApplication::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'id',
            'name' => fn ($row) => "{$row->fname} {$row->lname}",
            'email',
            'stage' => fn ($row) => ApplicationStage::fromValue($row->stage)->label(),
            'status' => fn ($row) => ApplicationStatus::fromValue($row->application_state)->label(),
            'created_at',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.collaborator-applications';
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
        return '';
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceFilters(): array
    {
        return [
            ...$this->nationalityAndResidenceFilter(),

            'stage' => [
                'label' => __('collaborators.collaborator_application.stage'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('status'),
                        'name' => 'status',
                        'type' => 'select',
                        'options' => ApplicationStage::forSelector(true),
                    ]),
                ]),
                'value' => fn ($value) => static::getEnumLabel(ApplicationStage::class, $value),
            ],
            'status' => [
                'label' => __('collaborators.collaborator_application.status'),
                'render' => fn () => view('partials.ui.collaborate.input-filter', [
                    'attributes' => new ComponentAttributeBag([
                        'label' => '',
                        'value' => $this->getFilterValue('status'),
                        'name' => 'status',
                        'type' => 'select',
                        'options' => ApplicationStatus::forSelector(true),
                    ]),
                ]),
                'value' => fn ($value) => static::getEnumLabel(ApplicationStatus::class, $value),
            ],
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
        ];
    }

    /**
     * Approve the application.
     *
     * @param CollaboratorApplication $application
     *
     * @return RedirectResponse
     */
    public function approve(CollaboratorApplication $application): RedirectResponse
    {
        $application->load(['profile']);

        /** @var Profile $profile */
        $profile = $application->profile;

        if (!$profile->user_id && ApplicationStage::two()->is($application->stage)) {
            $application->stage = ApplicationStage::provisional()->value;
        }

        if (ApplicationStatus::pending()->is($application->application_state)) {
            $application->application_state = ApplicationStatus::approved()->value;
            $application->save();
            CollaboratorApplicationApproved::dispatch($application);
        }

        $this->notifySuccessfulUpdate();

        $profile->refresh();

        if ($profile->user_id) {
            return redirect()->route('collaborate.collaborators.show', [
                'collaborator' => $profile->user_id, 'tab' => 'access',
            ]);
        }

        return redirect()->route('collaborate.collaborator-applications.show', ['collaborator_application' => $application->id]);
    }

    /**
     * Decline the application.
     *
     * @param CollaboratorApplication $application
     *
     * @return RedirectResponse
     */
    public function decline(CollaboratorApplication $application): RedirectResponse
    {
        if (ApplicationStatus::pending()->is($application->application_state)) {
            $application->application_state = ApplicationStatus::declined()->value;
            $application->save();
            CollaboratorApplicationDeclined::dispatch($application);
        }

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.collaborator-applications.show', ['collaborator_application' => $application->id]);
    }

    /**
     * Revert the application status to pending.
     *
     * @param CollaboratorApplication $application
     *
     * @return RedirectResponse
     */
    public function revert(CollaboratorApplication $application): RedirectResponse
    {
        if (!ApplicationStatus::pending()->is($application->application_state)) {
            $application->application_state = ApplicationStatus::pending()->value;
            $application->save();
        }

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.collaborator-applications.show', ['collaborator_application' => $application->id]);
    }
}
