<?php

namespace App\Http\Controllers\Corpus\My;

use App\Http\Controllers\Controller;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Http\Request;

abstract class AbstractWorkController extends Controller
{
    /**
     * @return array<mixed>
     */
    protected function getNormaOrganisationQuery(Request $request): array
    {
        $filters = $request->only(['tags', 'domain', 'jurisdictionType']);

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $locationTypeQuery = null;
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $norma->load('location');
            $query = Work::primaryForNorma($norma, $filters);
        // $locationTypeQuery = LocationType::forNorma($norma, array_diff_key($filters, ['jurisdictionType' => false]))
        //     ->with([
        //         'locations' => function ($q) use ($norma) {
        //             $q->whereHas(
        //                 'references',
        //                 function ($q) use ($norma) {
        //                     $q->forNorma($norma);
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

        return [$query, $locationTypeQuery, $norma ?? null, $organisation ?? null];
    }
}
