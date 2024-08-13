<?php

namespace App\Http\Controllers\Customer\My;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\GenericActivityUsingAuth;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Customer\ActiveOrganisationManager;
use App\Services\Geo\Geohasher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibryoController extends Controller
{
    public function index(): View
    {
        /** @var User */
        $user = Auth::user();

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $isSingleMode = false;
        $query = Libryo::active()->userHasAccess($user);
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $isSingleMode = true;
        } else {
            /** @var Libryo|null */
            $libryo = $query->first();
        }

        /** @var View */
        return view(
            'pages.customer.my.libryo.index',
            [
                'baseQuery' => $query,
                'libryo' => $libryo,
                'mapZoom' => $isSingleMode ? 15 : 5,
                'mapCenterLat' => (float) $libryo?->geo_lat,
                'mapCenterLng' => (float) $libryo?->geo_lng,
            ]
        );
    }

    /**
     * Get libryos for markers.
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

        $query = Libryo::userHasAccess($user)->active()->withinBounds($north, $east, $south, $west, $precision);
        $items = $query->get();

        return response()->json($items);
    }

    /**
     * @param int $libryo
     *
     * @return RedirectResponse
     */
    public function activate(int $libryo): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Libryo $libryoModel */
        $libryoModel = Libryo::active()
            ->userHasAccess($user)
            ->findOrFail($libryo);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $manager->activate($user, $libryoModel);

        return back();
    }

    /**
     * @return RedirectResponse
     */
    public function activateAll(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $manager->activateAll($user);

        event(new GenericActivityUsingAuth(UserActivityType::activatedMultiStream()));

        return back();
    }

    /**
     * @param Request $request
     * @param int     $libryo
     *
     * @return RedirectResponse
     */
    public function activateRedirect(Request $request, int $libryo): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Libryo */
        $libryoModel = Libryo::active()
            ->userHasAccess($user)
            ->findOrFail($libryo);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $manager->activate($user, $libryoModel);

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

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
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
