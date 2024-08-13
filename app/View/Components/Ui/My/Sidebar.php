<?php

namespace App\View\Components\Ui\My;

use App\Models\Customer\Libryo;
use App\Services\Customer\ActiveLibryosManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Sidebar extends Component
{
    /** @var array<int, mixed> */
    public array $items = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(protected ActiveLibryosManager $activeLibryosManager)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        /** @var Request */
        $request = request();

        $this->items = [
            [
                'icon' => 'browser',
                'label' => __('my.nav.dashboard'),
                'route' => route('my.dashboard'),
                'current_module' => $request->routeIs('my.dashboard'),
            ],
        ];

        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();
        $libryo = $manager->getActive();

        if ($organisation->hasAssessModule()) {
            $assessItem = [
                'icon' => 'clipboard-list',
                'label' => __('my.nav.assess'),
                'route' => route('my.assess.assessment-item-responses.index'),
                'beta' => false,
                'current_module' => $request->routeIs('my.assess.*'),
                'children' => [
                    [
                        'label' => __('my.nav.assessment_items'),
                        'route' => route('my.assess.assessment-item-responses.index'),
                    ],
                    [
                        'label' => __('my.nav.performance'),
                        'route' => route('my.assess.dashboard'),
                    ],
                ],
            ];
            if ($manager->isSingleMode()) {
                /** @var Libryo $libryo */
                if (!$libryo->hasAssessModule()) {
                    $assessItem = null;
                }
            }
            if (!is_null($assessItem)) {
                $this->items[] = $assessItem;
            }
        }

        $this->items[] = [
            'icon' => 'bell',
            'label' => __('my.nav.updates'),
            'route' => route('my.notify.legal-updates.index'),
            'current_module' => $request->routeIs('my.notify.*'),
        ];

        $this->items[] = [
            'icon' => 'gavel',
            'label' => __('my.nav.requirements'),
            'route' => route('my.corpus.requirements.index'),
            'current_module' => $request->routeIs('my.requirements.*') || $request->routeIs('my.corpus.*'),
        ];

        if ($organisation->hasActionsModule() && $organisation->libryos()->where('settings->modules->actions', true)->exists()) {
            $actions = [
                'icon' => 'clipboard-list-check',
                'label' => __('settings.nav.actions'),
                'route' => '#',
                'current_module' => $request->routeIs('my.actions.*'),
                'children' => [
                    [
                        'label' => __('my.nav.actions_planner'),
                        'route' => route('my.actions.action-areas.subject.index'),
                    ],
                    [
                        'label' => __('my.nav.actions_manager'),
                        'route' => route('my.actions.tasks.index', ['view' => 'schedule']),
                    ],
                    [
                        'label' => __('my.nav.actions_checklist'),
                        'route' => route('my.actions.checklists.index'),
                    ],
                    [
                        'label' => __('my.nav.dashboard'),
                        'route' => route('my.actions.tasks.dashboard'),
                    ],
                ],
            ];

            // @codeCoverageIgnoreStart
            if ($manager->isSingleMode() && $libryo) {
                if (!$libryo->hasActionsModule()) {
                    $actions = null;
                }
            }
            // @codeCoverageIgnoreEnd

            if (!is_null($actions)) {
                $this->items[] = $actions;
            }
        }

        if ($organisation->hasTaskModule()) {
            $tasks = [
                'icon' => 'tasks',
                'label' => __('my.nav.tasks'),
                'route' => '#',
                'beta' => false,
                'children' => [
                    [
                        'label' => __('my.nav.tasks'),
                        'route' => route('my.tasks.tasks.index'),
                    ],
                    [
                        'label' => __('my.nav.projects'),
                        'route' => route('my.tasks.task-projects.index'),
                    ],
                ],
                'current_module' => $request->routeIs('my.tasks.*'),
            ];

            // @codeCoverageIgnoreStart
            if ($manager->isSingleMode()) {
                /** @var Libryo $libryo */
                if (!$libryo->hasTasksModule()) {
                    $tasks = null;
                }
            }
            // @codeCoverageIgnoreEnd
            if (!is_null($tasks)) {
                $this->items[] = $tasks;
            }
        }

        $this->items[] = [
            'icon' => 'folder-open',
            'label' => __('my.nav.drives'),
            'route' => '#',
            'current_module' => $request->routeIs('my.drives.*'),
            'children' => [
                [
                    'label' => __('my.nav.files'),
                    'route' => route('my.drives.files.index'),
                ],
                [
                    'label' => __('my.nav.folders'),
                    'route' => route('my.drives.root'),
                ],
            ],
        ];

        $this->items[] = [];

        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        $applicabilityHidden = !$manager->isSingleMode()
            && $organisation->libryos()->where('settings->modules->hide_applicability', true)->exists();

        if (($applicabilityHidden && $user->isMySuperUser()) || (!$applicabilityHidden && $user->canManageApplicability())) {
            $this->items[] = [
                'icon' => 'ballot-check',
                'label' => __('settings.nav.applicability'),
                'route' => route('my.context-questions.index'),
                'current_module' => $request->routeIs('my.context-questions.*'),
            ];
        }

        if (userIsOrgAdmin()) {
            $this->items[] = [
                'icon' => 'sitemap',
                'label' => __('settings.nav.org_settings'),
                'route' => route('my.settings.dashboard', ['activateOrgId' => $organisation->id]),
                'current_module' => false,
            ];
        }

        return view('components.ui.my.sidebar');
    }
}
