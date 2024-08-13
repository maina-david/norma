<?php

namespace App\Http\Controllers\Corpus\My;

use App\Http\Controllers\Controller;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\Request;

abstract class AbstractWorkController extends Controller
{
    /**
     * @return array<mixed>
     */
    protected function getLibryoOrganisationQuery(Request $request): array
    {
        $filters = $request->only(['tags', 'domain', 'jurisdictionType']);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $locationTypeQuery = null;
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $libryo->load('location');
            $query = Work::primaryForLibryo($libryo, $filters);
        // $locationTypeQuery = LocationType::forLibryo($libryo, array_diff_key($filters, ['jurisdictionType' => false]))
        //     ->with([
        //         'locations' => function ($q) use ($libryo) {
        //             $q->whereHas(
        //                 'references',
        //                 function ($q) use ($libryo) {
        //                     $q->forLibryo($libryo);
        //                 }
        //             );
        //         },
        //     ]);
        } else {
            /** @var Organisation $organisation */
            $organisation = $manager->getActiveOrganisation();
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            $query = Work::primaryForOrganisation($organisation, $user, $filters);
            // $locationTypeQuery = LocationType::forOrganisationUserAccess($organisation, $user, array_diff_key($filters, ['jurisdictionType' => false]))
            //     ->with([
            //         'locations' => function ($q) use ($organisation, $user) {
            //             $q->whereHas('references', fn ($q) => $q->forOrganisationUserAccess($organisation, $user));
            //         },
            //     ]);
        }

        return [$query, $locationTypeQuery, $libryo ?? null, $organisation ?? null];
    }
}
