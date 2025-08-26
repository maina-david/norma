<?php

namespace App\Http\Controllers\Ontology\My;

use App\Http\Controllers\Controller;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LegalDomainStreamController extends Controller
{
    /**
     * Used for search suggest to search for works.
     *
     * @param Request $request
     * @param string  $key     To distinguish between different frames if there are multiple frames on the same page
     *
     * @return Response
     */
    public function searchSuggest(Request $request, string $key): Response
    {
        /** @var string */
        $search = $request->input('search', '');

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $query = LegalDomain::forNorma($norma);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            $query = LegalDomain::forOrganisationUserAccess($organisation, $user);
        }
        $query->titleLike($search);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.ontology.my.legal-domain.search-suggest',
            'target' => 'search-suggest-legal-domains-' . $key,
            'domains' => $search ? $query->get() : (new LegalDomain())->newCollection(),
            'search' => $search,
            'linkToDetailed' => (bool) $request->query('link'),
        ]);

        return turboStreamResponse($view);
    }
}
