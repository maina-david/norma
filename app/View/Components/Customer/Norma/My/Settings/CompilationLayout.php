<?php

namespace App\View\Components\Customer\Norma\My\Settings;

use App\Models\Customer\Norma;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class CompilationLayout extends Component
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
                'label' => __('settings.nav.settings'),
                'route' => route('my.settings.normas.compilation-settings.index', ['norma' => $this->norma->id]),
                'isCurrent' => $this->request->routeIs('my.settings.normas.compilation-settings.index'),
            ],
            [
                'label' => __('settings.nav.collections'),
                'route' => route('my.settings.normas.compilation.requirements-collections.index', ['norma' => $this->norma->id]),
                'isCurrent' => $this->request->routeIs('my.settings.normas.compilation.requirements-collections.*'),
            ],
            [
                'label' => __('settings.nav.legal_domains'),
                'route' => route('my.settings.normas.compilation.legal-domains.index', ['norma' => $this->norma->id]),
                'isCurrent' => $this->request->routeIs('my.settings.normas.compilation.legal-domains.index'),
            ],
        ];

        /** @var View */
        return view('components.customer.norma.my.settings.compilation-layout');
    }
}
