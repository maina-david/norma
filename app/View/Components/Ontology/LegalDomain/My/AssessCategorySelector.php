<?php

namespace App\View\Components\Ontology\LegalDomain\My;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveNormasManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class AssessCategorySelector extends Component
{
    /** @var Collection<LegalDomain> */
    public Collection $legalDomains;

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $forNormaOrgFunc = null;
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $forNormaOrgFunc = function ($q) use ($norma) {
                $q->forNorma($norma);
            };
        } else {
            /** @var User $user */
            $user = Auth::user();
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $forNormaOrgFunc = function ($q) use ($organisation, $user) {
                $q->forOrganisationUserAccess($organisation, $user);
            };
        }
        $domainIds = LegalDomain::whereHas('assessmentItems', function ($q) use ($forNormaOrgFunc) {
            $q->whereHas('assessmentResponses', $forNormaOrgFunc);
        })->pluck('id')->all();

        // This is pretty horrible, but hopefully we'll move these assess categories to the categories table, which has a closure table
        $whereKeyIn = fn ($q) => $q->whereKey($domainIds);
        $domainsIds = LegalDomain::whereKey($domainIds)
            ->orWhereHas('parent', $whereKeyIn)
            ->orWhereHas('parent.parent', $whereKeyIn)
            ->orWhereHas('parent.parent.parent', $whereKeyIn)
            ->pluck('id')
            ->all();

        $whereKeyIn = fn ($q) => $q->whereKey($domainsIds);
        $whereKeyInForRelation = fn ($q) => $q->whereKey($domainsIds)
            ->orWhereHas('children', $whereKeyIn)
            ->orWhereHas('children.children', $whereKeyIn)
            ->orWhereHas('children.children.children', $whereKeyIn);

        $query = LegalDomain::whereNull('parent_id')->where(function ($q) use ($whereKeyIn, $domainIds) {
            $q->whereKey($domainIds);
            $q->orWhereHas('children', $whereKeyIn);
            $q->orWhereHas('children.children', $whereKeyIn);
            $q->orWhereHas('children.children.children', $whereKeyIn);
        })
            ->with([
                'children' => $whereKeyInForRelation,
                'children.children' => $whereKeyInForRelation,
                'children.children.children' => $whereKeyInForRelation,
            ])
            ->orderBy('title');

        /** @var Collection<LegalDomain> */
        $legalDomains = $query->get();
        $this->legalDomains = $legalDomains;

        /** @var View */
        return view('components.ontology.legal-domain.my.assess-category-selector');
    }
}
