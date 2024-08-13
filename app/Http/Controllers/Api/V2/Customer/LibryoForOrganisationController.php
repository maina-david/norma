<?php

namespace App\Http\Controllers\Api\V2\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\LibryoRequest;
use App\Http\Resources\Customer\Libryo\V2\LibryoResource;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\LegalDomain;
use App\Stores\Compilation\LibraryLibryoStore;
use App\Stores\Customer\LibryoLegalDomainStore;
use App\Stores\Customer\LibryoUserStore;

class LibryoForOrganisationController extends Controller
{
    /**
     * Create a new libryo for the given organisation.
     *
     * @param LibryoRequest $request
     * @param Organisation  $organisation
     *
     * @return object
     */
    public function store(LibryoRequest $request, Organisation $organisation): object
    {
        $payload = $request->only(['integration_id', 'title', 'address', 'geo_lat', 'geo_lng', 'location_id']);
        $payload['organisation_id'] = $organisation->id;
        $payload['needs_recompilation'] = 1;

        /** @var Libryo $libryo */
        $libryo = Libryo::create($payload);

        $library = app(LibraryLibryoStore::class)->createForLibryo($libryo);

        $libryo->library_id = $library->id;

        $libryo->save();

        if ($organisation->partner_id !== null) {
            /** @var \App\Models\Auth\User $user */
            $user = $request->user();
            app(LibryoUserStore::class)->attachAdminUsers($libryo, collect([$user]));
        }

        if ($request->has('legalDomains')) {
            $domains = LegalDomain::whereIn('id', $request->get('legalDomains', []))->get();

            app(LibryoLegalDomainStore::class)->attachLegalDomains($libryo, $domains);
        }

        $libryo->setRelation('library', $library);

        return (new LibryoResource($libryo))
            ->additional(['meta' => ['message' => __('actions.saved_successfully')]])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the Libryo.
     *
     * @param LibryoRequest $request
     * @param Organisation  $organisation
     * @param Libryo        $libryo
     *
     * @return LibryoResource
     */
    public function update(LibryoRequest $request, Organisation $organisation, Libryo $libryo): LibryoResource
    {
        $libryo->update($request->only(['title', 'address', 'geo_lat', 'geo_lng', 'deactivated']));

        return (new LibryoResource($libryo))
            ->additional(['meta' => ['message' => __('actions.saved_successfully')]]);
    }
}
