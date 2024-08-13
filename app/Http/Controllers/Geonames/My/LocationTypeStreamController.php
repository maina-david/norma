<?php

namespace App\Http\Controllers\Geonames\My;

use App\Http\Controllers\Controller;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Geonames\LocationType;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LocationTypeStreamController extends Controller
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

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $query = LocationType::forLibryo($libryo);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            $query = LocationType::forOrganisationUserAccess($organisation, $user);
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.geonames.my.location-type.search-suggest',
            'target' => 'search-suggest-location-types-' . $key,
            'locationTypes' => $search
                ? $query->get()->filter(fn ($lt) => str_contains(mb_strtolower(__('corpus.location_type.' . $lt->adjective_key)), mb_strtolower($search)))
                : (new LocationType())->newCollection(),
            'search' => $search,
            'linkToDetailed' => (bool) $request->query('link'),
        ]);

        return turboStreamResponse($view);
    }
}
