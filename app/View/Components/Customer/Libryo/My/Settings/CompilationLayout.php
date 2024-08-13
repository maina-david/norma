<?php

namespace App\View\Components\Customer\Libryo\My\Settings;

use App\Models\Customer\Libryo;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class CompilationLayout extends Component
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
                'label' => __('settings.nav.settings'),
                'route' => route('my.settings.libryos.compilation-settings.index', ['libryo' => $this->libryo->id]),
                'isCurrent' => $this->request->routeIs('my.settings.libryos.compilation-settings.index'),
            ],
            [
                'label' => __('settings.nav.collections'),
                'route' => route('my.settings.libryos.compilation.requirements-collections.index', ['libryo' => $this->libryo->id]),
                'isCurrent' => $this->request->routeIs('my.settings.libryos.compilation.requirements-collections.*'),
            ],
            [
                'label' => __('settings.nav.legal_domains'),
                'route' => route('my.settings.libryos.compilation.legal-domains.index', ['libryo' => $this->libryo->id]),
                'isCurrent' => $this->request->routeIs('my.settings.libryos.compilation.legal-domains.index'),
            ],
        ];

        /** @var View */
        return view('components.customer.libryo.my.settings.compilation-layout');
    }
}
