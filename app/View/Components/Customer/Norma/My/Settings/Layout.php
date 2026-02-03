<?php

namespace App\View\Components\Customer\Norma\My\Settings;

use App\Models\Customer\Norma;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class Layout extends Component
{
    /** @var array<int, mixed> */
    public array $navItems = [];

    /** @var Norma */
    public Norma $norma;

    /**
     * Can't typehint norma as laravel will attempt to use DI.
     *
     * @param Request $request
     * @param Norma  $norma
     */
    public function __construct(public Request $request, $norma)
    {
        $this->norma = $norma;
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
                'route' => route('my.settings.normas.show', ['norma' => $this->norma->id]),
                'isCurrent' => $this->request->routeIs('my.settings.normas.show'),
            ],
            [
                'label' => __('settings.nav.users'),
                'route' => route('my.settings.users.for.norma.index', ['norma' => $this->norma->id]),
                'isCurrent' => $this->request->routeIs('my.settings.users.for.norma.index'),
            ],
            [
                'label' => __('settings.nav.teams'),
                'route' => route('my.settings.teams.for.norma.index', ['norma' => $this->norma->id]),
                'isCurrent' => $this->request->routeIs('my.settings.teams.for.norma.index'),
            ],
            [
                'label' => __('settings.nav.assess_setup'),
                'route' => route('my.settings.assess.setup.for.norma', ['norma' => $this->norma->id]),
                'isCurrent' => $this->request->routeIs('my.settings.assess.setup.for.norma'),
            ],
        ];

        if ($this->request->user()?->canManageAllOrganisations()) {
            $this->navItems[] = [
                'label' => __('settings.nav.modules'),
                'route' => route('my.settings.normas.modules.index', ['norma' => $this->norma->id]),
                'isCurrent' => $this->request->routeIs('my.settings.normas.modules.index'),
            ];
            $this->navItems[] = [
                'label' => __('settings.nav.compilation'),
                'route' => route('my.settings.normas.compilation-settings.index', ['norma' => $this->norma->id]),
                'isCurrent' => $this->request->routeIs('my.settings.normas.compilation*'),
            ];
        }

        /** @var View */
        return view('components.customer.norma.my.settings.layout');
    }
}
