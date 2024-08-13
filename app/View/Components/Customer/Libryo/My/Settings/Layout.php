<?php

namespace App\View\Components\Customer\Libryo\My\Settings;

use App\Models\Customer\Libryo;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class Layout extends Component
{
    /** @var array<int, mixed> */
    public array $navItems = [];

    /** @var Libryo */
    public Libryo $libryo;

    /**
     * Can't typehint libryo as laravel will attempt to use DI.
     *
     * @param Request $request
     * @param Libryo  $libryo
     */
    public function __construct(public Request $request, $libryo)
    {
        $this->libryo = $libryo;
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
                'label' => __('settings.nav.info'),
                'route' => route('my.settings.libryos.show', ['libryo' => $this->libryo->id]),
                'isCurrent' => $this->request->routeIs('my.settings.libryos.show'),
            ],
            [
                'label' => __('settings.nav.users'),
                'route' => route('my.settings.users.for.libryo.index', ['libryo' => $this->libryo->id]),
                'isCurrent' => $this->request->routeIs('my.settings.users.for.libryo.index'),
            ],
            [
                'label' => __('settings.nav.teams'),
                'route' => route('my.settings.teams.for.libryo.index', ['libryo' => $this->libryo->id]),
                'isCurrent' => $this->request->routeIs('my.settings.teams.for.libryo.index'),
            ],
            [
                'label' => __('settings.nav.assess_setup'),
                'route' => route('my.settings.assess.setup.for.libryo', ['libryo' => $this->libryo->id]),
                'isCurrent' => $this->request->routeIs('my.settings.assess.setup.for.libryo'),
            ],
        ];

        if ($this->request->user()?->canManageAllOrganisations()) {
            $this->navItems[] = [
                'label' => __('settings.nav.modules'),
                'route' => route('my.settings.libryos.modules.index', ['libryo' => $this->libryo->id]),
                'isCurrent' => $this->request->routeIs('my.settings.libryos.modules.index'),
            ];
            $this->navItems[] = [
                'label' => __('settings.nav.compilation'),
                'route' => route('my.settings.libryos.compilation-settings.index', ['libryo' => $this->libryo->id]),
                'isCurrent' => $this->request->routeIs('my.settings.libryos.compilation*'),
            ];
        }

        /** @var View */
        return view('components.customer.libryo.my.settings.layout');
    }
}
