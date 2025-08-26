<?php

namespace App\Http\Controllers\Api\V2\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\NormaRequest;
use App\Http\Resources\Customer\Norma\V2\NormaResource;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\LegalDomain;
use App\Stores\Compilation\LibraryNormaStore;
use App\Stores\Customer\NormaLegalDomainStore;
use App\Stores\Customer\NormaUserStore;

class NormaForOrganisationController extends Controller
{
    /**
     * Create a new norma for the given organisation.
     *
     * @param NormaRequest $request
     * @param Organisation  $organisation
     *
     * @return object
     */
    public function store(NormaRequest $request, Organisation $organisation): object
    {
        $payload = $request->only(['integration_id', 'title', 'address', 'geo_lat', 'geo_lng', 'location_id']);
        $payload['organisation_id'] = $organisation->id;
        $payload['needs_recompilation'] = 1;

        /** @var Norma $norma */
        $norma = Norma::create($payload);

        $library = app(LibraryNormaStore::class)->createForNorma($norma);

        $norma->library_id = $library->id;

        $norma->save();

        if ($organisation->partner_id !== null) {
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            app(NormaUserStore::class)->attachAdminUsers($norma, collect([$user]));
        }

        if ($request->has('legalDomains')) {
            $domains = LegalDomain::whereIn('id', $request->get('legalDomains', []))->get();

            app(NormaLegalDomainStore::class)->attachLegalDomains($norma, $domains);
        }

        $norma->setRelation('library', $library);

        return (new NormaResource($norma))
            ->additional(['meta' => ['message' => __('actions.saved_successfully')]])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the Norma.
     *
     * @param NormaRequest $request
     * @param Organisation  $organisation
     * @param Norma        $norma
     *
     * @return NormaResource
     */
    public function update(NormaRequest $request, Organisation $organisation, Norma $norma): NormaResource
    {
        $norma->update($request->only(['title', 'address', 'geo_lat', 'geo_lng', 'deactivated']));

        return (new NormaResource($norma))
            ->additional(['meta' => ['message' => __('actions.saved_successfully')]]);
    }
}
