<?php

namespace App\Http\Controllers\Api\V2\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\OrganisationPartnerRequest;
use App\Http\Requests\Customer\OrganisationRequest;
use App\Http\Resources\Customer\Norma\V2\OrganisationForPartnerResource;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Partners\Partner;
use Illuminate\Auth\Access\AuthorizationException;

class OrganisationForPartnerController extends Controller
{
    /**
     * Authorise the partner request.
     *
     * @param int $partnerId
     *
     * @throws AuthorizationException
     *
     * @return void
     */
    protected function authorisePartner(int $partnerId): void
    {
        /** @var Partner $partner */
        $partner = Partner::findOrFail($partnerId);

        $this->authorize('createOrganisation', $partner);
    }

    /**
     * Create a new organisation for the given partner.
     *
     * @param OrganisationPartnerRequest $request
     *
     * @throws AuthorizationException
     *
     * @return OrganisationForPartnerResource
     */
    public function store(OrganisationPartnerRequest $request): OrganisationForPartnerResource
    {
        $this->authorisePartner($request->get('partner_id'));

        /** @var Organisation $organisation */
        $organisation = Organisation::create($request->only([
            'title', 'integration_id', 'partner_id', 'whitelabel_id',
        ]));

        /** @var User $user */
        $user = $request->user();

        $user->organisations()->attach($organisation->id, ['is_admin' => true]);

        return (new OrganisationForPartnerResource($organisation))->additional([
            'meta' => [
                'message' => __('actions.saved_successfully'),
            ],
        ]);
    }

    /**
     * Update an existing organisation for the given partner.
     *
     * @param OrganisationRequest $request
     * @param Organisation        $organisation
     *
     * @throws AuthorizationException
     *
     * @return OrganisationForPartnerResource
     */
    public function update(OrganisationRequest $request, Organisation $organisation): OrganisationForPartnerResource
    {
        if ($organisation->partner_id) {
            $this->authorisePartner($organisation->partner_id);
        }

        $organisation->update($request->only(['title']));

        return (new OrganisationForPartnerResource($organisation))->additional([
            'meta' => [
                'message' => __('actions.saved_successfully'),
            ],
        ]);
    }
}
