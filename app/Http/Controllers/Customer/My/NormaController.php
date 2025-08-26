<?php

namespace App\Http\Controllers\Customer\My;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\GenericActivityUsingAuth;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
use App\Services\Customer\ActiveOrganisationManager;
use App\Services\Geo\Geohasher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NormaController extends Controller
{
    public function index(): View
    {
        /** @var User */
        $user = Auth::user();

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $isSingleMode = false;
        $query = Norma::active()->userHasAccess($user);
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $isSingleMode = true;
        } else {
            /** @var Norma|null */
            $norma = $query->first();
        }

        /** @var View */
        return view(
            'pages.customer.my.norma.index',
            [
                'baseQuery' => $query,
                'norma' => $norma,
                'mapZoom' => $isSingleMode ? 15 : 5,
                'mapCenterLat' => (float) $norma?->geo_lat,
                'mapCenterLng' => (float) $norma?->geo_lng,
            ]
        );
    }

    /**
     * Get normas for markers.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forMarkers(Request $request): JsonResponse
    {
        /** @var User */
        $user = Auth::user();
        $bounds = explode(',', $request->input('bounds'));
        [$north, $east, $south, $west] = array_map(function ($bound) {
            return (float) $bound;
        }, $bounds);
        $zoom = $request->input('zoom', 7);
        $precision = app(Geohasher::class)->getGeohashLength($zoom);

        $query = Norma::userHasAccess($user)->active()->withinBounds($north, $east, $south, $west, $precision);
        $items = $query->get();

        return response()->json($items);
    }

    /**
     * @param int $norma
     *
     * @return RedirectResponse
     */
    public function activate(int $norma): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Norma $normaModel */
        $normaModel = Norma::active()
            ->userHasAccess($user)
            ->findOrFail($norma);

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $manager->activate($user, $normaModel);

        return back();
    }

    /**
     * @return RedirectResponse
     */
    public function activateAll(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $manager->activateAll($user);

        event(new GenericActivityUsingAuth(UserActivityType::activatedMultiStream()));

        return back();
    }

    /**
     * @param Request $request
     * @param int     $norma
     *
     * @return RedirectResponse
     */
    public function activateRedirect(Request $request, int $norma): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Norma */
        $normaModel = Norma::active()
            ->userHasAccess($user)
            ->findOrFail($norma);

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $manager->activate($user, $normaModel);

        /** @var RedirectResponse */
        return response()->redirectTo($request->input('redirect'));
    }

    /**
     * @param Request $request
     * @param int     $organisation
     *
     * @return RedirectResponse
     */
    public function activateAllRedirect(Request $request, int $organisation): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $manager->activateAll($user);

        /** @var Organisation */
        $organisationModel = Organisation::userHasAccess($user)
            ->findOrFail($organisation);

        /** @var ActiveOrganisationManager */
        $orgManager = app(ActiveOrganisationManager::class);
        $orgManager->activate($organisationModel->id);

        /** @var RedirectResponse */
        return redirect($request->input('redirect'));
    }
}
