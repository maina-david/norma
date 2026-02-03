<?php

namespace App\View\Components\Collaborators\Team;

use App\Models\Collaborators\Team;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class Layout extends Component
{
    /** @var array<int, mixed> */
    public array $navItems = [];

    /** @var Team */
    public Team $team;

    /**
     * Can't typehint norma as laravel will attempt to use DI.
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

        if ($user->can('collaborate.collaborators.team.view')) {
            $this->navItems[] = [
                'label' => __('nav.info'),
                'route' => route('collaborate.teams.show', ['team' => $this->team->id]),
                'isCurrent' => $this->request->routeIs('collaborate.teams.show'),
            ];
        }

        if ($user->can('collaborate.payments.payment.viewAny')) {
            $this->navItems[] = [
                'label' => __('nav.payments'),
                'route' => route('collaborate.collaborators.teams.payments.index', ['team' => $this->team->id]),
                'isCurrent' => $this->request->routeIs('collaborate.collaborators.teams.payments.index'),
            ];
        }

        if ($this->team->outstandingPaymentRequests()->exists() && $user->can('collaborate.collaborators.team.payment-request.outstanding.viewAny')) {
            $this->navItems[] = [
                'label' => __('nav.payments_outstanding'),
                'route' => route('collaborate.collaborators.teams.payment-requests.outstanding', ['team' => $this->team->id]),
                'isCurrent' => $this->request->routeIs('collaborate.collaborators.teams.payment-requests.outstanding'),
            ];
        }
        if ($user->can('collaborate.collaborators.team-rate.viewAny')) {
            $this->navItems[] = [
                'label' => __('nav.team_rates'),
                'route' => route('collaborate.collaborators.teams.team-rates.index', ['team' => $this->team->id]),
                'isCurrent' => $this->request->routeIs('collaborate.collaborators.teams.team-rates.*'),
            ];
        }

        /** @var View */
        return view('partials.ui.nav-layout');
    }
}
