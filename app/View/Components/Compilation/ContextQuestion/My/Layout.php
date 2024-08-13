<?php

namespace App\View\Components\Compilation\ContextQuestion\My;

use App\Models\Auth\User;
use App\Services\Customer\ActiveLibryosManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class Layout extends Component
{
    /** @var array<int, mixed> */
    public array $navItems = [];

    public function __construct(protected Request $request, public bool $bordered = false)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->navItems = [
            [
                'icon' => 'ballot-check',
                'label' => __('compilation.context_question.applicability_questions'),
                'route' => route('my.context-questions.index'),
                'isCurrent' => $this->request->routeIs('my.context-questions.index'),
            ],
        ];

        /** @var User $user */
        $user = $this->request->user();

        $org = app(ActiveLibryosManager::class)->getActiveOrganisation();

        if ($user->can('access.org.settings', $org)) {
            $this->navItems[] = [
                'icon' => 'file-alt',
                'label' => __('requirements.requirements'),
                'route' => route('my.applicability.requirements-setup.index'),
                'isCurrent' => $this->request->routeIs('my.applicability.requirements-setup.index'),
            ];

            $this->navItems[] = [
                'icon' => 'stream',
                'label' => __('compilation.context_question.change_history'),
                'route' => route('my.applicability.history.index'),
                'isCurrent' => $this->request->routeIs('my.applicability.history.index'),
            ];
        }

        /** @var View */
        return view('partials.ui.plain-nav-layout');
    }
}
