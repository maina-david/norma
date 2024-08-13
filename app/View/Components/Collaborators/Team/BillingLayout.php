<?php

namespace App\View\Components\Collaborators\Team;

use App\Models\Collaborators\Team;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class BillingLayout extends Component
{
    /** @var array<int, mixed> */
    public array $navItems = [];

    /** @var Team */
    public Team $team;

    /**
     * Can't typehint libryo as laravel will attempt to use DI.
     *
     * @param Request $request
     * @param Team    $team
     */
    public function __construct(public Request $request, $team)
    {
        $this->team = $team;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->navItems = [];
        /** @var \App\Models\Auth\User $user */
        $user = $this->request->user();

        if ($user->can('manage', $this->team)) {
            $this->navItems[] = [
                'label' => __('nav.payments'),
                'route' => route('collaborate.collaborators.teams.my-payments.index'),
                'isCurrent' => $this->request->routeIs('collaborate.collaborators.teams.my-payments.index'),
            ];
            $this->navItems[] = [
                'label' => __('nav.billing'),
                'route' => route('collaborate.collaborators.teams.billing.edit'),
                'isCurrent' => $this->request->routeIs('collaborate.collaborators.teams.billing.edit'),
            ];
        }

        /** @var View */
        return view('partials.ui.nav-layout');
    }
}
