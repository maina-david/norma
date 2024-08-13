<?php

namespace App\Http\Controllers\Api\V2\Corpus;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Corpus\Reference\V2\BasicReferenceForOrgResource;
use App\Models\Corpus\Reference;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Only to be used for isometrix integration.
 */
class ForOrganisationReferenceController extends AbstractApiController
{
    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getModelClass(): string
    {
        return Reference::class;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getApiResourceClass(): string
    {
        return BasicReferenceForOrgResource::class;
    }

    /**
     * Get base query for model.
     *
     * @param Request $request
     *
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        /** @var Organisation $organisation */
        $organisation = $request->route('organisation');

        // not using forOrganisationUserAccess because this is specificaly for Isometrix integration
        /** @var Builder */
        return Reference::forOrganisation($organisation)
            ->with([
                'citation' => fn ($q) => null,
                'refPlainText' => fn ($q) => $q->select(['reference_id', 'plain_text']),
                'htmlContent' => fn ($q) => $q->select(['reference_id', 'cached_content']),
                'libryos' => fn ($q) => $q->forOrganisation($organisation->id),
            ]);
    }

    /**
     * @param ApiMultiGetRequest $request
     * @param Organisation       $organisation
     *
     * @return ResourceCollection<Reference>
     */
    public function forOrganisation(Request $request, Organisation $organisation): ResourceCollection
    {
        /** @var ResourceCollection<Reference> */
        return parent::index($request);
    }
}
