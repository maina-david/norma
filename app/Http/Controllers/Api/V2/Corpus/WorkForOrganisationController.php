<?php

namespace App\Http\Controllers\Api\V2\Corpus;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Requests\ApiMultiGetRequest;
use App\Http\Resources\Corpus\Work\V2\WorkForOrganisationResource;
use App\Models\Corpus\Work;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WorkForOrganisationController extends AbstractApiController
{
    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getModelClass(): string
    {
        return Work::class;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getApiResourceClass(): string
    {
        return WorkForOrganisationResource::class;
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
        /** @var Organisation */
        $organisation = $request->route('organisation');
        $baseQuery = Work::whereHas('references', function ($q) use ($organisation) {
            $q->forOrganisation($organisation);
        });

        // really not a nice way of doing this, but this end point was requested by Isometrix and
        // this seems to be the most efficient way of querying it (rather than combining the baseQuery with orWhereHas children forOrganisation)
        $allWorkIds = $baseQuery->get('id')->pluck('id')->toArray();

        return Work::where(function ($q) use ($allWorkIds) {
            $q->whereKey($allWorkIds);
            $q->orWhereHas('children', function ($q) use ($allWorkIds) {
                $q->whereKey($allWorkIds);
            });
        })
            ->active()
            ->with('parents');
    }

    /**
     * @param ApiMultiGetRequest $request
     * @param Organisation       $organisation
     *
     * @return ResourceCollection<Work>
     */
    public function forOrganisation(Request $request, Organisation $organisation): ResourceCollection
    {
        /** @var ResourceCollection<Work> */
        return parent::index($request);
    }
}
