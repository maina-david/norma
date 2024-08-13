<?php

namespace App\View\Components\Ontology\LegalDomain\Collaborate;

use App\Models\Auth\User;
use App\Models\Ontology\LegalDomain;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class Layout extends Component
{
    /** @var array<int, mixed> */
    public array $navItems = [];

    public function __construct(protected Request $request, public LegalDomain $domain)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->navItems = [];

        /** @var User $user */
        $user = $this->request->user();

        if ($user->can('collaborate.compilation.context-question.view')) {
            $this->navItems[] = [
                'label' => __('nav.info'),
                'route' => route('collaborate.legal-domains.show', ['legal_domain' => $this->domain->id]),
                'isCurrent' => $this->request->routeIs('collaborate.legal-domains.show'),
            ];
        }

        if ($user->can('collaborate.ontology.legal-domain.locations.viewAny')) {
            $this->navItems[] = [
                'label' => __('nav.jurisdictions'),
                'route' => route('collaborate.legal-domains.jurisdictions.index', ['domain' => $this->domain->id]),
                'isCurrent' => $this->request->routeIs('collaborate.legal-domains.jurisdictions.*'),
            ];
        }

        /** @var View */
        return view('partials.ui.nav-layout');
    }
}
